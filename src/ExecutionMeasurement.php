<?php

	namespace Knister;

	class ExecutionMeasurement {
		private $events = array();
		private $staticInfo = array();

		public function __construct($staticInfo = array()) {
			$this->staticInfo = $staticInfo;
		}

		public function startEvent(...$eventNames) {
			foreach($eventNames as $eventName) {
				$this->events[$eventName] = array(
					'started' => $time = microtime(true),
					'startedReadable' => date("d.m.Y H:i:s", $time),
					'status' => 'running'
				);
			}
		}

		public function finishEvent(...$eventNames) {
			foreach($eventNames as $eventName) {
				if (array_key_exists($eventName, $this->events)) {
					$this->events[$eventName]['finished'] = microtime(true);
					$this->events[$eventName]['status'] = 'finished';
					$this->events[$eventName]['executionTime'] = (float) $this->events[$eventName]['finished'] - $this->events[$eventName]['started'];
				}
			}
		}

		public function getExecutionTime($eventName) {
			if (array_key_exists($eventName, $this->events) && $this->events[$eventName]['status'] === 'finished') {
				return $this->events[$eventName]['executionTime'];
			}

			return false;
		}

		public function getEvent($eventName) {
			if (array_key_exists($eventName, $this->events)) {
				return $this->events[$eventName];
			}

			return false;
		}

		public function storeEvents($destination, $append = false) {
			$add = $currentEvents = array(
				"staticInfo" => $this->staticInfo,
				"events" => $this->events
			);

			if ($append && file_exists($destination)) {
				$currentEvents = json_decode(file_get_contents($destination), JSON_FORCE_OBJECT);
				array_push($currentEvents, $add);
			}

			file_put_contents($destination, json_encode(array($currentEvents)));
		}
	}