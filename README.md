# zsf-cli
Collection of scripts/helpers for ZSF.

## Contributing: Adding New Script
1. Create a new script in the `scripts` directory, e.g. `scripts/new_script.script.php`.
2. Create class in new script file, making sure to implement the `ZsfCliScript` interface.
3. Add the script to the repository's `composer.json` file in the `autoload.files` section.