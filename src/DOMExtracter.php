<?php
	namespace Knister;

	class DOMExtracter {
		private $inputHTML;
		private $baseURL;
		private $xpath;
		private $doc;

		public function __construct($html, $baseURL = "") {
			$this->inputHTML = $html;
			$this->baseURL = $baseURL;
			$this->doc = new \DOMDocument();
			@$this->doc->loadHTML($html);
			$this->xpath = new \DomXpath($this->doc);
		}

		public function customXPathQuery($expression, $textOnly = true, $limitElems = false, $adaptSingle = true) {
			$foundElements = [];
			$i = 0;

			if ($resultLength = count($result = $this->xpath->query($expression))) {
				foreach ($result as $elem) {
					$value = $textOnly ? $elem->textContent : $elem;
					array_push($foundElements, $value);

					if (is_numeric($limitElems) && $i++ >= $limitElems) break;
				}
			}

			return self::adaptDOMElement(($adaptSingle && $resultLength === 1) ? $foundElements[0] : $foundElements);
		}

		public function getStylesheets() {
			return array_map(function($elem) {
				return $this->prepareURL($elem->processed_attributes['href']);
			}, $this->customXPathQuery("/html/head/link[@rel='stylesheet']", false));
		}

		public function getImageSources() {
			$foundElements = [];
			$tags = $this->doc->getElementsByTagName('img');
			foreach ($tags as $tag) {
				array_push($foundElements, $tag->getAttribute('src'));
			}
			return $foundElements;
		}

		public function getMetaTitle() {
			return $this->customXPathQuery("/html/head/title", true, 1);
		}

		public function getHeadlines() {
			return $this->customXPathQuery("//h1 | //h2 | //h3 | //h4 | //h5 | //h6");
		}

		private function prepareURL($url) {
			$isExternal = strpos($url, "http") === 0;
			return $isExternal ? $url : $this->baseURL . $url;
		}

		public static function getNodeHTML($node) {
			$innerHTML = '';
			$children = $node->childNodes;
			foreach ($children as $child) {
				$innerHTML .= $child->ownerDocument->saveXML($child);
			}
			return $innerHTML;
		}

		public function removeTags($tagNames) {
			if (is_string($tagNames)) $tagNames = [$tagNames];
			foreach($tagNames as $tagName) {
				$nodes = $this->xpath->query('//' . $tagName);
				if($nodes->item(0)) {
					$nodes->item(0)->parentNode->removeChild($nodes->item(0));
				}
			}
		}

		public static function getAllAttributesOfNode($domElement) {
			$atts = [];
			if ($domElement->hasAttributes()) {
				foreach ($domElement->attributes as $attr) {
					$atts[$attr->nodeName] = $attr->nodeValue;
				}
			}
			return $atts;
		}

		private static function adaptDOMElement($elementSet) {
			if ($elementSet instanceof \DOMElement) {
				$elementSet->innerHTML = self::getNodeHTML($elementSet);
				$elementSet->outerHTML = Helper::buildHTMLTag($elementSet->tagName, [], self::getNodeHTML($elementSet));
				$elementSet->processed_attributes = self::getAllAttributesOfNode($elementSet);
			}
			elseif (is_array($elementSet)) {
				foreach($elementSet as &$domElement) {
					$domElement = self::adaptDOMElement($domElement);
				}
			}
			return $elementSet;
		}
	}