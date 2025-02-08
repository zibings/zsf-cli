<?php
	namespace ???;  // Use short tag for namespace

	use Stoic\Log\Logger;
	use Stoic\Pdo\BaseDbQueryTypes;
	use Stoic\Pdo\BaseDbTypes;
	use Stoic\Pdo\PdoHelper;
	use Stoic\Pdo\StoicDbModel;
	use Stoic\Utilities\ReturnHelper;
	use Stoic\Pdo\BaseDbColumnFlags as BCF;

	class LoginKey extends StoicDbModel {
		public int $UserID;
		public int $Provider;
		public string $Key;

		public static function fromUserID_Provider(int $UserID, int $Provider, PdoHelper $db, Logger $log = null): LoginKey {
			$ret = new LoginKey($db, $log);
			$ret->UserID = $UserID;
			$ret->Provider = $Provider;
			$ret->read();
			return $ret;
		}

		protected function __setupModel() : void {
			$this->setColumn('UserID', 'UserID', BCF::INTEGER, BaseDbTypes::IS_KEY | BaseDbTypes::ALLOWS_NULLS);
			$this->setColumn('Provider', 'Provider', BCF::INTEGER, BaseDbTypes::IS_KEY | BaseDbTypes::ALLOWS_NULLS);
			$this->setColumn('Key', 'Key', BCF::STRING, BaseDbTypes::ALLOWS_NULLS);

			$this->UserID = 0;
			$this->Provider = 0;
			$this->Key = "";
		}
	}