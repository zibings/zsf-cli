<?php

	namespace Zsf\Utils\SchemaWriter;

	use Stoic\Log\Logger;
	use Stoic\Utilities\FileHelper;

	abstract class ISchemaWriter {
		protected FileHelper $fileHelper;
		protected Logger $log;


		/**
		 * Instantiates a new ISchemaWriter object with file helper and logging resources.
		 *
		 * @param FileHelper $fileHelper
		 * @param Logger|null $log
		 */
		public function __construct(FileHelper $fileHelper, ?Logger $log = null) {
			$this->fileHelper = $fileHelper;
			$this->log        = $log ?? new Logger();

			return;
		}

		/**
		 * Writes the provided schema as an API file to the specified output path.
		 *
		 * @param string $outputPath
		 * @return void
		 */
		abstract public function writeApi(string $outputPath) : void;

		/**
		 * Writes the provided schema as a model file to the specified output path.
		 *
		 * @param string $outputPath
		 * @return void
		 */
		abstract public function writeModel(string $outputPath) : void;

		/**
		 * Writes the provided schema as a repository file to the specified output path.
		 *
		 * @param string $outputPath
		 * @return void
		 */
		abstract public function writeRepository(string $outputPath) : void;
	}
