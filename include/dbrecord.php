<?php

class DBRecord extends Record implements DBObject {
	public $id;
	public $lifetime;
	public $lastAccessed;

	private $db;

	public function __construct($id, Database $db) {
		$config = Registry::get('config');

		$this->db = $db;

		$sql = 'SELECT *
    		FROM ' . $config['db']['tbl']['records'] . '
    		WHERE id = ' . (int) $id;

		$result = $this->db->query($sql, 1);
		$record = $result->first();

		$this->id = $record['id'];
		$this->lastAccessed = strtotime($record['last_accessed']);
		$this->lifetime = $record['lifetime'];
		$this->host = new DBHost($record['host_id'], $this->db);

		parent::__construct($this->host, (int) $record['ttl'], $record['class'], $record['type'], $record['rdata']);
	}

	public function __destruct() {
		//$this->update();
	}

	public function update() {
		$config = Registry::get('config');

		$sql = 'UPDATE ' . $config['db']['tbl']['records'] . '
				SET
					lifetime = ' . (int) $this->lifetime . ',
					last_accessed = \'' . date('Y-m-d H:i:s', $this->lastAccessed) . '\',
					host_id = \'' . $this->db->escape($this->host->id) . '\',
					ttl = ' . (int) $this->ttl . ',
					class = \'' . $this->db->escape($this->class) . '\',
					type = \'' . $this->db->escape($this->type) . '\',
					rdata = \'' . $this->db->escape( $this->rdata) . '\'
				WHERE id = ' . (int) $this->id;

		$this->db->execute($sql);
	}

	public function toXml(DOMDocument $doc) {
		$xmlRecord = parent::toXml($doc);

		$xmlRecord->setAttribute('id', $this->id);

		$xmlRecord->appendChild($doc->createElement('lifetime', $this->lifetime));
		$xmlRecord->appendChild($doc->createElement('lastaccessed', $this->lastAccessed));

		return $xmlRecord;
	}

	public function delete() {
		$config = Registry::get('config');

		$sql = 'DELETE FROM ' . $config['db']['tbl']['records'] . '
				WHERE id = ' . (int) $this->id;

		$this->db->execute($sql);
	}

	public static function get(Database $db, $filter = false) {
		$config = Registry::get('config');

		$sql = 'SELECT r.id
				FROM ' .  $config['db']['tbl']['records'] . ' AS r
				LEFT JOIN ' .  $config['db']['tbl']['hosts'] . ' AS h
				ON h.id = r.host_id
				WHERE true';

				if (!empty($filter['id']))
					$sql .= ' && id = ' . (int) $filter['id'];
				if (!empty($filter['host']) && $filter['host'] instanceof Host)
					$sql .= ' && host_id = ' . (int) $filter['host']->isRegistred($db);
				if (!empty($filter['host']) && $filter['host'] instanceof DBHost)
					$sql .= ' && host_id = ' . (int) $filter['host']->id;
				if (!empty($filter['host']) && is_string($filter['host']))
					$sql .= ' && hostname = \'' . $db->escape($filter['host']) . '\'';
				if (!empty($filter['zone']) && $filter['zone'] instanceof Zone)
					$sql .= ' && zone = \'' . $db->escape($filter['zone']->name) . '\'';
				if (!empty($filter['zone']) && is_string($filter['zone']))
					$sql .= ' && zone = \'' . $db->escape($filter['zone']->name) . '\'';
				if (!empty($filter['type']))
					$sql .= ' && type = \'' . $db->escape($filter['type']) . '\'';
				if (!empty($filter['class']))
					$sql .= ' && class = \'' . $db->escape($filter['class']) . '\'';
				if (!empty($filter['rdata']))
					$sql .= ' && rdata = \'' . $db->escape($filter['rdata']) . '\'';
				if (!empty($filter['ttl']))
					$sql .= ' && ttl = ' . (int) $filter['ttl'];

		$sql .= ' ORDER BY r.id ASC';

		$result = $db->query($sql);

		$records = array();
		foreach ($result as $record) {
			$records[] = new self($record['id'], $db);
		}
		return $records;
	}
}

?>
