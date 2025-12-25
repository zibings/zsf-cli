<?= '<?php' ?>


	namespace <?= $ApiNamespace ?>;

	use <?= $Namespace ?>\<?= $this->e($ClassName) ?>;
	use <?= $Namespace ?>\<?= $this->e($PluralClassName) ?> as <?= $this->e($ClassName) ?>Repo;

	use Stoic\Web\Api\Response;
	use Stoic\Web\Request;

	use Zibings\ApiController;

	class <?= $this->e($PluralClassName) ?> extends ApiController {
		/**
		 * Creates/Inserts a <?= $this->e($ClassName) ?> into the database.
		 *
		 * @param Request $request The current request which routed to the endpoint.
		 * @param null|array $matches Array of matches returned by endpoint regex pattern.
		 * @throws \Exception
		 * @return Response
		 */
		public function create<?= $this->e($ClassName) ?>(Request $request, null|array $matches = null) : Response {
			$ret    = $this->newResponse();
			$params = $request->getInput();

			if (!$params->hasAll(<?= html_entity_decode($this->e($PrimaryKeyArgsStrings)) ?>)) {
				$ret->setAsError("Missing required parameters to create <?= $this->e($ClassName) ?>.");

				return $ret;
			}

			<?= str_pad('$entity', $WidestColumnNameLength + 9) ?> = new <?= $this->e($ClassName) ?>($this->db, $this->log);
<?php foreach ($Columns as $column): ?>
<?php if ($column->getPhpType() === 'string'): ?>
			$entity-><?= $this->e(str_pad($column->getName($PreserveCase), $WidestColumnNameLength)) ?> = $params->getString("<?= $this->e($column->getName($PreserveCase)) ?>");
<?php elseif ($column->getPhpType() === 'int'): ?>
			$entity-><?= $this->e(str_pad($column->getName($PreserveCase), $WidestColumnNameLength)) ?> = $params->getInt("<?= $this->e($column->getName($PreserveCase)) ?>");
<?php elseif ($column->getPhpType() === 'bool'): ?>
			$entity-><?= $this->e(str_pad($column->getName($PreserveCase), $WidestColumnNameLength)) ?> = $params->getBool("<?= $this->e($column->getName($PreserveCase)) ?>");
<?php elseif ($column->getPhpType() === 'float'): ?>
			$entity-><?= $this->e(str_pad($column->getName($PreserveCase), $WidestColumnNameLength)) ?> = $params->getFloat("<?= $this->e($column->getName($PreserveCase)) ?>");
<?php endif; ?>
<?php endforeach; ?>
			<?= str_pad('$create', $WidestColumnNameLength + 9) ?> = $entity->create();

			if ($create->isBad()) {
				$this->assignReturnHelperError($ret, $create, 'Failed to create <?= $this->e($ClassName) ?>');

				return $ret;
			}

			$ret->setData($entity);

			return $ret;
		}

		/**
		 * Get/Read a <?= $this->e($ClassName) ?> from the database.
		 *
		 * @param Request $request The current request which routed to the endpoint.
		 * @param null|array $matches Array of matches returned by endpoint regex pattern.
		 * @throws \Exception
		 * @return Response
		 */
		public function read<?= $this->e($ClassName) ?>(Request $request, null|array $matches = null) : Response {
			$ret    = $this->newResponse();
			$params = $request->getInput();

			if (!$params->hasAll(<?= html_entity_decode($this->e($PrimaryKeyArgsStrings)) ?>)) {
				$ret->setAsError(" <?= $this->e($ClassName) ?>.");

				return $ret;
			}

			<?= str_pad('$entity', $WidestPrimaryKeyNameLength + 9) ?> = new <?= $this->e($ClassName) ?>($this->db, $this->log);
<?php foreach ($PrimaryKeys as $pk): ?>
<?php if ($pk->getPhpType() === 'string'): ?>
			$entity-><?= $this->e(str_pad($pk->getName($PreserveCase), $WidestPrimaryKeyNameLength)) ?> = $params->getString("<?= $this->e($pk->getName($PreserveCase)) ?>");
<?php elseif ($pk->getPhpType() === 'int'): ?>
			$entity-><?= $this->e(str_pad($pk->getName($PreserveCase), $WidestPrimaryKeyNameLength)) ?> = $params->getInt("<?= $this->e($pk->getName($PreserveCase)) ?>");
<?php elseif ($pk->getPhpType() === 'bool'): ?>
			$entity-><?= $this->e(str_pad($pk->getName($PreserveCase), $WidestPrimaryKeyNameLength)) ?> = $params->getBool("<?= $this->e($pk->getName($PreserveCase)) ?>");
<?php elseif ($pk->getPhpType() === 'float'): ?>
			$entity-><?= $this->e(str_pad($pk->getName($PreserveCase), $WidestPrimaryKeyNameLength)) ?> = $params->getFloat("<?= $this->e($pk->getName($PreserveCase)) ?>");
<?php endif; ?>
<?php endforeach; ?>
			<?= str_pad('$read', $WidestPrimaryKeyNameLength + 9) ?> = $entity->read();

			if ($read->isBad()) {
				$this->assignReturnHelperError($ret, $read, 'Failed to read <?= $this->e($ClassName) ?>');

				return $ret;
			}

			$ret->setData($entity);

			return $ret;
		}

		/**
		 * Modify/Update a <?= $this->e($ClassName) ?> from the database.
		 *
		 * @param Request $request The current request which routed to the endpoint.
		 * @param null|array $matches Array of matches returned by endpoint regex pattern.
		 * @throws \Exception
		 * @return Response
		 */
		public function update<?= $this->e($ClassName) ?>(Request $request, null|array $matches = null) : Response {
			$ret    = $this->newResponse();
			$params = $request->getInput();

			if (!$params->hasAll(<?= html_entity_decode($this->e($ColumnArgsStrings)) ?>)) {
				$ret->setAsError("Missing required parameters to update <?= $this->e($ClassName) ?>.");

				return $ret;
			}

			<?= str_pad('$entity', $WidestColumnNameLength + 9) ?> = new <?= $this->e($ClassName) ?>($this->db, $this->log);
<?php foreach ($Columns as $column): ?>
<?php if ($column->getPhpType() === 'string'): ?>
			$entity-><?= $this->e(str_pad($column->getName($PreserveCase), $WidestColumnNameLength)) ?> = $params->getString("<?= $this->e($column->getName($PreserveCase)) ?>");
<?php elseif ($column->getPhpType() === 'int'): ?>
			$entity-><?= $this->e(str_pad($column->getName($PreserveCase), $WidestColumnNameLength)) ?> = $params->getInt("<?= $this->e($column->getName($PreserveCase)) ?>");
<?php elseif ($column->getPhpType() === 'bool'): ?>
			$entity-><?= $this->e(str_pad($column->getName($PreserveCase), $WidestColumnNameLength)) ?> = $params->getBool("<?= $this->e($column->getName($PreserveCase)) ?>");
<?php elseif ($column->getPhpType() === 'float'): ?>
			$entity-><?= $this->e(str_pad($column->getName($PreserveCase), $WidestColumnNameLength)) ?> = $params->getFloat("<?= $this->e($column->getName($PreserveCase)) ?>");
<?php endif; ?>
<?php endforeach; ?>
			<?= str_pad('$update', $WidestColumnNameLength + 9) ?> = $entity->update();

			if ($update->isBad()) {
				$this->assignReturnHelperError($ret, $update, 'Failed to update <?= $this->e($ClassName) ?>');

				return $ret;
			}

			$ret->setData($entity);

			return $ret;
		}

		/**
		 * Delete a <?= $this->e($ClassName) ?> from the database.
		 *
		 * @param Request $request The current request which routed to the endpoint.
		 * @param null|array $matches Array of matches returned by endpoint regex pattern.
		 * @throws \Exception
		 * @return Response
		 */
		public function delete<?= $this->e($ClassName) ?>(Request $request, null|array $matches = null) : Response {
			$ret    = $this->newResponse();
			$params = $request->getInput();

			if (!$params->hasAll(<?= html_entity_decode($this->e($PrimaryKeyArgsStrings)) ?>)) {
				$ret->setAsError("Missing required parameters to delete <?= $this->e($ClassName) ?>.");

				return $ret;
			}

			<?= str_pad('$entity', $WidestPrimaryKeyNameLength + 9) ?> = new <?= $this->e($ClassName) ?>($this->db, $this->log);
<?php foreach ($PrimaryKeys as $pk): ?>
<?php if ($pk->getPhpType() === 'string'): ?>
			$entity-><?= $this->e(str_pad($pk->getName($PreserveCase), $WidestPrimaryKeyNameLength)) ?> = $params->getString("<?= $this->e($pk->getName($PreserveCase)) ?>");
<?php elseif ($pk->getPhpType() === 'int'): ?>
			$entity-><?= $this->e(str_pad($pk->getName($PreserveCase), $WidestPrimaryKeyNameLength)) ?> = $params->getInt("<?= $this->e($pk->getName($PreserveCase)) ?>");
<?php elseif ($pk->getPhpType() === 'bool'): ?>
			$entity-><?= $this->e(str_pad($pk->getName($PreserveCase), $WidestPrimaryKeyNameLength)) ?> = $params->getBool("<?= $this->e($pk->getName($PreserveCase)) ?>");
<?php elseif ($pk->getPhpType() === 'float'): ?>
			$entity-><?= $this->e(str_pad($pk->getName($PreserveCase), $WidestPrimaryKeyNameLength)) ?> = $params->getFloat("<?= $this->e($pk->getName($PreserveCase)) ?>");
<?php endif; ?>
<?php endforeach; ?>
			<?= str_pad('$delete', $WidestPrimaryKeyNameLength + 9) ?> = $entity->delete();

			if ($delete->isBad()) {
				$this->assignReturnHelperError($ret, $delete, 'Failed to delete <?= $this->e($ClassName) ?>');

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
