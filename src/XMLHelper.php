<?php

	namespace Knister;

	Class SimpleXMLElementExtended extends \SimpleXMLElement {
		public function addChildWithCDATA($name, $value = NULL) {
			$new_child = $this->addChild($name);

			if ($new_child !== NULL) {
				$node = dom_import_simplexml($new_child);
				$no = $node->ownerDocument;
				$node->appendChild($no->createCDATASection($value));
			}

			return $new_child;
		}
	}

	class XMLHelper {

		public static function XML2Array($xmlObject) {
			if (is_string($xmlObject)) $xmlObject = simplexml_load_string($xmlObject, 'Knister\SimpleXMLElementExtended',LIBXML_NOCDATA);
			return json_decode(json_encode((array)$xmlObject), 1);
		}

		public static function isXML($xml) {
			$doc = @simplexml_load_string($xml, "Knister\SimpleXMLElementExtended");
			return !is_bool($doc); //$doc is false if $xml is invalid, otherwise SimpleXMLElement
		}

		public static function isValidXMLAgainstSchema($xml, $schemaPath) {
			$doc = new \DOMDocument();
			@$doc->loadXML($xml, LIBXML_NOBLANKS);
			if (@!$doc->schemaValidate($schemaPath)) return false;

			return true;
		}

		public static function getXMLValidationErrors($xml, $schemaPath) {
			libxml_use_internal_errors(true);
			$doc = new \DOMDocument();
			$doc->loadXML($xml, LIBXML_NOBLANKS);
			$result = [];
			if (!$doc->schemaValidate($schemaPath)) {
				$errors = libxml_get_errors();
				foreach ($errors as $error) {
					array_push($result, ["msg" => "XML error occured", "xml_error" => [
						"message" => $error->message,
						"level" => $error->level,
						"code" => $error->code,
						"line" => $error->line
					]]);
				}
				libxml_clear_errors();
			}
			libxml_use_internal_errors(false);

			return $result;
		}
	}