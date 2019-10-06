<?php
	namespace Knister;

	class Page {
		private $header;
		private $body;
		private $footer;

		private $minify;

		private $title;
		private $stylesheets = array();
		private $scripts = array();

		private $bodyElems = array();

		public function __construct($config) {
			//baseConfig defines possible values to be passed
			$baseConfig = [
				"title" => [
					"defaultValue" => "",
					"type" => "string"
				],
				"minify" => [
					"defaultValue" => false,
					"type" => "boolean"
				],
				"addResponsiveMeta" => [
					"defaultValue" => true,
					"type" => "boolean"
				]
			];

			//check and save config
			//only allow configs defined in $baseConfig
			foreach ($baseConfig as $singleConfigName => $singleConfigInfo) {
				if (array_key_exists($singleConfigName, $config) && core\Helper::variableIsOfType($config[$singleConfigName], $singleConfigInfo["type"])) {
					$this->$singleConfigName = $config[$singleConfigName];
				} else $this->$singleConfigName = $singleConfigInfo["defaultValue"];
			}

			//scripts and stylesheets are not added through baseconfig, doing it manually to check in detail
			if (isset($config["stylesheets"]) && core\Helper::variableIsOfType($config["stylesheets"], "array")) {
				foreach($config["stylesheets"] as $singleStylesheet) {
					if (!isset($singleStylesheet["id"]) || !isset($singleStylesheet["url"])) continue;
					$media = isset($singleStylesheet["media"]) ? $singleStylesheet["media"] : NULL;
					$this->addStylesheet($singleStylesheet["id"], $singleStylesheet["url"], $media);
				}
			}
			if (isset($config["scripts"]) && core\Helper::variableIsOfType($config["scripts"], "array")) {
				foreach($config["scripts"] as $singleScript) {
					if (!isset($singleScript["id"]) || !isset($singleScript["url"])) continue;
					$inFooter = isset($singleScript["inFooter"]) ? $singleScript["inFooter"] : NULL;
					$this->addScript($singleScript["id"], $singleScript["url"], $inFooter);
				}
			}
		}

		private function buildHTML() {
			//add html start
			$header = file_get_contents(__DIR__ . "/start.html");

			//add title
			$header .= Helper::buildHTMLTag("title", array(), $this->title) . "\n";

			//add responsive meta
			$header .= Helper::buildHTMLTag("meta", array("name" => "viewport", "content" => "width=device-width, initial-scale=1"), false, true) . "\n";

			//add stylesheets
			foreach ($this->stylesheets as $singleStylesheet) {
				$header .= Helper::buildHTMLTag("link", array(
						"id" => $singleStylesheet["id"],
						"href" => $singleStylesheet["url"],
						"rel" => "stylesheet",
						"media" => $singleStylesheet["media"]
					), "", true) . "\n";
			}

			//add header scripts
			foreach ($this->scripts as $singleScript) {
				if (!$singleScript["inFooter"]) {
					$header .= Helper::buildHTMLTag("script", array(
							"id" => $singleScript["id"],
							"src" => $singleScript["url"],
							"type" => "text/javascript"
						)) . "\n";
				}
			}

			//add body start
			$header .= file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "body.html");

			//add body itsself
			$body = "";
			foreach($this->bodyElems as $bodyElem) {
				$body .= "<div id=' " . $bodyElem['id'] . "'>" . $bodyElem['html'] . "</div>\n";
			}

			//add footer scripts
			$footer = "";
			foreach ($this->scripts as $singleScript) {
				if ($singleScript["inFooter"]) {
					$footer .= Helper::buildHTMLTag("script", array(
							"id" => $singleScript["id"],
							"src" => $singleScript["url"],
							"type" => "text/javascript"
						)) . "\n";
				}
			}
			$footer .= file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "end.html");

			//make it public
			$this->header = $header;
			$this->body = $body;
			$this->footer = $footer;
		}

		public function addStylesheet($id, $url, $media = "all") {
			if ($media == NULL) $media = "all";
			array_push($this->stylesheets, array("id" => $id, "url" => $url, "media" => $media));
		}

		public function addScript($id, $url, $inFooter = true) {
			if ($inFooter == NULL) $inFooter = true;
			array_push($this->scripts, array("id" => $id, "url" => $url, "inFooter" => $inFooter));
		}

		public function addContent($id, $html) {
			array_push($this->bodyElems, array("id" => $id, "html" => $html));
		}

		public function output($type = "all") {
			$this->buildHTML();

			switch ($type) {
				case "header":
					$code = $this->header;
					break;
				case "body":
					$code = $this->body;
					break;
				case "footer":
					$code = $this->footer;
					break;
				case "all":
				default:
					$code = $this->header . $this->body . $this->footer;
					break;
			}

			return ($this->minify ? Helper::minify($code, "html") : $code);
		}

	}
