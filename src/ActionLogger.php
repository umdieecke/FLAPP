<?php

	namespace Knister;

	class ActionLogger {
		private $logEntries = [];
		private $logfilePath;
		private $mailAlert = false;
		private $fullConfig;
		private $simpleLog;

		const SEND_MAIL_EVERY_X_SECONDS = 60 * 60 * 1;
		const SEND_MAIL_FROM_LEVEL = 3;
		const PROPERTY_SEQUENCE = [
			"date", "actionID", "msg", "level", "IP", "data"
		];
		const MAX_LOG_FILE_AGE = 60 * 60 * 24 * 7;

		public function __construct($storeMethod = "file", $config = []) {
			global $flapp;
			$this->fullConfig = $config;
			if (array_key_exists("mailAlert", $config)) $this->mailAlert = $config["mailAlert"];
			if ($storeMethod === "file") {
				if (array_key_exists("logfilePath", $config)) $this->logfilePath = $config["logfilePath"];
				else $this->logfilePath = $flapp->getPath("working") . "/fl-actionlog.json";
				$this->setLogFile();
			}
			$this->simpleLog = new Log($flapp->getPath("working") . "/fl-simple.log");
		}

		private function setLogFile() {
			if (Helper::getFileAge($this->logfilePath) > self::MAX_LOG_FILE_AGE) {
				$pathInfo = pathinfo($this->logfilePath);
				rename($this->logfilePath, $pathInfo['dirname'] . '/fl-actionlog_' . date('Y-m-d H:i:s') . '.json');
			}

			if (!file_exists($this->logfilePath)) {
				file_put_contents($this->logfilePath, json_encode([
					"logEntries" => []
				]));
			}
		}

		public function log($actionID, $data = [], $msg = "", $level = 0, $date = false) {
			global $flapp;
			if ($date === false) {
				$date = $flapp ? $flapp->getTime() : time();
			}
			array_push($this->logEntries, [$date, $actionID, $msg, $level, $ip = core\Helper::getIP(), $data]);
			$this->storePermanently();
			if (is_string(filter_var($this->mailAlert, FILTER_VALIDATE_EMAIL))) {
				if ($level >= self::SEND_MAIL_FROM_LEVEL) {
					if ($date - self::getLastMailSent() > self::SEND_MAIL_EVERY_X_SECONDS) {
						$this->sendMail([$date, $actionID, $msg, $level, core\Helper::getIP(), $data]);
					}
				}
			}
			$this->simpleLog->log($stringified = "Action: {$actionID} :: Data: " . var_export($data, true) . " Message: {$msg} :: IP: {$ip}");
			return $stringified;
		}

		public function readLog($filterConfig = []) {
			$currentStorage = json_decode(file_get_contents($this->logfilePath), JSON_FORCE_OBJECT);
			$result = $this->logEntries = $currentStorage['logEntries'];

			foreach ($filterConfig["filters"] as $filterSet) {
				if (!array_key_exists('prop', $filterSet) || !array_key_exists('value', $filterSet)) continue;

				if (strpos($filterSet['prop'], 'data') === 0) {
					$desiredPosition = array_search("data", self::PROPERTY_SEQUENCE);
					$desiredPosition .= substr($filterSet['prop'], 4);
				} else {
					$desiredPosition = array_search($filterSet['prop'], self::PROPERTY_SEQUENCE);
				}
				$result = Helper::filterMultiArray($result, $desiredPosition, $filterSet['value']);
			}
			return array_values($result);
		}

		private function sendMail($mailData) {
			global $flapp;
			$mailer = new \FL\Mailer($this->fullConfig["smtp_server"], $this->fullConfig["smtp_user"], $this->fullConfig["smtp_password"]);
			$mailer->sendMail([
				"fromName" => "ActionLogger",
				"subject" => "Fehler auf " . $flapp->getURL(),
				"htmlBody" => "<pre>" . print_r($mailData, true) . "</pre>",
				"toAddresses" => [$this->mailAlert]
			]);
			self::setLastMailSent();
		}

		private static function setLastMailSent() {
			global $flapp;
			$now = $flapp ? $flapp->getTime() : time();
			$tmpFile = $flapp->getPath("working") . "/ActionLogger_lastMailSentTime.txt";
			file_put_contents($tmpFile, $now);
		}

		private static function getLastMailSent() {
			global $flapp;
			if (!file_exists($flapp->getPath("working") . "/ActionLogger_lastMailSentTime.txt")) return 0;
			$lastTime = file_get_contents($flapp->getPath("working") . "/ActionLogger_lastMailSentTime.txt");

			return (is_numeric($lastTime)) ? $lastTime : 0;
		}

		private function storePermanently() {
			$currentStorage = json_decode(file_get_contents($this->logfilePath), JSON_FORCE_OBJECT);
			foreach ($this->logEntries as $logEntry)
				if ($logEntry && !is_null($logEntry)) array_push($currentStorage["logEntries"], $logEntry);
			if (file_put_contents($this->logfilePath, json_encode($currentStorage)))
				$this->logEntries = [];
		}
	}