<?php

	namespace Knister;

	class Helper {
		public static function getCounterString($stack, $maxNumber = 9) {
			if (is_numeric($stack)) $actualCount = $stack;
			elseif (is_array($stack) || is_object($stack)) $actualCount = count($stack);
			elseif (is_string(($stack))) $actualCount = strlen($stack);
			else $actualCount = 0;


			return ($actualCount > $maxNumber ? $maxNumber . "+" : $actualCount);
		}

		public static function generateFileID() {
			return time() . "_" . Helper::generateRandomLetter(4);
		}

		public static function generateRandomLetter($length = 1) {
			$str = "";
			for ($i = 0; $i < $length; $i++) {
				$str .= chr(mt_rand(65, 90));
			}

			return $str;
		}

		public static function getParameterValue($paramName, $defaultValue = false) {
			$possibleHolders = [$_GET, $_POST, $_REQUEST];
			foreach ($possibleHolders as $possibleHolder) {
				if (isset($possibleHolder[$paramName])) return strip_tags($possibleHolder[$paramName]);
			}

			return $defaultValue;
		}

		public static function getFileExtension($filename) {
			$ext = explode(".", $filename);

			return $ext[count($ext) - 1];
		}

		public static function getNumOfFilesInDirectory($dir) {
			return iterator_count(new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS));
		}

		public static function getNumOfDirectoriesInDirectory($dir) {
			return count(glob("$dir/*", GLOB_ONLYDIR));
		}

		public static function deleteDirectory($dir) {
			$overallSuccess = true;
			if (is_dir($dir)) {
				$objects = scandir($dir);
				foreach ($objects as $object) {
					if ($object != "." && $object != "..") {
						if (is_dir($dir . "/" . $object))
							$overallSuccess = Helper::deleteDirectory($dir . "/" . $object) ? $overallSuccess : false;
						else
							$overallSuccess = unlink($dir . "/" . $object) ? $overallSuccess : false;
					}
				}
				$overallSuccess = rmdir($dir) ? $overallSuccess : false;
			}

			return $overallSuccess;
		}

		public static function combineMessages($msgArray) {
			$feedbackMsg = "";
			$separator = " ";
			foreach ($msgArray as $msg) {
				$feedbackMsg .= $msg . $separator;
			}
			$feedbackMsg = substr($feedbackMsg, 0, -strlen($separator));

			return $feedbackMsg;
		}

		public static function buildHTMLTag($tagName, $attributes = [], $content = "", $shortClose = false) {
			$html = "<" . $tagName;
			foreach ($attributes as $singleAttributeName => $singleAttributeValue) {
				if ($singleAttributeValue == NULL) continue;
				$html .= " " . $singleAttributeName . "='" . $singleAttributeValue . "'";
			}
			if (($content == "" || !$content) && $shortClose) {
				$html .= "/>";
			} else {
				$html .= ">";
				$html .= $content;
				$html .= "</" . $tagName . ">";
			}

			return $html;
		}

		public static function calculateDistance($point1_lat, $point1_long, $point2_lat, $point2_long, $unit = 'km', $decimals = 2) {
			$degrees = rad2deg(acos((sin(deg2rad($point1_lat)) * sin(deg2rad($point2_lat))) + (cos(deg2rad($point1_lat)) * cos(deg2rad($point2_lat)) * cos(deg2rad($point1_long - $point2_long)))));
			switch ($unit) {
				case 'km':
					$distance = $degrees * 111.13384;
					break;
				case 'mi':
					$distance = $degrees * 69.05482;
					break;
				case 'nmi':
					$distance = $degrees * 59.97662;
					break;
				default:
					$distance = 0;
					break;
			}

			return round($distance, $decimals);
		}

		public static function URL_exists($url) {
			$headers = get_headers($url);
			return stripos($headers[0], "200 OK") ? true : false;
		}

		public static function get_URL_contents($url) {
			global $flapp;
			$cookie_file = $flapp->getPath("working") . DIRECTORY_SEPARATOR . "cookie.txt";
			if (function_exists('curl_init')) {
				$ch = curl_init();
				curl_setopt_array($ch, [
						CURLOPT_URL => $url,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_SSL_VERIFYPEER => false,
						CURLOPT_SSL_VERIFYHOST => 2,
						CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36",
						CURLOPT_FOLLOWLOCATION => true,
						CURLOPT_AUTOREFERER => true,
						CURLOPT_UNRESTRICTED_AUTH => true,
						CURLOPT_VERBOSE => true,
						CURLOPT_COOKIESESSION => true,
						CURLOPT_COOKIEFILE => $cookie_file,
						CURLOPT_COOKIEJAR => $cookie_file
					]
				);
				$result = curl_exec($ch);
				curl_close($ch);
			} elseif (function_exists('file_get_contents')) {
				$result = file_get_contents($url);
			} elseif (function_exists('fopen') && function_exists('stream_get_contents')) {
				$handle = fopen($url, "r");
				$result = stream_get_contents($handle);
			} else {
				$result = false;
			}

			return $result;
		}

		public static function exitWithHTTPCode($code, $msg = false, $asJSON = false, $jsonData = []) {
			http_response_code($code);
			if ($asJSON) {
				self::setContentTypeHeader("json");
				die(json_encode([
					"success" => ($code === 200),
					"http_code" => $code,
					"msg" => $msg,
					"data" => $jsonData
				]));
			} else {
				self::setContentTypeHeader("text");
				is_string($msg) ? die($msg) : die();
			}
		}

		public static function setContentTypeHeader($type) {
			switch ($type) {
				case "xml":
					header("Content-type: text/xml; charset=utf-8'");
					break;
				case "json":
					header('Content-Type: application/json');
					break;
				case "text":
					header("Content-Type: text/plain");
					break;
				default:
					break;
			}
		}

		public static function getFileAge($filePath) {
			global $flapp;
			if (!file_exists($filePath)) return false;
			return ($flapp->getTime() - filemtime($filePath));
		}

		public static function generateShortHash($input, $length = 8) {
			return substr(sha1($input), 0, $length);
		}

		public static function extractHTMLPart($xpathSelector, $html, $includeOuterTag = false, $excludeTags = []) {
			$extractor = new DOMExtracter($html);
			$extractor->removeTags($excludeTags);
			$elem = $extractor->customXPathQuery($xpathSelector, false);
			return $includeOuterTag ? $elem->outerHTML : $elem->innerHTML;
		}

		public static function getListOfFilesInDirectory($path, $fileExtensions = "") {
			$filteredFiles = [];
			$files = array_values(array_map("basename", array_filter(glob($path . "/*"), 'is_file')));
			$allowedExtensions = explode(",", preg_replace('/\s+/', '', $fileExtensions));
			foreach ($files as $singleFileName) {
				$currentExtension = pathinfo($singleFileName, PATHINFO_EXTENSION);
				if (in_array($currentExtension, $allowedExtensions) || (count($allowedExtensions) == 1) && $allowedExtensions[0] == "") {
					$filteredFiles[] = $singleFileName;
				}
			}

			return $filteredFiles;
		}

		public static function getListOfDirectoriesInDirectoy($path, $includePath = false) {
			return $includePath ? glob($path . "/*", GLOB_ONLYDIR) : array_map('basename', glob($path . "/*", GLOB_ONLYDIR));
		}

		public static function getFileTree($path, $fileExtensions = "") {
			$filetree = [];
			$dirs = self::getListOfDirectoriesInDirectoy($path);
			foreach ($dirs as $singleDirectory) {
				array_push($filetree, [
					"type" => "directory",
					"name" => $singleDirectory,
					"fullPath" => $path . "/" . $singleDirectory,
					"sublevel" => self::getFileTree($path . "/" . $singleDirectory, $fileExtensions)
				]);
			}
			$files = self::getListOfFilesInDirectory($path, $fileExtensions);
			foreach ($files as $singleFile) {
				array_push($filetree, [
					"type" => "file",
					"name" => $singleFile,
					"rawName" => self::getFilenameWithoutExtension($path . DIRECTORY_SEPARATOR . $singleFile),
					"fullPath" => $path . "/" . $singleFile,
					"directory" => $path
				]);
			}
			return $filetree;
		}

		public static function getRelativePath($fullPath, $startPath) {
			return str_replace($startPath, "", $fullPath);
		}

		public static function createDirectory($path, $mode = 0777, $recursive = true) {
			if (is_dir($path)) return true;

			return (self::createDirectory(dirname($path)) && mkdir($path, $mode, $recursive));
		}

		public static function createDirectories($dirs) {
			$helper = new Helper;
			foreach ($dirs as $singleDir) {
				if (self::variableIsOfType($singleDir, "string")) {
					self::createDirectory($singleDir);
				} elseif (self::variableIsOfType($singleDir, "array")) {
					call_user_func_array([$helper, "createDirectory"], $singleDir);
				}
			}
		}

		public static function getFilenameWithoutExtension($filePath) {
			return pathinfo($filePath, PATHINFO_FILENAME);
		}

		public static function variableIsOfType($var, $type) {
			switch ($type) {
				case "bool":
				case "boolean":
					return is_bool($var);
				case "number":
				case "num":
					return is_numeric($var);
				case "integer":
				case "int":
					return is_int($var);
				case "array":
				case "arr":
					return is_array($var);
				case "object":
				case "obj":
					return is_object($var);
				case "string":
				case "str":
					return is_string($var);
				default:
					return false;
			}
		}

		/**
		 * @param string $trustableRemoteAddr
		 *        since 'forwarded for' can be set manually and therefore not be trusted, give me a trustable remote address and i will return the IP in HTTP_X_FORWARDED_FOR only if the remote ip matches
		 *        can be an IP or an IP range (e.g. '86.86.86')
		 * @return string // IP
		 */
		public static function getIP($trustableRemoteAddr = "") {
			$headers = $_SERVER;
			if (array_key_exists('HTTP_X_FORWARDED_FOR', $headers) && strpos($_SERVER["REMOTE_ADDR"], $trustableRemoteAddr)) {
				$the_ip = $headers['HTTP_X_FORWARDED_FOR'];
				$the_ip_array = explode(",", $the_ip);
				$the_ip = $the_ip_array[0];
			} else {
				$the_ip = $_SERVER['REMOTE_ADDR'];
			}

			return $the_ip;
		}

		public static function filterArray($array, $prop, $value) {
			return array_filter($array, function ($entry) use ($prop, $value) {
				if (!array_key_exists($prop, $entry)) return false;
				return $entry[$prop] === $value;
			});
		}

		public static function filterMultiArray($array, $prop, $value) {
			$prop = explode('->', $prop);

			return array_filter($array, function ($entry) use ($prop, $value) {
				foreach ($prop as $propPart) {
					if (!is_array($entry) || !array_key_exists($propPart, $entry)) break;
					$entry = $entry[$propPart];
				}
				return $entry === $value;
			});
		}
	}