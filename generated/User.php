<?php
	namespace ???;  // Use short tag for namespace

	use Stoic\Log\Logger;
	use Stoic\Pdo\BaseDbQueryTypes;
	use Stoic\Pdo\BaseDbTypes;
	use Stoic\Pdo\PdoHelper;
	use Stoic\Pdo\StoicDbModel;
	use Stoic\Utilities\ReturnHelper;
	use Stoic\Pdo\BaseDbColumnFlags as BCF;

	class User extends StoicDbModel {
		public int $ID;
		public string $Email;
		public int $EmailConfirmed;
		public \DateTimeInterface $Joined;
		public \DateTimeInterface $LastLogin;
		public \DateTimeInterface $LastActive;

		protected function __setupModel() : void {
			$this->setColumn('ID', 'ID', BCF::INTEGER, BaseDbTypes::IS_KEY | BaseDbTypes::ALLOWS_NULLS | BaseDbTypes::AUTO_INCREMENT);
			$this->setColumn('Email', 'Email', BCF::STRING, BaseDbTypes::ALLOWS_NULLS);
			$this->setColumn('EmailConfirmed', 'EmailConfirmed', BCF::INTEGER, BaseDbTypes::ALLOWS_NULLS);
			$this->setColumn('Joined', 'Joined', BCF::DATETIME, BaseDbTypes::ALLOWS_NULLS);
			$this->setColumn('LastLogin', 'LastLogin', BCF::DATETIME);
			$this->setColumn('LastActive', 'LastActive', BCF::DATETIME);

			$this->ID = 0;
			$this->Email = "";
			$this->EmailConfirmed = 0;
		}

		public static function fromID(int $ID,  PdoHelper $db, Logger $log = null): User {
			$ret = new User($db, $log);
			$ret->ID = $ID;
			$ret->read();
			return $ret;
		}
	}