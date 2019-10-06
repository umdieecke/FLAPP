<?php
	namespace Knister;

	class JobWorker {

		private $jobFunction; //should return false in case it didn't succeed
		private $finisherFunction;

		private $jobID;
		private $status;
		private $numOfTries; //this is increased everytime the jobFunction is started. used to sort out jobs that produce errors (like timeouts) and therefore never reach "done" state
		private $createTime;
		private $functionInfos;
		private $functionResult;
		private $ownerSessionID;
		private $jobWorkingDirectory;
		private $additionalInformation;

		private static $possibleStatuses = ["done", "running", "queued", "failed"];
		private static $maxNumOfTries = 2;

		public function __construct($jobFunction, $functionInfos = "", $finisherFunction = "", $additionalInformation = "", $ownerSessionID = "", $jobID = "", $status = "", $createTime = "", $functionResult = "", $numOfTries = "", $loadedFromFile = false) {
			//created needed directories
			global $flapp;
			$this->jobWorkingDirectory = $flapp->getPath("working") . "/jobworker";
			foreach(self::$possibleStatuses as $possibleStatus) {
				core\Helper::createDirectory($this->jobWorkingDirectory . DIRECTORY_SEPARATOR . $possibleStatus);
			}

			$this->jobFunction = $jobFunction;
			$this->finisherFunction = $finisherFunction;

			$this->jobID = $jobID == "" ? "job_" . Helper::generateFileID() : $jobID;
			$this->status = $status == "" ? "queued" : $status;
			$this->functionInfos = $functionInfos == "" ? "" : $functionInfos;
			$this->createTime = $createTime == "" ? time() : $createTime;
			$this->functionResult = $functionResult == "" ? "" : $functionResult;
			$this->additionalInformation = $additionalInformation == "" ? "" : $additionalInformation;
			if ($ownerSessionID == "") {
				if (session_status() == PHP_SESSION_NONE) session_start();
				$ownerSessionID = session_id();
			}
			$this->ownerSessionID = $ownerSessionID;
			$this->numOfTries = $numOfTries == "" ? 0 : $numOfTries;

			if (!$loadedFromFile) $this->updateInfoFile(); //do not write info file if infos are coming right from the file anyway
		}

		public function startWorking() {
			$this->setStatus("running");

			if ($this->numOfTries >= JobWorker::$maxNumOfTries) {
				$this->setStatus("failed");

				return;
			}

			$this->numOfTries++;
			$this->updateInfoFile();

			//start job
			$functionResult = false;
			if (is_string($this->jobFunction) && function_exists($this->jobFunction)) {
				$functionResult = call_user_func($this->jobFunction, $this->functionInfos, $this->additionalInformation);
			}

			$this->functionResult = $functionResult;

			if (is_string($this->finisherFunction) && function_exists($this->finisherFunction)) {
				call_user_func($this->finisherFunction, $this->functionResult, $this->jobID, $this->additionalInformation, $this->ownerSessionID);
			}

			$this->setStatus("done");
		}

		private function updateInfoFile() {
			$infos = array(
				"jobID" => $this->jobID,
				"status" => $this->status,
				"createTime" => $this->createTime,
				"numOfTries" => $this->numOfTries,
				"jobFunction" => $this->jobFunction,
				"functionInfos" => $this->functionInfos,
				"functionResult" => $this->functionResult,
				"ownerSessionID" => $this->ownerSessionID,
				"finisherFunction" => $this->finisherFunction,
				"additionalInformation" => $this->additionalInformation
			);

			file_put_contents($this->jobWorkingDirectory . DIRECTORY_SEPARATOR . $this->status . DIRECTORY_SEPARATOR . $this->jobID . ".json", json_encode($infos));
		}

		private function setStatus($status) {
			if (!in_array($status, JobWorker::$possibleStatuses)) return false;

			rename($this->jobWorkingDirectory . DIRECTORY_SEPARATOR . $this->status . DIRECTORY_SEPARATOR . $this->jobID . ".json", $this->jobWorkingDirectory . DIRECTORY_SEPARATOR . $status . DIRECTORY_SEPARATOR . $this->jobID . ".json");
			$this->status = $status;
			$this->updateInfoFile();
		}

		public static function takeOff() {
			global $flapp;
			//get all jobs that are running and then those in queue
			$runningJobFiles = core\Helper::getListOfFilesInDirectory($flapp->getPath("working") . "/jobworker" . DIRECTORY_SEPARATOR . "running");
			$queueJobFiles = core\Helper::getListOfFilesInDirectory($flapp->getPath("working") . "/jobworker" . DIRECTORY_SEPARATOR . "queued");
			$jobFiles = array_merge($runningJobFiles, $queueJobFiles);

			$timeLimit = ini_get('max_execution_time');

			foreach ($jobFiles as $jobFile) {
				$jobID = core\Helper::getFilenameWithoutExtension($jobFile);
				$job = JobWorker::loadFromJobID($jobID);

				//for jobs that are running, check the last change time. maybe the job is currently running (started by another process)
				//if difference between last change and current time is > execution time, this job can't be running right now and needs to be restarted (was aborted due to an error or exceeded execution time)
				$startWorking = false;
				if ($job->status == "running") {
					$startWorking = (time() - filemtime($flapp->getPath("working") . "/jobworker" . DIRECTORY_SEPARATOR . "running" . DIRECTORY_SEPARATOR . $jobFile) > $timeLimit);
				}
				//queued jobs can be started in any case
				if ($job->status == "queued" || $startWorking) $job->startWorking();
			}
		}

		public static function loadFromJobID($jobID) {
			global $flapp;

			$folders = JobWorker::$possibleStatuses;
			$infoFile = false;
			foreach ($folders as $folder) {
				if (file_exists($flapp->getPath("working") . "/jobworker" . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $jobID . ".json")) {
					$infoFile = file_get_contents($flapp->getPath("working") . "/jobworker" . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $jobID . ".json");
				}
			}
			if (!$infoFile) return false;

			$infos = json_decode($infoFile);

			return new JobWorker($infos->jobFunction, $infos->functionInfos, $infos->finisherFunction, $infos->additionalInformation, $infos->ownerSessionID, $infos->jobID, $infos->status, $infos->createTime, $infos->functionResult, $infos->numOfTries, true);
		}

		/**
		 * @param array $requirements
		 * @param array $additionalInfoRequirements => multidimensional array of key value pairs
		 * @return array $jobs => Jobs that meet all the requirements
		 */
		public static function loadJobs($requirements = array(), $additionalInfoRequirements = array()) {
			global $flapp;

			//load jobs
			$jobs = [];
			foreach (JobWorker::$possibleStatuses as $folder) {
				$jobFiles = core\Helper::getListOfFilesInDirectory($flapp->getPath("working") . "/jobworker" . DIRECTORY_SEPARATOR . $folder);
				foreach ($jobFiles as $jobFile) {
					$curJob = json_decode(file_get_contents($flapp->getPath("working") . "/jobworker" . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $jobFile));
					$generalRequirementsMet = JobWorker::checkObjectProperties($curJob, $requirements);
					$additionalRequirementsMet = JobWorker::checkObjectProperties($curJob->additionalInformation, $additionalInfoRequirements);
					if ($generalRequirementsMet && $additionalRequirementsMet) $jobs[] = $curJob;
				}
			}

			return $jobs;
		}

		private static function checkObjectProperties($object, $requirements) {
			if (count($requirements) == 0) return true; //no requirements set
			foreach ($requirements as $neededKey => $neededValue) {
				if (!property_exists($object, $neededKey) || $object->$neededKey != $neededValue) return false;
			}

			return true;
		}

		public function __get($property) {
			if ($property == "jobID") return $this->jobID;

			return false;
		}
	}