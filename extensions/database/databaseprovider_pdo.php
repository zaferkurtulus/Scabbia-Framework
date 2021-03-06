<?php

	/**
	 * Database Provider PDO Extension
	 *
	 * @package Scabbia
	 * @subpackage databaseprovider_pdo
	 * @version 1.0.2
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends database
	 * @scabbia-phpversion 5.2.0
	 * @scabbia-phpdepends pdo
	 */
	class databaseprovider_pdo {
		/**
		 * @ignore
		 */
		public $standard = null;
		/**
		 * @ignore
		 */
		public $pdoString;
		/**
		 * @ignore
		 */
		public $username;
		/**
		 * @ignore
		 */
		public $password;
		/**
		 * @ignore
		 */
		public $overrideCase;
		/**
		 * @ignore
		 */
		public $persistent;
		/**
		 * @ignore
		 */
		public $fetchMode;

		/**
		 * @ignore
		 */
		public function __construct($uConfig) {
			$this->pdoString = $uConfig['pdoString'];
			$this->username = $uConfig['username'];
			$this->password = $uConfig['password'];

			if(isset($uConfig['overrideCase'])) {
				$this->overrideCase = $uConfig['overrideCase'];
			}

			$this->persistent = isset($uConfig['persistent']);
			$this->fetchMode = PDO::FETCH_ASSOC;

			$tConnectionString = explode(':', $this->pdoString, 2);
			$this->standard = $tConnectionString[0];
		}

		/**
		 * @ignore
		 */
		public function open() {
			$tParms = array();
			if($this->persistent) {
				$tParms[PDO::ATTR_PERSISTENT] = true;
			}

			switch($this->overrideCase) {
			case 'lower':
				$tParms[PDO::ATTR_CASE] = PDO::CASE_LOWER;
				break;
			case 'upper':
				$tParms[PDO::ATTR_CASE] = PDO::CASE_UPPER;
				break;
			default:
				$tParms[PDO::ATTR_CASE] = PDO::CASE_NATURAL;
				break;
			}

			$tParms[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

			$this->connection = new PDO($this->pdoString, $this->username, $this->password, $tParms);

			// $this->standard = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);
		}

		/**
		 * @ignore
		 */
		public function close() {
		}

		/**
		 * @ignore
		 */
		public function beginTransaction() {
			$this->connection->beginTransaction();
		}

		/**
		 * @ignore
		 */
		public function commit() {
			$this->connection->commit();
		}

		/**
		 * @ignore
		 */
		public function rollBack() {
			$this->connection->rollBack();
		}

		/**
		 * @ignore
		 */
		public function execute($uQuery) {
			return $this->connection->exec($uQuery);
		}

		/**
		 * @ignore
		 */
		public function &queryDirect($uQuery, $uParameters = array()) {
			$tQuery = $this->connection->prepare($uQuery);
			$tResult = $tQuery->execute($uParameters);

			return $tQuery;
		}

		/**
		 * @ignore
		 */
		public function itSeek(&$uObject, $uRow) {
			// return $uObject->fetch($this->fetchMode, PDO::FETCH_ORI_ABS, $uRow);
			for($i = 0; $i < $uRow; $i++) {
				$uObject->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);
			}

			return $this->itNext($uObject);
		}

		/**
		 * @ignore
		 */
		public function itNext(&$uObject) {
			return $uObject->fetch($this->fetchMode, PDO::FETCH_ORI_NEXT);
		}

		/**
		 * @ignore
		 */
		public function itCount(&$uObject) {
			return $uObject->rowCount();
		}

		/**
		 * @ignore
		 */
		public function itClose(&$uObject) {
			return $uObject->closeCursor();
		}

		/**
		 * @ignore
		 */
		public function lastInsertId($uName = null) {
			return $this->connection->lastInsertId($uName);
		}

		/**
		 * @ignore
		 */
		public function serverInfo() {
			return $this->connection->getAttribute(PDO::ATTR_SERVER_INFO);
		}

		/**
		 * @ignore
		 */
		public function sqlInsert($uTable, $uObject, $uReturning = '') {
			$tSql =
				'INSERT INTO ' . $uTable . ' ('
					. implode(', ', array_keys($uObject))
					. ') VALUES ('
					. implode(', ', array_values($uObject))
					. ')';

			if(strlen($uReturning) > 0) {
				$tSql .= ' RETURNING ' . $uReturning;
			}

			return $tSql;
		}

		/**
		 * @ignore
		 */
		public function sqlUpdate($uTable, $uObject, $uWhere, $uExtra = null) {
			$tPairs = array();
			foreach($uObject as $tKey => &$tValue) {
				$tPairs[] = $tKey . '=' . $tValue;
			}

			$tSql = 'UPDATE ' . $uTable . ' SET '
				. implode(', ', $tPairs);

			if(strlen($uWhere) > 0) {
				$tSql .= ' WHERE ' . $uWhere;
			}

			if(!is_null($uExtra)) {
				if($this->standard == 'mysql') {
					if(isset($uExtra['limit']) && $uExtra['limit'] >= 0) {
						$tSql .= ' LIMIT ' . $uExtra['limit'];
					}
				}
			}

			return $tSql;
		}

		/**
		 * @ignore
		 */
		public function sqlDelete($uTable, $uWhere, $uExtra = null) {
			$tSql = 'DELETE FROM ' . $uTable;

			if(strlen($uWhere) > 0) {
				$tSql .= ' WHERE ' . $uWhere;
			}

			if(!is_null($uExtra)) {
				if($this->standard == 'mysql') {
					if(isset($uExtra['limit']) && $uExtra['limit'] >= 0) {
						$tSql .= ' LIMIT ' . $uExtra['limit'];
					}
				}
			}

			return $tSql;
		}

		/**
		 * @ignore
		 */
		public function sqlSelect($uTable, $uFields, $uWhere, $uOrderBy, $uGroupBy, $uExtra = null) {
			$tSql = 'SELECT ';

			if(count($uFields) > 0) {
				$tSql .= implode(', ', $uFields);
			}
			else {
				$tSql .= '*';
			}

			$tSql .= ' FROM ' . $uTable;

			if(strlen($uWhere) > 0) {
				$tSql .= ' WHERE ' . $uWhere;
			}

			if(!is_null($uGroupBy) && strlen($uGroupBy) > 0) {
				$tSql .= ' GROUP BY ' . $uGroupBy;
			}

			if(!is_null($uOrderBy) && strlen($uOrderBy) > 0) {
				$tSql .= ' ORDER BY ' . $uOrderBy;
			}

			if(!is_null($uExtra)) {
				if($this->standard == 'pgsql') {
					if(isset($uExtra['limit']) && $uExtra['limit'] >= 0) {
						$tSql .= ' LIMIT ' . $uExtra['limit'];
					}

					if(isset($uExtra['offset']) && $uExtra['offset'] >= 0) {
						$tSql .= ' OFFSET ' . $uExtra['offset'];
					}
				}
				else {
					if($this->standard == 'mysql') {
						if(isset($uExtra['limit']) && $uExtra['limit'] >= 0) {
							if(isset($uExtra['offset']) && $uExtra['offset'] >= 0) {
								$tSql .= ' LIMIT ' . $uExtra['offset'] . ', ' . $uExtra['limit'];
							}
							else {
								$tSql .= ' LIMIT ' . $uExtra['limit'];
							}
						}
					}
				}
			}

			return $tSql;
		}
	}

?>