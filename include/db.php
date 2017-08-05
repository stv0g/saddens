<?php
/**
 * Database abstraction
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

/**
 * @brief base exception for database queries
 */
class DatabaseException extends CustomException {}

/**
 * @brief abstract resultset definition
 */
abstract class DBResultSet implements Iterator {
	/**
	 * @brief rowcount of the result
	 * @var int
	 */
	protected $_num_rows = 0;

	/**
	 * @brief result
	 * @var array
	 */
	protected $_rows = array();

	/**
	 * @param resource $resource database resource
	 */
	abstract function __construct($resource);

	/**
	 * @brief current element (iterator)
	 * @return array
	 */
	public function current() {
		return current($this->_rows);
	}

	/**
	 * @brief next element (iterator)
	 * @return array
	 */
	public function next() {
		return next($this->_rows);
	}

	/**
	 * @brief index of current element (iterator)
	 * @return array
	 */
	public function key() {
		return key($this->_rows);
	}

	/**
	 * @brief first element (pointer reset, iterator)
	 * @return array
	 */
	public function rewind() {
		return reset($this->_rows);
	}

	/**
	 * @brief check current element (iterator)
	 * @return bool
	 */
	public function valid() {
		return (bool) is_array($this->current());
	}

	public function first() {
		return (isset($this->_rows[0])) ? $this->_rows[0] : null;
	}

	public function last() {
		return $this->_rows[$this->_num_rows - 1];
	}

	public function count() {
		return $this->_num_rows;
	}
}

/**
 * @brief interface database definition
 */
interface IDatabase {
	/**
	 * @brief create database connection
	 * @param string $host IP or domain of the database host
	 * @param string $user user
	 * @param string $passwd password
	 */
	public function connect($host, $user, $pw);

	/**
	 * @brief close database connection
	 */
	public function close();

	/**
	 * @brief select database
	 * @param string $name name of database
	 */
	public function select($db);

	/**
	 * @brief execute query
	 * @param string $sql query
	 * @return mixed
	 */
	public function execute($sql);

	/**
	 * @brief query
	 * @param string $sql
	 * @param int $offset
	 * @param int $limit
	 * @return TDatabaseResultSet
	 */
	public function query($sql, $limit = -1, $offset = 0);
}

/**
 * @brief abstract database layer definition
 */
abstract class Database implements IDatabase {
	/**
	 * @brief current database
	 * @var string
	 */
	protected $database = '';

	/**
	 * @brief database handle
	 * @var resource
	 */
	protected $resource = false;

	/**
	 * @brief container with exectuted queries
	 * @var array
	 */
	protected $statements = array();
}
