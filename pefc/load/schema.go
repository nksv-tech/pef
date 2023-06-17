package load

import (
	"encoding/json"
)

// Schema represents an ent.Schema that was loaded from a complied user package.

type Schema struct {
	ClassInfo   *ClassInfo  `json:"classInfo,omitempty"`
	Annotations Annotations `json:"annotations,omitempty"`
	Fields      []*Field    `json:"fields,omitempty"`
}

type ClassInfo struct {
	ClassName     string   `json:"className,omitempty"`
	ClassNameFull string   `json:"classNameFull,omitempty"`
	Namespace     string   `json:"namespace,omitempty"`
	Interfaces    []string `json:"interfaces,omitempty"`
}

type Field struct {
	Name         string        `json:"name,omitempty"`
	Type         string        `json:"type,omitempty"`
	Optional     bool          `json:"optional,omitempty"`
	Nillable     bool          `json:"nillable,omitempty"`
	Comment      string        `json:"comment,omitempty"`
	SchemaType   SchemaType    `json:"schemaType,omitempty"`
	ValueScanner *ValueScanner `json:"valueScanner,omitempty"`
}

type Annotations map[string]any

type SchemaType map[string]string

type ValueScanner struct {
	ClassInfo *ClassInfo `json:"classInfo,omitempty"`
}

// UnmarshalSchema decodes the given buffer to a loaded schema.
func UnmarshalSchema(buf []byte) (*Schema, error) {
	s := &Schema{}
	if err := json.Unmarshal(buf, s); err != nil {
		return nil, err
	}
	return s, nil
}
