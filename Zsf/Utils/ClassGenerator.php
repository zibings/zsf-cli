<?php
	class ClassGenerator {
		private $namespace;
		public function __construct($namespace = 'SomeCachedName') {
			$this->namespace = $namespace;
		}

		public function generateClass($table_name, $columns) {
			$class_content = [];
			$class_content[] = "<?php\n";
			$class_content[] = "namespace {$this->namespace};\n\n";
			$class_content[] = "use Stoic\\Log\\Logger;\n";
			$class_content[] = "use Stoic\\Pdo\\BaseDbQueryTypes;\n";
			$class_content[] = "use Stoic\\Pdo\\BaseDbTypes;\n";
			$class_content[] = "use Stoic\\Pdo\\PdoHelper;\n";
			$class_content[] = "use Stoic\\Pdo\\StoicDbModel;\n";
			$class_content[] = "use Stoic\\Utilities\\ReturnHelper;\n\n";
			$class_content[] = "class {$table_name} extends StoicDbModel {\n";

			$this->generateProperties($class_content, $columns);
			$this->generateCrudMethods($class_content);
			$this->generateSetupModel($class_content, $table_name, $columns);

			$class_content[] = "}\n";
			$class_content[] = "?>\n";

			// Write to file
			file_put_contents("{$table_name}.cls.php", implode("", $class_content));
		}

		private function toLowerFirstChar($s) {
			if ($s === "ID") {
				return "id";
			}
			return lcfirst($s);
		}

		private function generateProperties(&$class_content, $columns) {
			$sql_to_php_types = [
				'int' => 'int',
				'tinyint' => 'int',
				'smallint' => 'int',
				'mediumint' => 'int',
				'bigint' => 'int',
				'varchar' => 'string',
				'char' => 'string',
				'text' => 'string',
				'tinytext' => 'string',
				'mediumtext' => 'string',
				'longtext' => 'string',
				'date' => 'string',
				'time' => 'string',
				'datetime' => 'string',
				'timestamp' => 'string',
				'float' => 'float',
				'double' => 'float',
				'decimal' => 'float',
				'bool' => 'bool',
				'boolean' => 'bool',
				'enum' => 'string',
				'set' => 'string'
			];

			foreach ($columns as $column) {
				$php_type = $sql_to_php_types[$column['type']] ?? 'mixed';
				$class_content[] = "    public {$php_type} \${$this->toLowerFirstChar($column['name'])};\n";
			}
		}

		private function generateCrudMethods(&$class_content) {
			$methods = ['Create', 'Delete', 'Read', 'Update'];
			foreach ($methods as $method) {
				$class_content[] = "\n    /**\n";
				$class_content[] = "     * Determines if the system should attempt to {$method} the record in the database.\n";
				$class_content[] = "     *\n";
				$class_content[] = "     * @return bool|ReturnHelper\n";
				$class_content[] = "     */\n";
				$class_content[] = "    protected function __can{$method}() : bool|ReturnHelper {\n";
				$class_content[] = "        \$ret = new ReturnHelper();\n";
				$class_content[] = "        \$ret->makeGood();\n";
				$class_content[] = "        return \$ret;\n";
				$class_content[] = "    }\n";
			}
		}

		private function generateSetupModel(&$class_content, $table_name, $columns) {
			$class_content[] = "\n    /**\n";
			$class_content[] = "     * Initializes a new model object.\n";
			$class_content[] = "     *\n";
			$class_content[] = "     * @return void\n";
			$class_content[] = "     */\n";
			$class_content[] = "    protected function __setupModel() : void {\n";
			$class_content[] = "        \$this->setTableName('{$table_name}');\n";

			foreach ($columns as $column) {
				$php_type = in_array($column['type'], ['int', 'tinyint', 'smallint', 'mediumint', 'bigint']) ? 'BaseDbTypes::INTEGER' : 'BaseDbTypes::STRING';
				$auto_increment = strpos($column['extra'], 'auto_increment') !== false ? 'true' : 'false';
				$primary_key = $column['key'] === 'PRI' ? 'true' : 'false';
				$nullable = $column['nullable'] === 'YES' ? 'true' : 'false';
				$column_name = $this->toLowerFirstChar($column['name']);

				$class_content[] = "        \$this->setColumn('{$column_name}', '{$column['name']}', {$php_type}, {$primary_key}, true, false, {$nullable}, {$auto_increment});\n";
			}

			foreach ($columns as $column) {
				$default_value = in_array($column['type'], ['int', 'tinyint', 'smallint', 'mediumint', 'bigint']) ? '0' : "''";
				$class_content[] = "        \$this->{$this->toLowerFirstChar($column['name'])} = {$default_value};\n";
			}

			$class_content[] = "        return;\n";
			$class_content[] = "    }\n";
		}
	}

	// Usage example
	$columns = [
		['name' => 'ID', 'type' => 'int', 'key' => 'PRI', 'nullable' => 'NO', 'extra' => 'auto_increment'],
		['name' => 'Name', 'type' => 'varchar', 'key' => '', 'nullable' => 'YES', 'extra' => ''],
		// Add more columns as needed
	];

	$generator = new ClassGenerator();
	$generator->generateClass('ExampleTable', $columns);

