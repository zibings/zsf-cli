<?= '<?php' ?>

	namespace <?= '???' ?>;  // Use short tag for namespace

	use Stoic\Log\Logger;
	use Stoic\Pdo\BaseDbQueryTypes;
	use Stoic\Pdo\BaseDbTypes;
	use Stoic\Pdo\PdoHelper;
	use Stoic\Pdo\StoicDbModel;
	use Stoic\Utilities\ReturnHelper;
	use Stoic\Pdo\BaseDbColumnFlags as BCF;

	class <?= $this->e($ClassName) ?> extends StoicDbModel {
<?php foreach ($Columns as $column): ?>
		public <?= $this->e($column->type) ?> $<?= $this->e($column->name) ?>;
<?php endforeach; ?>

		protected function __setupModel() : void {
<?php foreach ($Columns as $column): ?>
			$this->setColumn('<?= $this->e($column->name) ?>', '<?= $this->e($column->name) ?>', <?= $this->e($column->baseType) ?><?php if ($this->e($column->flagsToString) !== ""): ?>, <?= $this->e($column->flagsToString) ?><?php endif; ?>);
<?php endforeach; ?>

<?php foreach ($Columns as $column): ?>
<?php if ($column->type === 'string'): ?>
			$this-><?= $this->e($column->name) ?> = "";
<?php elseif ($column->type === 'int'): ?>
			$this-><?= $this->e($column->name) ?> = 0;
<?php elseif ($column->type === 'bool'): ?>
			$this-><?= $this->e($column->name) ?> = false;
<?php elseif ($column->type === 'float'): ?>
			$this-><?= $this->e($column->name) ?> = 0.0;
<?php elseif ($column->type === 'DateTime'): ?>
			$this-><?= $this->e($column->name) ?> = new \DateTime();
<?php endif; ?>
<?php endforeach; ?>
		}

		public static function from<?= $this->e($FromPrimaryKey) ?>(<?= $this->e($PrimaryKeyArgsWithTypes) ?>,  PdoHelper $db, Logger $log = null): <?= $this->e($ClassName) ?> {
			$ret = new <?= $this->e($ClassName) ?>($db, $log);
<?php foreach ($PrimaryKeys as $pk): ?>
			$ret-><?= $this->e($pk->name) ?> = $<?= $this->e($pk->name) ?>;
<?php endforeach; ?>
			$ret->read();
			return $ret;
		}
	}