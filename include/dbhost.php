<?php

class DBHost extends Host implements DBObject {
	public $id;
	public $generated;
	private $password;

	private $db;

	public function __construct($id, Database $db) {
		$config = Registry::get('config');

		$this->db = $db;

		$sql = 'SELECT * FROM ' . $config['db']['tbl']['hosts'] . ' WHERE id = ' . (int) $id;
		$result = $this->db->query($sql, 1);

		if ($result->count() == 1) {
			$host = $result->first();
			$this->id = $host['id'];
			parent::__construct($host['hostname'], $config['sddns']['zones'][$host['zone']], $host['generated']);
		}
		else {
			throw new CustomException('Host with id ' . $id . ' not found!');
		}
	}

	public function __destruct() {
		//$this->update();
	}

	public function update() {
		$config = Registry::get('config');

		$sql = 'UPDATE ' . $config['db']['tbl']['hosts'] . '
				SET
					hostname = \'' . $this->db->escape($this->toPunycode()) . '\',
					zone = \'' . $this->db->escape($this->zone->name) . '\',
					password = \'' . $this->db->escape(sha1($this->password)) . '\',
					generated = \'' .$this->db->escape( $this->generated) . '\'
				WHERE id = ' . (int) $this->id;

		$this->db->execute($sql);
	}

	public function delete() {
		$config = Registry::get('config');

		if ($this->getRecordsFromDB() > 0) {
			throw new UserException('Host has records!');
		}
		elseif ($this->getUrisFromDB() > 0) {
			throw new UserException('Host has uris!');
		}
		else {
			$sql = 'DELETE FROM ' .  $config['db']['tbl']['hosts'] . '
					WHERE id = ' . (int) $this->id;
			$this->db->execute($sql);
		}
	}

	public function checkPassword($pw) {
		$config = Registry::get('config');

		$sql = 'SELECT password
			     FROM ' .  $config['db']['tbl']['hosts'] . '
			     WHERE hostname = \'' . $this->db->escape($this->toPunycode()) . '\' && zone = \'' . $this->db->escape($this->zone->name) . '\'';

		$result = $this->db->query($sql, 1);
		$entry = $result->first();

		return ($entry['password'] === sha1($pw)) && !empty($pw);
	}

	public function getRecordsFromDB() {
		return DBRecord::get($this->db, array('host' => $this));
	}

	public function getUrisFromDB() {
		return DBRUri::get($this->db, array('host' => $this));
	}

	public static function get(Database $db, $filter = false, $order = array()) {
		$config = Registry::get('config');

		$sql = 'SELECT id
				FROM ' .  $config['db']['tbl']['hosts'] . '
				WHERE true';

				if (!empty($filter['id']))
					$sql .= ' && id = ' . (int) $filter['id'];
				if (!empty($filter['host']) && is_string($filter['host']))
					$sql .= ' && hostname = \'' . $db->escape($filter['host']) . '\'';
				if (!empty($filter['zone']) && $filter['zone'] instanceof Zone)
					$sql .= ' && zone = \'' . $db->escape($filter['zone']->name) . '\'';
				if (!empty($filter['zone']) && is_string($filter['zone']))
					$sql .= ' && zone = \'' . $db->escape($filter['zone']->name) . '\'';
				if (!empty($filter['generated']))
					$sql .= ' && generated = ' . ($filter['generated']) ? '1' : '0';

		$sql .= ' ORDER BY';
		foreach ($order as $column => $dir) {
			$sql .= ' ' . $column . ' ' . $dir . ',';
		}
		$sql .= ' id ASC';


		$result = $db->query($sql);

		$hosts = array();
		foreach ($result as $host) {
			$hosts[] = new self($host['id'], $db);
		}
		return $hosts;
	}

	/*
	 * Output
	 */
	public function toXml(DOMDocument $doc) {
		$xmlRecord = parent::toXml($doc);

		$xmlRecord->setAttribute('id', $this->id);

		return $xmlRecord;
	}
}

?>
