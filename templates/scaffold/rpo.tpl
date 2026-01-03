<?= '<?php' ?>


	namespace <?= $Namespace ?>;

	use Stoic\Log\Logger;
	use Stoic\Pdo\BaseDbQueryTypes;
	use Stoic\Pdo\PdoHelper;
	use Stoic\Pdo\StoicDbClass;

	class <?= $this->e($PluralClassName) ?> extends StoicDbClass {
		protected <?= $this->e($ClassName) ?> $obj;


		/**
		 * Instantiates a new <?= $this->e($PluralClassName) ?> repository object.
		 *
		 * @param PdoHelper $db
		 * @param null|Logger $log
		 */
		public function __construct(PdoHelper $db, null|Logger $log = null) {
			parent::__construct($db, $log);

			$this->obj = new <?= $this->e($ClassName) ?>($db, $log);

			return;
		}

		/**
		 * Retrieves all <?= $this->e($PluralClassName) ?> from the database.
		 *
		 * @return <?= $this->e($ClassName) ?>[]
		 */
		public function getAll<?= $this->e($PluralClassName) ?>(): array {
			$ret = [];
			$this->tryPdoExcept(function () use (&$ret) {
				$query = $this->db->query($this->obj->generateClassQuery(BaseDbQueryTypes::SELECT, false));

				while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
					$ret[] = <?= $this->e($ClassName) ?>::fromArray($row, $this->db, $this->log);
				}

				return;
			}, "Failed to get all <?= $this->e($PluralClassName) ?>");

			return $ret;
		}
	}
