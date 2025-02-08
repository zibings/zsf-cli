<?php
	namespace ???;  // Use short tag for namespace

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

	class LoginKeyAPI extends ApiController {
		public function createLoginKey(Request $request, array $matches = null): Response {
			$ret = $this->newResponse();

			if ($this->tryGetParams($ret, $request, ['UserID', 'Provider']) === false) {
				return $ret;
			}

			$entity = new LoginKey($this->db, $this->log);
			$entity->UserID = $params->getInt("UserID");
			$entity->Provider = $params->getInt("Provider");
			$entity->Key = $params->getString("Key");

			$create = $entity->create();
			if ($create->isBad()) {
				if ($create->hasMessages()) {
					$ret->setAsError($create->getMessages()[0]);
				} else {
					$ret->setAsError('Failed to create LoginKey');
				}
			}

			return $ret;
		}

		public function readLoginKey(Request $request, array $matches = null): Response {
			$ret = $this->newResponse();

			if ($this->tryGetParams($ret, $request, ['UserID', 'Provider']) === false) {
				return $ret;
			}

			$entity = new LoginKey($this->db, $this->log);
			$entity->UserID = $params->getInt("UserID");
			$entity->Provider = $params->getInt("Provider");
			$entity->Key = $params->getString("Key");

			$read = $entity->read();
			if ($read->isBad()) {
				if ($read->hasMessages()) {
					$ret->setAsError($read->getMessages()[0]);
				} else {
					$ret->setAsError('Failed to get LoginKey');
				}
			}

			return $ret;
		}
	}