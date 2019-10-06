<?php

	namespace Knister;

	use mysqli;

	class DB {
		private $db;
		private $prefix;
		private $lastQuery;
		private $customError;

		private function escape($string) {
			return $this->db->real_escape_string($string);
		}

		private function tableExists($tableName) {
			$sql = "SHOW TABLES LIKE '" . $tableName . "'";
			$result = $this->query($sql);
			$result = $result->fetch_assoc();

			return is_array($result);
		}

		public function closeConnection() {
			$this->db->close();
		}

		public function query($sqlStatement) {
			$this->lastQuery = $sqlStatement;

			return $this->db->query($sqlStatement);
		}

		public function getDebugInfo() {
			return [
				"lastQuery" => $this->lastQuery,
				"customError" => $this->customError,
				"error" => $this->db->error
			];
		}

		public function getInsertID() {
			return $this->db->insert_id;
		}

		public static function getResult($query, $adaptSingle = false) {
			$result = [];
			while ($row = $query->fetch_assoc()) {
				array_push($result, $row);
			}

			if ($adaptSingle && count($result) == 1) return $result[0];

			return $result;
		}

		public function __construct($host, $user, $password, $name, $prefix = "") {
			if (isset($host) && isset($user) && isset($password) && isset($name)) {
				$this->db = new mysqli($host, $user, $password, $name);
				$this->db->set_charset("UTF8");

				$this->setPrefix($prefix);
			} else {
				die("insufficient data");
			}
		}

		public function insert($table, $data) {
			$propertyString = $valueString = "";
			foreach ($data as $singleEntryKey => $singleEntryValue) {
				$propertyString .= $singleEntryKey . ",";
				if ($singleEntryValue === "now()") {
					$valueString .= "now(),";
				} elseif ($singleEntryValue == "NULL" || $singleEntryValue == NULL) {
					$valueString .= "NULL,";
				} else {
					$valueString .= "'" . $this->escape($singleEntryValue) . "',";
				}
			}
			$propertyString = substr($propertyString, 0, -1);
			$valueString = substr($valueString, 0, -1);

			$query = "INSERT INTO " . $this->prefix . $table . " (" . $propertyString . ") VALUES (" . $valueString . ")";

			return $this->query($query);
		}

		public function delete($table, $data) {
			$whereString = "";
			foreach ($data as $singleEntryKey => $singleEntryValue) {
				$whereString .= $singleEntryKey . "='" . $singleEntryValue . "' AND ";
			}
			$whereString = substr($whereString, 0, -4);
			$query = "DELETE FROM " . $this->prefix . $table . " WHERE " . $whereString;

			return $this->query($query);
		}

		public function update($table, $where, $data) {
			$setString = $whereString = "";

			foreach ($data as $singleUpdateKey => $singleUpdateValue) {
				if ($singleUpdateValue == NULL || strtolower($singleUpdateValue) === "null") {
					$valueString = "NULL";
				} else {
					$valueString = "'" . $this->escape($singleUpdateValue) . "'";
				}
				$setString .= $singleUpdateKey . " = " . $valueString . " , ";
			}
			$setString = substr($setString, 0, -2);

			foreach ($where as $singleWhereKey => $singleWhereValue) {
				if ($singleWhereValue == NULL || strtolower($singleWhereValue) === "null") {
					$whereString .= $singleWhereKey . " IS NULL AND ";
				} else {
					$whereString .= $singleWhereKey . " = '" . $singleWhereValue . "' AND ";
				}
			}
			$whereString = substr($whereString, 0, -4);

			$query = "UPDATE " . $this->prefix . $table . " SET " . $setString . " WHERE " . $whereString;

			return $this->query($query);
		}

		public function select($table, $where = [], $properties = "*", $orderby = "", $limit = false) {
			if (!$this->tableExists($this->prefix.$table)) {
				$this->customError = "Table '" . $table . "' does not exist";

				return false;
			}

			$query = "SELECT " . $properties . " FROM " . $this->prefix . $table;
			if (count($where) > 0) {
				$whereString = "";
				foreach ($where as $singleWhereKey => $singleWhereValue) {
					if ($singleWhereValue == NULL || strtolower($singleWhereValue) === "null") {
						$whereString .= $singleWhereKey . " IS NULL AND ";
					} elseif (strtoupper($singleWhereValue) == "NOT NULL") {
						$whereString .= $singleWhereKey . " IS NOT NULL AND ";
					} else {
						$whereString .= $singleWhereKey . " = '" . $singleWhereValue . "' AND ";
					}
				}
				$whereString = substr($whereString, 0, -4);
				$query .= " WHERE " . $whereString;
			}

			if (is_string($orderby) && strlen($orderby) > 0) {
				$query .= " ORDER BY " . $orderby;
			}

			if ($limit) {
				$query .= " LIMIT " . $limit;
			}


			$result = [];
			$queryResult = $this->query($query);
			while ($row = $queryResult->fetch_assoc()) {
				array_push($result, $row);
			}

			return $result;
		}

		public function get($table, $where = false, $properties = " * ") {
			$result = $this->select($table, $where, $properties, "", "0,1");

			if (isset($result[0])) {
				return $result[0];
			}

			return false;
		}

		public function setPrefix($prefix) {
			$this->prefix = $prefix;
		}

		public function getPrefix() {
			return $this->prefix;
		}

		public function __destruct() {
			$this->closeConnection();
		}
	}