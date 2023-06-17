package load

import (
	"bytes"
	"embed"
	"encoding/json"
	"errors"
	"fmt"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
	"sync"
	"text/template"

	"github.com/VKCOM/php-parser/pkg/ast"
	"github.com/VKCOM/php-parser/pkg/visitor/traverser"
	"golang.org/x/sync/errgroup"

	"github.com/VKCOM/php-parser/pkg/conf"
	"github.com/VKCOM/php-parser/pkg/parser"
	"github.com/VKCOM/php-parser/pkg/version"
	"github.com/VKCOM/php-parser/pkg/visitor/nsresolver"
)

const composerFile = "composer.json"

type (
	// A SchemaSpec holds a serializable version of an ent.Schema
	// and its Go package and module information.
	SchemaSpec struct {
		// Schemas defines the loaded schema descriptors.
		Schemas []*Schema

		Namespace string

		Names []string
	}

	// Config holds the configuration for loading an ent/schema package.
	Config struct {
		// Path to PHP executor
		Executor string
		// Path is the path for the schema package.
		Path string
		// Names are the schema names to load. Empty means all schemas in the directory.
		Names []string

		Composer struct {
			Version  string
			Autoload string
		}
	}
)

func (c *Config) Load() (*SchemaSpec, error) {
	err := c.loadComposer()
	if err != nil {
		return nil, fmt.Errorf("pefc/load: get composer file: %w", err)
	}
	spec, err := c.load()
	if err != nil {
		return nil, fmt.Errorf("pefc/load: parse schema dir: %w", err)
	}

	if len(c.Names) == 0 {
		return nil, fmt.Errorf("pefc/load: no schema found in: %s", c.Path)
	}
	var b bytes.Buffer
	err = buildTmpl.ExecuteTemplate(&b, "index.php.tmpl", struct {
		*Config
		Namespace        string
		ComposerAutoload string
	}{
		Config:           c,
		Namespace:        spec.Namespace,
		ComposerAutoload: c.Composer.Autoload,
	})
	if err != nil {
		return nil, fmt.Errorf("pefc/load: execute template: %w", err)
	}
	if err := os.MkdirAll(".pefc", os.ModePerm); err != nil {
		return nil, err
	}
	target := filepath.Join(".pefc", fmt.Sprintf("index.php"))
	if err := os.WriteFile(target, b.Bytes(), 0644); err != nil {
		return nil, fmt.Errorf("pefc/load: write file %s: %w", target, err)
	}
	defer os.RemoveAll(".pefc")
	out, err := phprun(c.Executor, target, nil)
	if err != nil {
		return nil, err
	}
	type result struct {
		Schemas []json.RawMessage `json:"schemas"`
	}
	res := new(result)
	if err := json.Unmarshal([]byte(out), res); err != nil {
		return nil, fmt.Errorf("pefc/load: unmarshal schemas: %w", err)
	}

	for _, line := range res.Schemas {
		schema, err := UnmarshalSchema(line)
		if err != nil {
			return nil, fmt.Errorf("pefc/load: unmarshal schema %s: %w", line, err)
		}
		spec.Schemas = append(spec.Schemas, schema)
	}
	return spec, nil
}

func (c *Config) loadComposer() error {
	composerPath, err := findComposerJson(c.Path)
	if err != nil {
		return fmt.Errorf("pefc/loadComposer: %w", err)
	}

	bs, err := os.ReadFile(composerPath)
	if err != nil {
		return err
	}

	type composer struct {
		Config struct {
			VendorDir string `json:"vendor-dir,omitempty"`
			Platform  struct {
				PHP string `json:"php"`
			} `json:"platform"`
		} `json:"config"`
	}

	comp := new(composer)
	if err := json.Unmarshal(bs, &comp); err != nil {
		return fmt.Errorf("pefc/loadComposer: parse composer file %w", err)
	}

	autoloadPath := comp.Config.VendorDir
	if autoloadPath == "" {
		autoloadPath = filepath.Join(".", "vendor")
	}
	autoloadPath = filepath.Join(strings.TrimSuffix(composerPath, composerFile), autoloadPath, "autoload.php")

	c.Composer.Autoload = autoloadPath
	c.Composer.Version = comp.Config.Platform.PHP
	if c.Composer.Version == "" {
		c.Composer.Version = "8.1.0"
	}

	verParts := strings.SplitN(c.Composer.Version, ".", -1)
	if len(verParts) > 2 {
		c.Composer.Version = strings.Join(verParts[:2], ".")
	}

	return nil
}
func (c *Config) load() (*SchemaSpec, error) {
	v, err := version.New(c.Composer.Version)
	if err != nil {
		return nil, fmt.Errorf("pefc/load: parse php version: %w", err)
	}
	parserCfg := conf.Config{
		Version: v,
	}

	dirEntries, err := os.ReadDir(c.Path)
	if err != nil {
		return nil, err
	}

	spec := &SchemaSpec{
		Names: make([]string, 0, len(dirEntries)),
	}

	mu := &sync.Mutex{}
	eg := &errgroup.Group{}
	eg.SetLimit(10)
	for _, dirEntry := range dirEntries {
		dirEntry := dirEntry
		if dirEntry.IsDir() {
			continue
		}

		if filepath.Ext(dirEntry.Name()) != ".php" {
			continue
		}

		filePath := filepath.Join(c.Path, dirEntry.Name())
		eg.Go(func() error {
			bs, err := os.ReadFile(filePath)
			if err != nil {
				return fmt.Errorf("pefc/load: can't read file \"%s\": %w", dirEntry.Name(), err)
			}

			vertex, err := parser.Parse(bs, parserCfg)
			if err != nil {
				return fmt.Errorf("pefc/load: parse php file \"%s\": %w", dirEntry.Name(), err)
			}
			nsResolver := nsresolver.NewNamespaceResolver()
			traverser.NewTraverser(nsResolver).Traverse(vertex)

			name := ""
			implementSchema := false
			for key, value := range nsResolver.ResolvedNames {
				// Check class implement Schema interface
				if strings.Contains(value, "Schema") {
					implementSchema = true
				}
				if _, ok := key.(*ast.StmtClass); ok {
					name = value
				}
			}
			// Schema not implemented, skip file
			if !implementSchema {
				return nil
			}

			mu.Lock()
			if spec.Namespace == "" {
				spec.Namespace = nsResolver.Namespace.Namespace
			}
			spec.Names = append(spec.Names, name)
			mu.Unlock()

			return nil
		})
	}

	if err := eg.Wait(); err != nil {
		return nil, err
	}

	for _, needName := range c.Names {
		found := false
		for _, name := range spec.Names {
			if needName == name {
				found = true
				break
			}
		}
		if !found {
			return nil, fmt.Errorf("pefc/load: name \"%s\" not found in schema directory", needName)
		}
	}

	if len(c.Names) > 0 {
		spec.Names = c.Names
	} else {
		c.Names = spec.Names
	}

	return spec, nil
}

// run 'php run' command and return its output.
func phprun(executor, target string, flags []string) (string, error) {
	s, err := cmd(executor, "", target, flags)
	if err != nil {
		return "", fmt.Errorf("pefc/load: %s", err)
	}
	return s, nil
}

// cmd runs a command and returns its output.
func cmd(executor, command, target string, flags []string) (string, error) {
	var args []string
	if command != "" {
		args = append(args, command)
	}
	args = append(args, flags...)
	args = append(args, target)
	cmd := exec.Command(executor, args...)
	stderr := bytes.NewBuffer(nil)
	stdout := bytes.NewBuffer(nil)
	cmd.Stderr = stderr
	cmd.Stdout = stdout
	if err := cmd.Run(); err != nil {
		errMsg := stderr.String()
		if errMsg == "" {
			errMsg = stdout.String()
		}
		return "", errors.New(errMsg)
	}
	return stdout.String(), nil
}

func findComposerJson(path string) (string, error) {
	composerPath := filepath.Join(path, composerFile)
	if _, err := os.Stat(composerPath); os.IsNotExist(err) && path != "." && path != "/" && path != ".." {
		parentDir := filepath.Dir(path)
		if parentDir == path {
			return "", err
		}
		return findComposerJson(parentDir)
	} else if err != nil {
		return "", err
	}

	return composerPath, nil
}

var (
	//go:embed template/index.php.tmpl schema.go
	files     embed.FS
	buildTmpl = templates()
)

func templates() *template.Template {
	tmpl := template.Must(template.New("templates").
		ParseFS(files, "template/index.php.tmpl"))
	return tmpl
}
