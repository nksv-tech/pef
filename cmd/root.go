package cmd

import (
	"fmt"

	"github.com/spf13/cobra"
)

func Execute() error {
	return rootCmd.Execute()
}

var rootCmd = &cobra.Command{
	Use:   "generate",
	Short: "Generate PHP code",
	Long:  `test`,
	RunE: func(cmd *cobra.Command, args []string) error {
		fmt.Println("This is the first cobra example")
		return nil
	},
}
