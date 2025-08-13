<?= '<?php' ?>


	namespace <?= $Namespace ?>;

	use Stoic\Pdo\StoicDbClass;

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
					$ret[] = <?= $this->e($ClassName) ?>::fromArray($row, $this->db, $this->log);
				}

				return;
			}, "Failed to get all <?= $this->e($ClassName) ?>s");

			return $ret;
		}
	}