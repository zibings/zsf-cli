<?= '<?php' ?>

	namespace <?= '???' ?>;  // Use short tag for namespace

	use Stoic\Log\Logger;
	use Stoic\Pdo\PdoHelper;
	use Stoic\Web\Api\Response;
	use Stoic\Web\Api\Stoic;
	use Stoic\Web\Request;

	use Zibings\ApiController;

	// Create
	// Read
	// Update
	// Delete

	class <?= $this->e($ClassName) ?>API extends ApiController {
		public function create<?= $this->e($ClassName) ?>(Request $request, array $matches = null): Response {
			$ret = $this->newResponse();
			$params = $request->getInput();

			if ($this->tryGetParams($ret, $request, [<?= html_entity_decode($this->e($PrimaryKeyArgsStrings)) ?>]) === false) {
				return $ret;
			}

			$entity = new <?= $this->e($ClassName) ?>($this->db, $this->log);
<?php foreach ($Columns as $column): ?>
<?php if ($column->type === 'string'): ?>
			$entity-><?= $this->e($column->name) ?> = $params->getString("<?= $this->e($column->name) ?>");
<?php elseif ($column->type === 'int'): ?>
			$entity-><?= $this->e($column->name) ?> = $params->getInt("<?= $this->e($column->name) ?>");
<?php elseif ($column->type === 'bool'): ?>
			$entity-><?= $this->e($column->name) ?> = $params->getBool("<?= $this->e($column->name) ?>");
<?php elseif ($column->type === 'float'): ?>
			$entity-><?= $this->e($column->name) ?> = $params->getFloat("<?= $this->e($column->name) ?>");
<?php endif; ?>
<?php endforeach; ?>

			$create = $entity->create();
			if ($create->isBad()) {
				if ($create->hasMessages()) {
					$ret->setAsError($create->getMessages()[0]);
				} else {
					$ret->setAsError('Failed to create <?= $this->e($ClassName) ?>');
				}
			}

			return $ret;
		}

		public function read<?= $this->e($ClassName) ?>(Request $request, array $matches = null): Response {
			$ret = $this->newResponse();
			$params = $request->getInput();

			if ($this->tryGetParams($ret, $request, [<?= html_entity_decode($this->e($PrimaryKeyArgsStrings)) ?>]) === false) {
				return $ret;
			}

			$entity = new <?= $this->e($ClassName) ?>($this->db, $this->log);
<?php foreach ($PrimaryKeys as $pk): ?>
<?php if ($pk->type === 'string'): ?>
			$entity-><?= $this->e($pk->name) ?> = $params->getString("<?= $this->e($pk->name) ?>");
<?php elseif ($pk->type === 'int'): ?>
			$entity-><?= $this->e($pk->name) ?> = $params->getInt("<?= $this->e($pk->name) ?>");
<?php elseif ($pk->type === 'bool'): ?>
			$entity-><?= $this->e($pk->name) ?> = $params->getBool("<?= $this->e($pk->name) ?>");
<?php elseif ($pk->type === 'float'): ?>
			$entity-><?= $this->e($pk->name) ?> = $params->getFloat("<?= $this->e($pk->name) ?>");
<?php endif; ?>
<?php endforeach; ?>

			$read = $entity->read();
			if ($read->isBad()) {
				if ($read->hasMessages()) {
					$ret->setAsError($read->getMessages()[0]);
				} else {
					$ret->setAsError('Failed to get <?= $this->e($ClassName) ?>');
				}
			}

			return $ret;
		}


		public function update<?= $this->e($ClassName) ?>(Request $request, array $matches = null): Response {
			$ret = $this->newResponse();
			$params = $request->getInput();

			if ($this->tryGetParams($ret, $request, [<?= html_entity_decode($this->e($ColumnArgsStrings)) ?>]) === false) {
				return $ret;
			}

			$entity = new <?= $this->e($ClassName) ?>($this->db, $this->log);
<?php foreach ($Columns as $column): ?>
<?php if ($column->type === 'string'): ?>
			$entity-><?= $this->e($column->name) ?> = $params->getString("<?= $this->e($column->name) ?>");
<?php elseif ($column->type === 'int'): ?>
			$entity-><?= $this->e($column->name) ?> = $params->getInt("<?= $this->e($column->name) ?>");
<?php elseif ($column->type === 'bool'): ?>
			$entity-><?= $this->e($column->name) ?> = $params->getBool("<?= $this->e($column->name) ?>");
<?php elseif ($column->type === 'float'): ?>
			$entity-><?= $this->e($column->name) ?> = $params->getFloat("<?= $this->e($column->name) ?>");
<?php endif; ?>
<?php endforeach; ?>

			$update = $entity->update();
			if ($update->isBad()) {
				if ($update->hasMessages()) {
					$ret->setAsError($update->getMessages()[0]);
				} else {
					$ret->setAsError('Failed to update <?= $this->e($ClassName) ?>');
				}
			}

			return $ret;
		}

		public function delete<?= $this->e($ClassName) ?>(Request $request, array $matches = null): Response {
			$ret = $this->newResponse();
			$params = $request->getInput();

			if ($this->tryGetParams($ret, $request, [<?= html_entity_decode($this->e($PrimaryKeyArgsStrings)) ?>]) === false) {
				return $ret;
			}

			$entity = new <?= $this->e($ClassName) ?>($this->db, $this->log);
<?php foreach ($PrimaryKeys as $pk): ?>
<?php if ($pk->type === 'string'): ?>
			$entity-><?= $this->e($pk->name) ?> = $params->getString("<?= $this->e($pk->name) ?>");
<?php elseif ($pk->type === 'int'): ?>
			$entity-><?= $this->e($pk->name) ?> = $params->getInt("<?= $this->e($pk->name) ?>");
<?php elseif ($pk->type === 'bool'): ?>
			$entity-><?= $this->e($pk->name) ?> = $params->getBool("<?= $this->e($pk->name) ?>");
<?php elseif ($pk->type === 'float'): ?>
			$entity-><?= $this->e($pk->name) ?> = $params->getFloat("<?= $this->e($pk->name) ?>");
<?php endif; ?>
<?php endforeach; ?>

			$delete = $entity->delete();
			if ($delete->isBad()) {
				if ($delete->hasMessages()) {
					$ret->setAsError($delete->getMessages()[0]);
				} else {
					$ret->setAsError('Failed to delete <?= $this->e($ClassName) ?>');
				}
			}

			return $ret;
		}

	}