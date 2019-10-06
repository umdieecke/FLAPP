<?php
	namespace Knister;

	class Log {

		private $logfilePath;
		private $header = "\n";
		private $timePart = "{time} ";
		private $footer = "";

		public function __construct($logfilePath = "") {
			if ($logfilePath == "") {
				global $flapp;
				$logfilePath = $flapp->getPath("base") . "/fl.log";
			}
			$this->logfilePath = $logfilePath;
		}

		public function log($message, $loggingMode = "var_export") {
			$content = $message;
			if (!Helper::variableIsOfType($message, "string")) {
				switch ($loggingMode) {
					case "json":
						$content = json_encode($message);
						break;
					case "serialize":
						$content = serialize($message);
						break;
					case "var_export":
					default:
						$content = var_export($message, true);
						break;
				}
			}

			$output = $this->header . str_replace("{time}", date("Y-m-d H:i:s", time()), $this->timePart) . $content . $this->footer;
			return file_put_contents($this->logfilePath, $output, FILE_APPEND);
		}

		public function __get($propertyName) {
			if ($propertyName == "logfilePath") return $this->logfilePath;
		}

	}