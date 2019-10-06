<?php
	namespace Knister;

	use AltoRouter;

	class FrontController extends AltoRouter {
		public function __construct($basepath = "", $routes = array()) {
			parent::__construct();

			if ($basepath != null) $this->setBasePath($basepath);
			$this->addRoutes($routes);
		}

		public function addRoute($methods, $route, $function, $name = null) {
			$this->map($methods, $route, $function, $name);
		}

		public function addRoutes($routes) {
			foreach ($routes as $routePath => $routeProps) {
				$this->addRoute($routeProps["methods"], $routePath, $routeProps["target"]);
			}
		}
	}