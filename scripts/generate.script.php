<?php

	namespace Zsf\Scripts;

	use AndyM84\Config\ConfigContainer;

	use Stoic\Pdo\BaseDbTypes;
	use Stoic\Utilities\ConsoleHelper;
	use Stoic\Utilities\FileHelper;

	use Zsf\Utils\ZsfCliScript;

	class GenerateArguments {
		/**
		 * Create a GenerateArguments instance from an array.
		 *
		 * @param array $input
		 * @return GenerateArguments
		 */
		public static function fromArray(array $input) : GenerateArguments {
			return new GenerateArguments(
				$input['type'] ?? '',
				$input['model'] ?? null,
				$input['namespace'] ?? null,
				$input['out-dir'] ?? '',
				$input['overwrite'] ?? false,
				$input['singleFile'] ?? false
			);
		}


		/**
		 * Instantiates a GenerateArguments object with the provided parameters.
		 *
		 * @param string $type
		 * @param null|string $model
		 * @param null|string $namespace
		 * @param string $outDir
		 * @param bool $overwrite
		 * @param bool $singleFile
		 */
		public function __construct(
			public string $type,
			public null|string $model,
			public null|string $namespace,
			public string $outDir,
			public bool $overwrite,
			public bool $singleFile = false
		) {
			return;
		}
	}

	class GenerateScript implements ZsfCliScript {
		protected function __getInput(ConsoleHelper $ch) : GenerateArguments {
			$ret = [
				'type'       => $ch->getParameterWithDefault('t', 'type', '', true),
				'model'      => $ch->getParameterWithDefault('m', 'model', null, true),
				'namespace'  => $ch->getParameterWithDefault('n', 'namespace', null, true),
				'out-dir'    => $ch->getParameterWithDefault('o', 'out-dir', '', true),
				'overwrite'  => $ch->hasShortLongArg('w', 'overwrite', true) ? true : null,
				'singleFile' => $ch->hasShortLongArg('s', 'single-file', true) ? true : null,
			];

			$supplied        = [];
			$required        = ['t.type', 'o.out-dir', 'm.model||n.namespace'];
			$validationFuncs = [
				'empty'  => function (mixed $value) : bool {
					return !empty($value);
				},
				'type'   => function (mixed $value) : bool {
					return in_array(strtolower($value), ['ts']);
				},
				'yesno'  => function (mixed $value) : bool {
					return in_array(strtolower($value), ['yes', 'no', 'y', 'n']);
				},
				'source' => function (mixed $value) : bool {
					return in_array(strtolower($value), ['model', 'namespace']);
				}
			];

			foreach ($required as $param) {
				if (str_contains($param, '||')) {
					$params = explode('||', $param);

					foreach ($params as $p) {
						$parts = explode('.', $p);

						if ($ch->hasShortLongArg($parts[0], $parts[1], true)) {
							$supplied[] = $param;

							break;
						}
					}
				}

				$parts = explode('.', $param);

				if ($ch->hasShortLongArg($parts[0], $parts[1], true)) {
					$supplied[] = $param;
				}
			}

			if (count($supplied) === count($required)) {
				if (!$validationFuncs['type']($ret['type'])) {
					$ch->putLine('Aborting script execution, invalid type specified.  Valid types are: ts');

					exit;
				}

				if (!$validationFuncs['empty']($ret['out-dir'])) {
					$ch->putLine('Aborting script execution, out-dir cannot be empty.');

					exit;
				}

				if ($ret['overwrite'] === null) {
					$ret['overwrite'] = false;
				}

				if ($ret['singleFile'] === null) {
					$ret['singleFile'] = false;
				}

				return GenerateArguments::fromArray($ret);
			}

			$missing = array_merge(
				array_diff($required, $supplied),
				array_diff($supplied, $required)
			);

			$sanitationFuncs = [
				'trim'         => function (mixed $value) : string {
					return trim($value);
				},
				'trimAndLower' => function (mixed $value) : string {
					return strtolower(trim($value));
				},
				'yesno'        => function (mixed $value) : string {
					$value = strtolower(trim($value));

					if ($value == 'yes' || $value == 'y') {
						return 'yes';
					}

					return 'no';
				}
			];

			$maxTries = 3;

			foreach ($missing as $param) {
				switch ($param) {
					case 'type':
						$type = $ch->getQueriedInput(
							'What type of file(s) do you want to generate?',
							'ts',
							'Invalid type specified, valid types are: ts',
							$maxTries,
							$validationFuncs['type'],
							$sanitationFuncs['trimAndLower']
						);

						if ($type->isBad()) {
							$ch->putLine();
							$ch->putLine('Aborting script execution, invalid type specified.  Valid types are: ts');

							exit;
						}

						$ret['type'] = $type->getResults()[0];

						break;

					case 'out-dir':
						$outDir = $ch->getQueriedInput(
							'What is the output directory for the generated file(s)?',
							'./generated',
							'Invalid output directory specified, must be a valid path',
							$maxTries,
							$validationFuncs['empty'],
							$sanitationFuncs['trim']
						);

						if ($outDir->isBad()) {
							$ch->putLine();
							$ch->putLine('Aborting script execution, invalid output directory specified.');

							exit;
						}

						$ret['out-dir'] = $outDir->getResults()[0];

						break;

					case 'm.model||n.namespace':
						$source = $ch->getQueriedInput(
							'Would you like to generate files based on a model or a namespace?',
							'model',
							'Invalid source specified, must be either "model" or "namespace"',
							$maxTries,
							$validationFuncs['source'],
							$sanitationFuncs['trimAndLower']
						);

						if ($source->isBad()) {
							$ch->putLine();
							$ch->putLine('Aborting script execution, invalid source specified.  Must be either "model" or "namespace"');

							exit;
						}

						$chosenSource = $source->getResults()[0];

						if ($chosenSource == 'model') {
							$model = $ch->getQueriedInput(
								'What is the model name you want to generate files for?',
								null,
								'Invalid model name specified, must be a valid model class name',
								$maxTries,
								$validationFuncs['empty'],
								$sanitationFuncs['trim']
							);

							if ($model->isBad()) {
								$ch->putLine();
								$ch->putLine('Aborting script execution, invalid model name specified.');

								exit;
							}

							$ret['model'] = $model->getResults()[0];
						} else {
							$namespace = $ch->getQueriedInput(
								'What is the namespace you want to generate files for?',
								null,
								'Invalid namespace specified, must be a valid namespace',
								$maxTries,
								$validationFuncs['empty'],
								$sanitationFuncs['trim']
							);

							if ($namespace->isBad()) {
								$ch->putLine();
								$ch->putLine('Aborting script execution, invalid namespace specified.');

								exit;
							}

							$ret['namespace'] = $namespace->getResults()[0];
						}

						break;

					default:
						break;
				}
			}

			if (!$ch->hasShortLongArg('w', 'overwrite', true)) {
				$overwrite = $ch->getQueriedInput(
					'Do you want to overwrite existing files? (yes/no)',
					'no',
					'Invalid input, must be either "yes" or "no"',
					$maxTries,
					$validationFuncs['yesno'],
					$sanitationFuncs['yesno']
				);

				if ($overwrite->isBad()) {
					$ch->putLine();
					$ch->putLine('Aborting script execution, invalid input for overwrite.');

					exit;
				}

				$ret['overwrite'] = $overwrite->getResults()[0] == 'yes';

				$ch->putLine();
			}

			return GenerateArguments::fromArray($ret);
		}

		public function help() : string {
			return <<< HELP_TEXT
ZSF CLI Generate Script
-----------------------
Description:           Generate file(s) using meta data from existing db model(s)
Interactive Usage:     vendor/bin/zsf-cli generate
Non-Interactive Usage: vendor/bin/zsf-cli generate --type=js --model=model_name --out-dir=out_dir
                       vendor/bin/zsf-cli generate --type=ts --namespace=namespace --out-dir=out_dir
                       vendor/bin/zsf-cli generate --type=ts --model=model_name --out-dir=out_dir --overwrite
HELP_TEXT;
		}

		public function helpInternal() : string {
			return <<< HELP_TEXT
Description:           Generate file(s) using meta data from existing db models
Interactive Usage:     vendor/bin/zsf-cli generate
Non-Interactive Usage: vendor/bin/zsf-cli generate --model=Zibings\User --type=ts --out-dir=./generated
                       vendor/bin/zsf-cli generate --namespace=Zibings --type=ts --out-dir=./generated
                       vendor/bin/zsf-cli generate --model=Zibings\User --type=ts --out-dir=./generated --overwrite
HELP_TEXT;
		}

		public function key() : string {
			return 'generate';
		}

		public function oneLineDescription() : string {
			return 'Generate file(s) using meta data from existing db models';
		}

		public function run(ConsoleHelper $ch, FileHelper $fh, ConfigContainer $config) : void {
			$ch->putLine('ZSF Generate Script');
			$ch->putLine('-------------------');
			$ch->putLine();

			if ($ch->hasShortLongArg('help', 'h')) {
				$ch->putLine($this->helpInternal());

				return;
			}

			$input = $this->__getInput($ch);

			$ch->putLine('Input:');
			$ch->putLine('  Type:          ' . $input->type);
			$ch->putLine('  Out Directory: ' . $input->outDir);
			$ch->putLine('  Model:         ' . ($input->model ?? 'N/A'));
			$ch->putLine('  Namespace:     ' . ($input->namespace ?? 'N/A'));
			$ch->putLine('  Overwrite:     ' . ($input->overwrite ? 'true' : 'false'));
			$ch->putLine('  Single File:   ' . ($input->singleFile ? 'true' : 'false'));
			$ch->putLine();

			if (!$fh->folderExists($input->outDir)) {
				$fh->makeFolder($input->outDir, 0755, true);
			}

			$modelClasses = [];
			$blankDb      = new \Stoic\Pdo\PdoHelper('sqlite::memory:');

			foreach (get_declared_classes() as $class) {
				if (!is_subclass_of($class, \Stoic\Pdo\BaseDbModel::class)) {
					continue;
				}

				if ($input->model !== null && !str_ends_with($class, $input->model)) {echo($class);
					continue;
				}

				if ($input->namespace !== null && !str_starts_with($class, $input->namespace)) {
					continue;
				}

				$modelClasses[] = $class;
			}

			if (count($modelClasses) === 0) {
				$ch->putLine('No model classes found matching the specified criteria.');

				return;
			}

			$models     = [];
			$translator = new \Zsf\Utils\Translators\TypeScriptTranslator();

			$typeLookup = [
				BaseDbTypes::BOOLEAN  => $translator->toTranslatedType('bool'),
				BaseDbTypes::DATETIME => $translator->toTranslatedType('\DateTimeInterface'),
				BaseDbTypes::INTEGER  => $translator->toTranslatedType('int'),
				BaseDbTypes::STRING   => $translator->toTranslatedType('string'),
			];

			$defaultLookup = [
				BaseDbTypes::BOOLEAN  => $translator->defaultTranslatedValue('bool'),
				BaseDbTypes::DATETIME => $translator->defaultTranslatedValue('\DateTimeInterface'),
				BaseDbTypes::INTEGER  => $translator->defaultTranslatedValue('int'),
				BaseDbTypes::STRING   => $translator->defaultTranslatedValue('string'),
			];

			$ch->putLine('Found ' . count($modelClasses) . ' model class(es):');

			foreach ($modelClasses as $modelClass) {
				$cls       = new $modelClass($blankDb);
				$fields    = $cls->getDbColumns();
				$shortName = $cls->getShortClassName();

				$model       = [
					'className'  => $shortName,
					'properties' => []
				];

				foreach ($fields as $property => $field) {
					$model['properties'][$property] = [
						'defaultValue' => $defaultLookup[$field->type->getValue()],
						'name'         => $property,
						'type'         => $typeLookup[$field->type->getValue()],
					];
				}

				$models[] = $model;
			}

			$tplRootPath = '~/templates/generate';

			if (!$fh->folderExists($tplRootPath)) {
				$tplRootPath = '~/vendor/zibings/zsf-cli/templates/generate';

				if (!$fh->folderExists($tplRootPath)) {
					$ch->putLine('Aborting script execution, generate template folder not found');

					exit;
				}
			}

			$engine = new \League\Plates\Engine($fh->pathJoin($tplRootPath), 'tpl');

			if ($input->singleFile) {
				$output     = $engine->render('ts-model', ['models' => $models]);
				$outputPath = $fh->pathJoin($input->outDir, 'generated.ts');

				$ch->put("  - Generating generated.ts.. ");

				if ($input->overwrite || !$fh->fileExists($outputPath)) {
					$fh->putContents($outputPath, $output);
					$ch->putLine("  OK");
				} else {
					$ch->putLine("  SKIPPED (EXISTS)");
				}
			} else {
				foreach ($models as $model) {
					$fileName = $model['className'] . '.ts';

					$ch->put("  - Generating $fileName.. ");

					$output     = $engine->render('ts-model', ['models' => [$model]]);
					$outputPath = $fh->pathJoin($input->outDir, $fileName);

					if ($input->overwrite || !$fh->fileExists($outputPath)) {
						$fh->putContents($outputPath, $output);
						$ch->putLine("  OK");
					} else {
						$ch->putLine("  SKIPPED (EXISTS)");
					}
				}
			}

			$ch->putLine();
			$ch->putLine('All files generated successfully.');
			$ch->putLine();

			return;
		}
	}
