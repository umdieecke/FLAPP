<?php
	namespace Knister;

	class Languagesystem {
		private $langFolder;
		private $standardLanguage = "";
		private $availableLanguages = array();

		public function __construct($languageFolder, $standardLanguage = "") {
			if (is_dir($languageFolder)) {
				$this->langFolder = $languageFolder;
			} else {
				return;
			}

			$availableLanguageFiles = Helper::getListOfFilesInDirectory($this->langFolder, "json");

			foreach($availableLanguageFiles as $availableLanguageFile) {
				$langDetails = json_decode(file_get_contents($this->langFolder . DIRECTORY_SEPARATOR . $availableLanguageFile));
				$this->availableLanguages[$langDetails->languageKey] = array(
					"languageNameInternational" => $langDetails->languageNameInternational,
					"languageNameLocal" => $langDetails->languageNameLocal,
					"keys" => $langDetails->keys
				);
			}

			if ($this->isAvailableLanguage($standardLanguage)) $this->standardLanguage = $standardLanguage;
		}

		public function isAvailableLanguage($langKey) {
			return array_key_exists($langKey, $this->availableLanguages);
		}

		public function getLangString($key, $lang = "") {
			if ($lang == "" && $this->standardLanguage == "") return $key; //we can't find a translation for this
			if ($lang == "" && $this->standardLanguage != "") $lang = $this->standardLanguage; //use standard language if no language is forced
			if (!$this->isAvailableLanguage($lang)) return $key; //return key if given language is not available

			$translatedString = false;
			foreach($this->availableLanguages[$lang]["keys"] as $singleKeyInfo) {
				if ($singleKeyInfo->key == $key) {
					$translatedString = $singleKeyInfo->translation;
					break;
				}
			}
			return $translatedString ? $translatedString : $key;
		}

	}
