<?php
/**
 * DBUri class
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

class DBUri extends Uri implements DBObject {
	public $id;
	public $lifetime;
	public $lastAccessed;
	public $accessed;

	private $db;

	public function __construct($id, Database $db) {
		global $config;

		$this->db = $db;

		$sql = 'SELECT *
    		FROM ' . $config['db']['tbl']['uris'] . '
    		WHERE id = ' . (int) $id;

		$result = $this->db->query($sql, 1);
		$uri = $result->first();

		$this->id = $uri['id'];
		$this->lastAccessed = strtotime($uri['last_accessed']);
		$this->lifetime = $uri['lifetime'];
		$this->accessed = $uri['accessed'];
		$this->frame = $uri['frame'];
		$this->host = new DBHost($uri['host_id'], $this->db);

		parent::__construct($uri['uri'], $this->host);
	}

	public function update() {
		global $config;

		$sql = 'UPDATE ' . $config['db']['tbl']['uris'] . '
				SET
					host_id = ' . (int) $this->host->id . ',
					uri = \'' . $this->db->escape($this->uri) . '\',
					frame = ' . (($this->frame) ? 1 : 0) . ',
					accessed = ' . (int) $this->accessed . ',
					last_accessed = \'' . date('Y-m-d H:i:s', $this->lastAccessed) . '\',
					lifetime = ' . (int) $this->lifetime . '
				WHERE id = ' . (int) $this->id;

		$this->db->execute($sql);
	}

	public function toXml(DOMDocument $doc) {
		$xmlUri = parent::toXml($doc);

		$xmlUri->setAttribute('id', $this->id);

		$xmlUri->appendChild($doc->createElement('lifetime', $this->lifetime));
		$xmlUri->appendChild($doc->createElement('lastaccessed', $this->lastAccessed));

		return $xmlUri;
	}

	public function delete() {
		global $config;

		$sql = 'DELETE FROM ' . $config['db']['tbl']['uris'] . '
				WHERE id = ' . (int) $this->id;

		$this->db->execute($sql);
	}

	public static function get(Database $db, $filter = false, $order = array()) {
		global $config;

		$sql = 'SELECT u.id
				FROM ' .  $config['db']['tbl']['uris'] . ' AS u
				LEFT JOIN ' .  $config['db']['tbl']['hosts'] . ' AS h
				ON h.id = u.host_id
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
					$sql .= ' && zone = \'' . $db->escape($filter['zone']) . '\'';
				if (!empty($filter['uri']))
					$sql .= ' && uri = \'' . $filter['uri'] . '\'';


		$sql .= ' ORDER BY';
		foreach ($order as $column => $dir) {
			$sql .= ' ' . $column . ' ' . $dir . ',';
		}
		$sql .= ' u.id ASC';

		$result = $db->query($sql);

		$uris = array();
		foreach ($result as $uri) {
			$uris[] = new self($uri['id'], $db);
		}
		return $uris;
	}
}
