<?= '<?php' ?>


	namespace <?= $Namespace ?>;

	use Stoic\Log\Logger;
	use Stoic\Pdo\BaseDbColumnFlags as BCF;
	use Stoic\Pdo\BaseDbTypes;
	use Stoic\Pdo\PdoHelper;
	use Stoic\Pdo\StoicDbModel;
	use Stoic\Utilities\ReturnHelper;

	class <?= $this->e($ClassName) ?> extends StoicDbModel {
<?php foreach ($Columns as $column): ?>
		public <?= $this->e($column->getPhpType()) ?> $<?= $this->e($column->getName($CamelCase)) ?>;
<?php endforeach; ?>


		public static function from<?= $this->e(ucfirst($FromPrimaryKey)) ?>(<?= $this->e($PrimaryKeyArgsWithTypes) ?>, PdoHelper $db, Logger $log = null): <?= $this->e($ClassName) ?> {
			$ret = new <?= $this->e($ClassName) ?>($db, $log);
<?php foreach ($PrimaryKeys as $pk): ?>
			$ret-><?= $this->e($pk->getName($CamelCase)) ?> = $<?= $this->e($pk->getName($CamelCase)) ?>;
<?php endforeach; ?>
			$ret->read();

			return $ret;
		}


		/**
 		 * Determines if the system should attempt to create a <?= $this->e($ClassName) ?> in the database.
		 *
		 * @return bool|ReturnHelper
		 */
		protected function __canCreate() : bool|ReturnHelper {
			$ret = new ReturnHelper();

			$ret->makeGood();

			return $ret;
		}

		/**
		 * Determines if the system should attempt to delete a <?= $this->e($ClassName) ?> from the database.
		 *
		 * @return bool|ReturnHelper
		 */
		protected function __canDelete() : bool|ReturnHelper {
			$ret = new ReturnHelper();

			$ret->makeGood();

			return $ret;
		}

		/**
		 * Determines if the system should attempt to read a <?= $this->e($ClassName) ?> from the database.
		 *
		 * @return bool|ReturnHelper
		 */
		protected function __canRead() : bool|ReturnHelper {
			$ret = new ReturnHelper();

			$ret->makeGood();

			return $ret;
		}

		/**
		 * Determines if the system should attempt to update a <?= $this->e($ClassName) ?> in the database.
		 *
		 * @return bool|ReturnHelper
		 */
		protected function __canUpdate() : bool|ReturnHelper {
			$ret = new ReturnHelper();

			$ret->makeGood();

			return $ret;
		}

		/**
		 * Initializes a new <?= $this->e($ClassName) ?> object.
		 *
		 * @throws \Exception
		 * @return void
		 */
		protected function __setupModel() : void {
<?php foreach ($Columns as $column): ?>
			$this->setColumn('<?= $this->e($column->getName($CamelCase)) ?>', '<?= $this->e($column->getName()) ?>', BaseDbTypes::<?= $this->e($column->getModelType()) ?><?php if (count($column->getFlags()) > 0): ?>, BCF::<?= $this->e(implode(' | BCF::', $column->getFlags())) ?><?php endif; ?>);
<?php endforeach; ?>

<?php foreach ($Columns as $column): ?>
<?php if ($column->getPhpType() === 'string'): ?>
			$this-><?= $this->e($column->getName($CamelCase)) ?> = "";
<?php elseif ($column->getPhpType() === 'int'): ?>
			$this-><?= $this->e($column->getName($CamelCase)) ?> = 0;
<?php elseif ($column->getPhpType() === 'bool'): ?>
			$this-><?= $this->e($column->getName($CamelCase)) ?> = false;
<?php elseif ($column->getPhpType() === 'float'): ?>
			$this-><?= $this->e($column->getName($CamelCase)) ?> = 0.0;
<?php elseif ($column->getPhpType() === '\DateTimeInterface'): ?>
			$this-><?= $this->e($column->getName($CamelCase)) ?> = new \DateTime('now', new \DateTimeZone('UTC'));
<?php endif; ?>
<?php endforeach; ?>

			return;
		}
	}