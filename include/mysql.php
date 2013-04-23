<?php
/**
 * MySql database abstraction
 *
 * @copyright	2013 Steffen Vogel
 * @license	http://www.gnu.org/licenses/gpl.txt GNU Public License
 * @author	Steffen Vogel <post@steffenvogel.de>
 * @link	http://www.steffenvogel.de
 */
/*
 * This file is part of sddns
 *
 * sddns is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * sddns is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with sddns. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) . '/db.php';

/**
 * @brief base exception for mysql queries
 */
class MySqlException extends DatabaseException
{
	function __construct($message = null, $code = 0) {
		$message = sprintf('%04d: %s', mysql_errno(), mysql_error());
		parent::__construct($message, mysql_errno());
	}
}

/**
 * @brief resultset of a mysql query
 */
class MySqlResult extends DBResultSet
{
	/**
	 * @param resource $resource mysql resultset
	 */
	function __construct($resource) {
		while ($row = mysql_fetch_assoc($resource)) {
			$this->_rows[] = $row;
			++$this->_num_rows;
		}
	}
}

/**
 * @brief mysql layer
 */
class MySql extends Database {
	/**
	 * @param string $host IP or domain of the database host
	 * @param string $name database name
	 * @param string $user user
	 * @param string $passwd password
	 */
	function __construct($host, $user, $pw, $db) {
		$this->connect($host, $user, $pw);
		$this->select($db);
	}

	function __destruct() {
		$this->close();
	}

	/**
	 * @brief create database connection
	 * @param string $host IP or domain of the database host
	 * @param string $user user
	 * @param string $passwd password
	 */
	public function connect($host, $user, $pw) {
		$this->close();
		$__er = error_reporting(E_ERROR);
		if (!$this->resource = mysql_connect($host, $user, rawurlencode($pw))) {
			error_reporting($__er);
			throw new MySqlException();
		}

		error_reporting($__er);
	}

	/**
	 * @brief close database connection
	 */
	public function close() {
		if (!$this->resource)
			return;
		mysql_close($this->resource);
		$this->resource = false;
	}

	/**
	 * @brief select database
	 * @param string $name database name
	 */
	public function select($db) {
		if (!mysql_select_db($db, $this->resource))
			throw new MySqlException();
		$this->database = $db;
	}

	/**
	 * @brief execute query
	 * @param string $sql query
	 * @return mixed
	 */
	public function execute($sql) {
		if ($output = Registry::get('output'))
			$output->add('db query', 'debug', 8, $sql);

		if (!($result = mysql_unbuffered_query($sql, $this->resource)))
			throw new MySqlException();

		return $result;
	}

	/**
	 * @brief mysql query
	 * @param string $sql query
	 * @param int $offset
	 * @param int $limit
	 * @return TDatabaseResultSet
	 */
	public function query($sql, $limit = -1, $offset = 0) {
		if ($limit != -1)
			$sql .= sprintf(' LIMIT %d, %d', $offset, $limit);
		return new MySqlResult($this->execute($sql));
	}

	/**
	 * @brief mysql escape
	 * @param string $sql query
	 */
	public function escape($sql) {
		return mysql_real_escape_string($sql, $this->resource);
	}

	public function lastId() {
		return mysql_insert_id($this->resource);
	}

	public function affectedRows() {
		return mysql_affected_rows($this->resource);
	}
}

?>
