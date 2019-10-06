<?php
	namespace Knister;

	class PrepareValues {
		public static function multiple($value, $allowedValues, $defaultValue = '') {
			//try a regular match
			if (in_array($value, $allowedValues)) return $value;

			//try to lowercase everything
			if (in_array(strtolower($value), array_map('strtolower', $allowedValues))) return strtolower($value);

			return $defaultValue;
		}

		public static function url($value, $defaultValue = '') {
			if (filter_var($value, FILTER_VALIDATE_URL) !== false) return $value;
			return $defaultValue;
		}

		public static function email($value, $defaultValue = '') {
			if (filter_var($value, FILTER_VALIDATE_EMAIL) !== false) return $value;
			return $defaultValue;
		}

		public static function date($value, $format = "Y-m-d") {
			$timestamp = strtotime($value);
			$newDate = date($format, $timestamp);
			return $newDate;
		}
	}