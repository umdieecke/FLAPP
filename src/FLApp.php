<?php

	namespace Knister;

	/**
	 * Class FLApp
	 * base class for framework
	 */
	class FLApp {
		/** values that can be passed through arguments */
		private $useFrontController;
		private $routes;
		private $workingPath;
		private $autoAddPagesAsRoute;
		private $showErrors;

		/** internal paths & URLs */
		private $BASE_PATH;
		private $PUBLIC_PATH;
		private $PAGES_PATH;
		private $subDirectory;

		private $BASE_URL;
		private $PUBLIC_URL;
		private $PAGES_URL;
		private $ASSETS_URL;
		private $CURRENT_URL;
		private $FRONTEND_LIBS_URL;

		/** misc stuff */
		private $time;
		private $params = [];

		/**
		 * FLApp constructor.
		 * @param $config
		 *            for possible values inside $config, inspect $baseConfig inside constructor
		 */
		public function __construct($config = []) {
			//baseConfig defines possible values to be passed
			$baseConfig = [
				"useFrontController" => [
					"defaultValue" => false,
					"type" => "boolean"
				],
				"routes" => [
					"defaultValue" => [],
					"type" => "array"
				],
				"workingPath" => [
					"defaultValue" => dirname(dirname(__FILE__)) . "/work",
					"type" => "string"
				],
				"time" => [
					"defaultValue" => time(),
					"type" => "int"
				],
				"autoAddPagesAsRoute" => [
					"defaultValue" => true,
					"type" => "boolean"
				],
				"showErrors" => [
					"defaultValue" => false,
					"type" => "boolean"
				]
			];

			//check and save config
			//only allow configs defined in $baseConfig
			foreach ($baseConfig as $singleConfigName => $singleConfigInfo) {
				if (array_key_exists($singleConfigName, $config) && Helper::variableIsOfType($config[$singleConfigName], $singleConfigInfo["type"])) {
					$this->$singleConfigName = $config[$singleConfigName];
				} else $this->$singleConfigName = $singleConfigInfo["defaultValue"];
			}

			//configure error reporting
			ini_set("log_errors", 1);
			ini_set("error_log", $this->workingPath . DIRECTORY_SEPARATOR . 'error.log');
			if ($this->showErrors) {
				ini_set('display_errors', 1);
				ini_set('display_startup_errors', 1);
				error_reporting(E_ALL);
			}

			return $this;
		}

		/**
		 * initialize FLApp
		 * settings base information like paths and URLs, starting up front controller
		 */
		public function init() {
			/*****************************
			 * determine URLs and paths
			 *****************************/
			$isSSL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? true : false;
			$protocol = $isSSL ? "https://" : "http://";
			$this->subDirectory = $this->determineSubdirectory();

			/*****************************
			 * define base variables
			 *****************************/
			$this->BASE_PATH = dirname(dirname(__FILE__));
			$this->PUBLIC_PATH = $this->BASE_PATH . DIRECTORY_SEPARATOR . "public";
			$this->PAGES_PATH = $this->BASE_PATH . DIRECTORY_SEPARATOR . "pages";
			$this->BASE_URL = $protocol . $_SERVER["HTTP_HOST"] . $this->subDirectory;
			$this->PUBLIC_URL = $this->BASE_URL . "/public";
			$this->PAGES_URL = $this->PUBLIC_URL . "/pages";
			$this->ASSETS_URL = $this->PUBLIC_URL . "/assets";
			$this->CURRENT_URL = $protocol . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

			/*****************************
			 * create needed directories
			 *****************************/
			Helper::createDirectory($this->workingPath);

			/*****************************
			 * start front controller
			 *****************************/
			if ($this->useFrontController) {
				$frontcontroller = new FrontController($this->subDirectory, $this->loadRoutes());
				$match = $frontcontroller->match();
				if ($match && is_callable($match['target'])) {
					call_user_func_array($match['target'], $match['params']);
				} else {
					header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
					if (file_exists($this->PUBLIC_PATH . "/404.php")) include($this->PUBLIC_PATH . "/404.php");
				}
			}
		}

		/**
		 * loading routes from a) given config b) filepath
		 * we create a route for each file stored inside PUBLIC_PATH
		 * this function is called from init()
		 * @return mixed
		 */
		private function loadRoutes() {
			$routes = $this->routes;

			//standard route for directory index
			$routes["/"] = [
				"methods" => "GET|POST|PATCH|PUT|DELETE",
				"target" => function () {
					if (file_exists($this->PUBLIC_PATH . "/index.php")) include($this->PUBLIC_PATH . "/index.php");
				}
			];

			//programmatically add routes for existing html and php files
			if ($this->autoAddPagesAsRoute) {
				$tree = Helper::getFileTree($this->PUBLIC_PATH, "html,php");
				$routes = $this->addDirectoryAsRoutes($tree, $routes, $this->PUBLIC_PATH);
			}

			return $routes;
		}

		/**
		 * helper function for loadRoutes()
		 * adds all possible routes inside a directory to $currentRoutes
		 * @param $tree
		 * @param $currentRoutes
		 * @param $basePath
		 * @return mixed
		 */
		private static function addDirectoryAsRoutes($tree, $currentRoutes, $basePath) {
			$routes = $currentRoutes;
			foreach ($tree as $treeItem) {
				if ($treeItem["type"] == "file") {
					$relativeRoute = Helper::getRelativePath($treeItem["directory"], $basePath) . "/" . $treeItem["rawName"];
					$routes[$relativeRoute] = [
						"methods" => "GET|POST|PATCH|PUT|DELETE",
						"target" => function () use ($treeItem) {
							include($treeItem["fullPath"]);
						}
					];
				} elseif ($treeItem["type"] == "directory") {
					$routes = self::addDirectoryAsRoutes($treeItem["sublevel"], $routes, $basePath);
				}
			}

			return $routes;
		}

		/* ==============================
		 * GETTERS
		 * ============================== */

		/**
		 * get specific url
		 * @param string $type
		 * @return string
		 */
		public function getURL($type = "base") {
			switch ($type) {
				case "public":
					return $this->PUBLIC_URL;
					break;
				case "pages":
					return $this->PAGES_URL;
					break;
				case "assets":
					return $this->ASSETS_URL;
					break;
				case "frontend":
					return $this->FRONTEND_LIBS_URL;
					break;
				case "current":
					return $this->CURRENT_URL;
					break;
				case "base":
				default:
					return $this->BASE_URL;
					break;
			}
		}

		/**
		 * get specific path
		 * @param string $type
		 * @return string
		 */
		public function getPath($type = "base") {
			switch ($type) {
				case "public":
					return $this->PUBLIC_PATH;
					break;
				case "pages":
					return $this->PAGES_PATH;
					break;
				case "working":
					return $this->workingPath;
					break;
				case "base":
				default:
					return $this->BASE_PATH;
					break;
			}
		}

		/**
		 * @return int
		 */
		public function getTime() {
			return $this->time;
		}

		private function determineSubdirectory() {
			$rootPath = str_replace("src/FLApp.php", "", __FILE__);
			$requested = $_SERVER['REQUEST_URI'];

			$result = implode('/',
				call_user_func_array('array_intersect',
					array_map(function($a) {
						return explode('/', $a);
					},[$rootPath, $requested])
				)
			);

			if ($result[strlen($result) - 1] === "/") $result = substr($result, 0, -1);

			return $result;
		}
	}