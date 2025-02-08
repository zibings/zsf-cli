<?= '<?php' ?>

	namespace <?= '???' ?>;

	class <?= $this->e($ClassName) ?>s extends StoicDbClass {
		/**
		* Retrieves all <?= $this->e($ClassName) ?>s from the database.
		*
		* @return <?= $this->e($ClassName) ?>[]
		*/
		public function getAll<?= $this->e($ClassName) ?>s(): array {
			$ret = [];
			$this->tryPdoExcept(function () use (&$ret) {
				$sql = "SELECT * FROM `<?= $this->e($ClassName) ?>`";
				$query = $this->db->query($sql);
				while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
					$entity = new <?= $this->e($ClassName) ?>($this->db, $this->log);
<?php foreach ($Columns as $column): ?>
					$entity-><?= $this->e($column->name) ?> = $row["<?= $this->e($column->name) ?>"];
<?php endforeach; ?>
					$ret[] = $entity;
				}
			}, "Failed to get all <?= $this->e($ClassName) ?>s");

			return $ret;
		}
	}