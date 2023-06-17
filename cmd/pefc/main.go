package main

import (
	"encoding/json"
	"fmt"
	"github.com/nksv-tech/pef/pefc/load"
)

func main() {
	c := &load.Config{
		Executor: "php",
		Path:     "/home/nikitaksv/www-data/private/pef/php/Schemas",
	}

	spec, err := c.Load()
	if err != nil {
		panic(err)
	}

	bs, err := json.Marshal(spec)
	if err != nil {
		panic(err)
	}

	fmt.Println(string(bs))
}
