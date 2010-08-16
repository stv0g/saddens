<?php
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

?>