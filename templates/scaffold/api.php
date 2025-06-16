<?= '<?php' ?>


	namespace <?= '???' ?>;  // Use short tag for namespace

	use Stoic\Web\Api\Response;
	use Stoic\Web\Request;
	use Zibings\ApiController;

	class <?= $this->e($ClassName) ?>API extends ApiController {
		/**
		 * Creates/Inserts a <?= $this->e($ClassName) ?> into the database.
		 *
		 * @return Response
		 */
		public function create<?= $this->e($ClassName) ?>(Request $request, array $matches = null): Response {
			$ret    = $this->newResponse();
			$params = $request->getInput();

			if ($this->tryGetParams($ret, $request, [<?= html_entity_decode($this->e($PrimaryKeyArgsStrings)) ?>]) === false) {
				return $ret;
			}

			$entity = new <?= $this->e($ClassName) ?>($this->db, $this->log);
<?php foreach ($Columns as $column): ?>
<?php if ($column->getPhpType() === 'string'): ?>
			$entity-><?= $this->e($column->getName($CamelCase)) ?> = $params->getString("<?= $this->e($column->getName($CamelCase)) ?>");
<?php elseif ($column->getPhpType() === 'int'): ?>
			$entity-><?= $this->e($column->getName($CamelCase)) ?> = $params->getInt("<?= $this->e($column->getName($CamelCase)) ?>");
<?php elseif ($column->getPhpType() === 'bool'): ?>
			$entity-><?= $this->e($column->getName($CamelCase)) ?> = $params->getBool("<?= $this->e($column->getName($CamelCase)) ?>");
<?php elseif ($column->getPhpType() === 'float'): ?>
			$entity-><?= $this->e($column->getName($CamelCase)) ?> = $params->getFloat("<?= $this->e($column->getName($CamelCase)) ?>");
<?php endif; ?>
<?php endforeach; ?>

			$create = $entity->create();

			if ($create->isBad()) {
				$ret->assignReturnHelperError($ret, $create, 'Failed to create <?= $this->e($ClassName) ?>');

				return $ret;
			}

			$ret->setData($entity);

			return $ret;
		}

		/**
		 * Get/Read a <?= $this->e($ClassName) ?> from the database.
		 *
		 * @return Response
		 */
		public function read<?= $this->e($ClassName) ?>(Request $request, array $matches = null): Response {
			$ret    = $this->newResponse();
			$params = $request->getInput();

			if ($this->tryGetParams($ret, $request, [<?= html_entity_decode($this->e($PrimaryKeyArgsStrings)) ?>]) === false) {
				return $ret;
			}

			$entity = new <?= $this->e($ClassName) ?>($this->db, $this->log);
<?php foreach ($PrimaryKeys as $pk): ?>
<?php if ($pk->getPhpType() === 'string'): ?>
			$entity-><?= $this->e($pk->getName($CamelCase)) ?> = $params->getString("<?= $this->e($pk->getName($CamelCase)) ?>");
<?php elseif ($pk->getPhpType() === 'int'): ?>
			$entity-><?= $this->e($pk->getName($CamelCase)) ?> = $params->getInt("<?= $this->e($pk->getName($CamelCase)) ?>");
<?php elseif ($pk->getPhpType() === 'bool'): ?>
			$entity-><?= $this->e($pk->getName($CamelCase)) ?> = $params->getBool("<?= $this->e($pk->getName($CamelCase)) ?>");
<?php elseif ($pk->getPhpType() === 'float'): ?>
			$entity-><?= $this->e($pk->getName($CamelCase)) ?> = $params->getFloat("<?= $this->e($pk->getName($CamelCase)) ?>");
<?php endif; ?>
<?php endforeach; ?>

			$read = $entity->read();

			if ($read->isBad()) {
				$ret->assignReturnHelperError($ret, $read, 'Failed to read <?= $this->e($ClassName) ?>');

				return $ret;
			}

			$ret->setData($entity);

			return $ret;
		}


		/**
		 * Modify/Update a <?= $this->e($ClassName) ?> from the database.
		 *
		 * @return Response
		 */
		public function update<?= $this->e($ClassName) ?>(Request $request, array $matches = null): Response {
			$ret    = $this->newResponse();
			$params = $request->getInput();

			if ($this->tryGetParams($ret, $request, [<?= html_entity_decode($this->e($ColumnArgsStrings)) ?>]) === false) {
				return $ret;
			}

			$entity = new <?= $this->e($ClassName) ?>($this->db, $this->log);
<?php foreach ($Columns as $column): ?>
<?php if ($column->type === 'string'): ?>
			$entity-><?= $this->e($column->getName($CamelCase)) ?> = $params->getString("<?= $this->e($column->getName($CamelCase)) ?>");
<?php elseif ($column->type === 'int'): ?>
			$entity-><?= $this->e($column->getName($CamelCase)) ?> = $params->getInt("<?= $this->e($column->getName($CamelCase)) ?>");
<?php elseif ($column->type === 'bool'): ?>
			$entity-><?= $this->e($column->getName($CamelCase)) ?> = $params->getBool("<?= $this->e($column->getName($CamelCase)) ?>");
<?php elseif ($column->type === 'float'): ?>
			$entity-><?= $this->e($column->getName($CamelCase)) ?> = $params->getFloat("<?= $this->e($column->getName($CamelCase)) ?>");
<?php endif; ?>
<?php endforeach; ?>

			$update = $entity->update();

			if ($update->isBad()) {
				$ret->assignReturnHelperError($ret, $update, 'Failed to update <?= $this->e($ClassName) ?>');

				return $ret;
			}

			$ret->setData($entity);

			return $ret;
		}

		/**
		 * Delete a <?= $this->e($ClassName) ?> from the database.
		 *
		 * @return Response
		 */
		public function delete<?= $this->e($ClassName) ?>(Request $request, array $matches = null): Response {
			$ret    = $this->newResponse();
			$params = $request->getInput();

			if ($this->tryGetParams($ret, $request, [<?= html_entity_decode($this->e($PrimaryKeyArgsStrings)) ?>]) === false) {
				return $ret;
			}

			$entity = new <?= $this->e($ClassName) ?>($this->db, $this->log);
<?php foreach ($PrimaryKeys as $pk): ?>
<?php if ($pk->type === 'string'): ?>
			$entity-><?= $this->e($pk->getName($CamelCase)) ?> = $params->getString("<?= $this->e($pk->getName($CamelCase)) ?>");
<?php elseif ($pk->type === 'int'): ?>
			$entity-><?= $this->e($pk->getName($CamelCase)) ?> = $params->getInt("<?= $this->e($pk->getName($CamelCase)) ?>");
<?php elseif ($pk->type === 'bool'): ?>
			$entity-><?= $this->e($pk->getName($CamelCase)) ?> = $params->getBool("<?= $this->e($pk->getName($CamelCase)) ?>");
<?php elseif ($pk->type === 'float'): ?>
			$entity-><?= $this->e($pk->getName($CamelCase)) ?> = $params->getFloat("<?= $this->e($pk->getName($CamelCase)) ?>");
<?php endif; ?>
<?php endforeach; ?>

			$delete = $entity->delete();

			if ($delete->isBad()) {
				$ret->assignReturnHelperError($ret, $delete, 'Failed to delete <?= $this->e($ClassName) ?>');

				return $ret;
			}

			$ret->setData($entity);

			return $ret;
		}

		protected function registerEndpoints() : void {
			$this->registerEndpoint('POST',   '/\/?<?= $this->e($ClassName) ?>\/?$/i', 'create<?= $this->e($ClassName) ?>');
			$this->registerEndpoint('GET',    '/\/?<?= $this->e($ClassName) ?>\/?$/i', 'read<?= $this->e($ClassName) ?>');
			$this->registerEndpoint('PATCH',  '/\/?<?= $this->e($ClassName) ?>\/?$/i', 'update<?= $this->e($ClassName) ?>');
			$this->registerEndpoint('DELETE', '/\/?<?= $this->e($ClassName) ?>\/?$/i', 'delete<?= $this->e($ClassName) ?>');

			return;
		}
	}