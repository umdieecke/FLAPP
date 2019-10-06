<?php
	namespace Knister;

	class AccessControl {
		private $allowedIPRanges; //ip range (e.g. "86.56.232") or full ip (e.g. "86.56.232.14")

		private $status;

		public function __construct($config) {
			if (array_key_exists("allowedIPRanges", $config)) $this->allowedIPRanges = $config["allowedIPRanges"];
			else $this->allowedIPRanges = [];
		}


		public function performCheck() {
			$clientIP = Helper::getIP();
			if (!$this->isAllowedIP($clientIP)) {
				$this->status["msgs"][] = "client IP is not within allowed IP range";

				return false;
			}
			return true;
		}

		private function isAllowedIP($ipToCheck) {
			if (count($this->allowedIPRanges) < 1) return true;

			foreach ($this->allowedIPRanges as $allowedIP) {
				if (strpos($ipToCheck, $allowedIP) > -1) return true;
			}

			return false;
		}

		public function getStatus() {
			return $this->status;
		}

	}
