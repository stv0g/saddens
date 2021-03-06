<?php
/**
 * Zone class
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

class Zone extends NameServer implements Object {

	public $name;
	private $key;

	function __construct($nserver, $name, $key, $nsport = 53) {
		parent::__construct($nserver, $nsport);

		$this->name = $name;
		$this->key = $key;
	}

	protected function initQueue() {
		parent::initQueue();

		$this->queueCommand('zone ' . $this->name);
		$this->queueCommand('key ' . $this->key['name'] . ' ' . $this->key['hmac']);
	}

	/*
	 * Maintenance
	 */
	function cleanup(Database $db) {
		global $config;
		global $output;

		// expired records & records without host
		$sql = 'DELETE r FROM ' . $config['db']['tbl']['records'] . ' AS r
				LEFT JOIN ' . $config['db']['tbl']['hosts'] . ' AS h
				ON h.id = r.host_id
				WHERE (
						(r.last_accessed +  INTERVAL r.lifetime SECOND) < NOW()
						&& h.zone = \'' . $db->escape($this->name) . '\'
						&& r.lifetime != 0
					) || h.id IS NULL';

		$db->execute($sql);
		if ($db->affectedRows() > 0) {
			$output->add('records deleted from db', 'success', $db->affectedRows(), $this);
		}

		// expired urls & uris without host
		$sql = 'DELETE u FROM ' . $config['db']['tbl']['uris'] . ' AS u
				LEFT JOIN ' . $config['db']['tbl']['hosts'] . ' AS h
				ON h.id = u.host_id
				WHERE (
						(u.last_accessed + INTERVAL u.lifetime SECOND) < NOW()
						&& h.zone = \'' . $db->escape($this->name) . '\'
						&& u.lifetime != 0
					) || h.id IS NULL';

		if ($db->affectedRows() > 0) {
			$output->add('uris deleted from db', 'success', $db->affectedRows(), $this);
		}

		// hosts without records or url
		$sql = 'DELETE h
				FROM ' . $config['db']['tbl']['hosts'] . ' AS h
				LEFT JOIN ' . $config['db']['tbl']['records'] . ' AS r
					ON h.id = r.host_id
				LEFT JOIN ' . $config['db']['tbl']['uris'] . ' AS u
					ON h.id = u.host_id
				WHERE
					(r.id IS NULL && u.id IS NULL) &&
					h.zone = \'' . $db->escape($this->name) . '\'';

		$db->execute($sql);
		if ($db->affectedRows() > 0) {
			$output->add('hosts deleted from db', 'success', $db->affectedRows(), $this);
		}
	}

	public function sync(Database $db) {
		global $output;

		$nsRecords = $this->getRecordsFromNS();
		$dbRecords = $this->getRecordsFromDB($db);

		$delete = array_diff($nsRecords, $dbRecords);
		$add = array_diff($dbRecords, $nsRecords);

		if (empty($delete) && empty($add)) {
			$output->add('ns in sync', 'success');
			return true;
		}

		$this->initQueue();

		foreach ($add as $record) {
			$this->add($record);
			$output->add('record added to ns', 'success', $record);
		}

		foreach ($delete as $record) {
			$this->delete($record);
			$output->add('record deleted from ns', 'success', $record);
		}

		$result = $this->commitQueue();

		if ($result['code']) {
                        throw new NameServerException('error during nameserver update', $result);
                }

		if (isAuthentificated()) {
			$output->add('ns response', 'debug', 7, $result); // includes key!
		}

		$output->add('ns synced', 'success');
		return true;
	}

	public function add(Record $record) {
		global $config;

		if ($record->host->zone->name != $this->name) {
			throw new NameServerException('zone mismatch: trying to add record ' . $record . ' to zone ' . $this);
		}

		parent::add($record);
	}

	public function delete(Record $record) {
		global $config;

		if ($record->host->zone->name != $this->name) {
			throw new NameServerException('zone mismatch: trying to delete record ' . $record . ' from zone ' . $this);
		}

		parent::delete($record);
	}

	/*
	 * Getter
	 */
	public function getRecordsFromNS() {
		global $config;
		global $output;

		$records = array();

		foreach (parent::query($this->name, 'AXFR') as $result) {
			if (in_array($result[3], $config['sddns']['types']) && strlen($result[0]) > strlen($this->name) + 1) {
				$hostname = substr($result[0], 0, -(strlen($this->name) + 2));

				switch ($result[3]) {
					case 'NS':
					case 'MX':
					case 'CNAME':
						$rdata = substr($result[4], 0, -1);
					break;

					case 'TXT':
						$rdata = trim($result[4], '"');
					break;

					default:
						$rdata = $result[4];
				}

				try {
					$host = new Host($hostname, $this);
					$records[] = new Record($host, (int) $result[1], $result[2], $result[3], $rdata);
				} catch (UserException $e) {
					$output->add('error during parsing', 'error', $e);
				}
			}
		}
		return $records;
	}

	public function getRecordsFromDB(Database $db) {
		return DBRecord::get($db, array('zone' => $this));
	}

	public function getUrisFromDB(Database $db) {
		return DBUri::get($db, array('zone' => $this));
	}

	public function getHostsFromDB(Database $db) {
		return DBHost::get($db, array('zone' => $this));
	}

	/*
	 * Output
	 */
	public function __toString() {
		return parent::__toString() . '/' . $this->name;
	}

	public function toXml(DOMDocument $doc) {
		$xmlZone = parent::toXml($doc, 'zone');

		$xmlZone->appendChild($doc->createElement('zone', $this->name));

		return $xmlZone;
	}

	public function toHtml() {
		return $this;
	}
}
