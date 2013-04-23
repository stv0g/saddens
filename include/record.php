<?php
/**
 * Record class
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

class Record implements Object {
	public $host, $ttl, $class, $type, $rdata;

	/*
	 * Constructors
	 */
	public function __construct(Host $host, $ttl, $class, $type, $rdata) {
		$config = Registry::get('config');

		$this->host = $host;

		if (is_int($ttl) && $ttl > 0 && $ttl <= $config['sddns']['max_ttl']) {
			$this->ttl = $ttl;
		} else {
			throw new UserException('Invalid ttl: ' . $ttl);
		}

		if (in_array($class, $config['sddns']['classes'])) {
			$this->class = $class;
		} else {
			throw new UserException('Invalid class: ' . $class);
		}

		if (in_array($type, $config['sddns']['types'])) {
			$this->type = $type;
		} else {
			throw new UserException('Invalid type: ' . $type);
		}

		$this->setRData($rdata);
	}

	/*
	 * Setter & Getter
	 */
	public function setRData($rdata) {
		if ($this->isRData($rdata, $this->type)) {
			switch ($this->type) {
				case 'A':
					$this->rdata = new IpV4($rdata);
					break;
				case 'AAAA':
					$this->rdata = new IpV6($rdata);
                                        break;
				default:
					$this->rdata = $rdata;
			}
		} else {
			throw new ValidationException('Invalid rdata: ' . $rdata);
		}
	}

	/*
	 * Database
	 */
	public function add(Database $db, $lifetime) {
		$config = Registry::get('config');
		$db = Registry::get('db');

		if ($this->host->isRegistred($db)) {
			$host = new DBHost($this->host->isRegistred($db), $db);
		}
		else {
			throw new UserException('Unable to add record: Host is not registred!');
		}

		$sql = 'INSERT INTO ' . $config['db']['tbl']['records'] . ' (host_id, ttl, class, type, rdata, created, last_accessed, lifetime, ip) VALUES (
					\'' . $db->escape($host->id) . '\',
					' . (int) $this->ttl . ',
					\'' . $db->escape($this->class) . '\',
					\'' . $db->escape($this->type) . '\',
					\'' . $db->escape($this->rdata) . '\',
					NOW(),
					NOW(),
					' . (int) $lifetime . ',
					\'' . $db->escape($_SERVER['REMOTE_ADDR']) . '\')';


		$db->execute($sql);

		return new DBRecord($db->lastId(), $db);
	}

	/*
	 * Checks
	 */
	static function isRData($rdata, $type) {
		$valid = false;

		switch ($type) {
			case 'A':
				$valid = IpV4::isValid($rdata);
				break;

			case 'AAAA':
				$valid = IpV6::isValid($rdata);
				break;

			case 'CNAME':
			case 'NS':
				$valid = Host::isValid($rdata);
				break;

			case 'MX':
				@list($priority, $hostname) = explode($rdata, ' ');
				$valid = (is_numeric($priority) && Host::isValid($hostname));
				break;

			case 'URL':
				$valid = Uri::isValid($rdata);

			default:
				$valid = true;
				break;
		}

		return $valid;
	}

	public function isRegistred(Database $db) {
		$config = Registry::get('config');

		$sql = 'SELECT *
			FROM ' .  $config['db']['tbl']['records'] . ' AS r
			WHERE
				host_id = ' . (int) $this->host->isRegistred($db) . ' &&
				class = \'' . $db->escape($this->class) . '\' &&
				type = \'' . $db->escape($this->type) . '\' &&
				rdata = \'' . $db->escape($this->rdata) . '\'';

		$result = $db->query($sql, 1);
		$record = $result->first();

		return ($result->count() > 0) ? $record['id'] : false;
	}

	/*
	 * Output
	 */
	function __toString() {
		$str = $this->host;

		if (isset($this->ttl)) {
			$str .= ' ' . $this->ttl;
		}

		if (isset($this->class)) {
			$str .= ' ' . $this->class;
		}

		$str .= ' ' . $this->type . ' ' . $this->rdata;

		return $str;
	}

	public function toXml(DOMDocument $doc) {
		$xmlRecord = $doc->createElement('record');

		$xmlRecord->appendChild($this->host->toXml($doc));
		$xmlRecord->appendChild($doc->createElement('ttl', $this->ttl));
		$xmlRecord->appendChild($doc->createElement('class', $this->class));
		$xmlRecord->appendChild($doc->createElement('type', $this->type));

		switch ($this->type) {
                        case 'A':
                        case 'AAAA':
				$xmlRecord->appendChild($this->rdata->toXml($doc));
				break;
			default:
				$xmlRecord->appendChild($doc->createElement('rdata', $this->rdata));
		}

		return $xmlRecord;
	}

	public function toHtml() {
		$html = '' . $this->host->toHtml() . '&nbsp;<a target="_blank" href="/?host=' . $this->host->toPunycode() . '&ttl=' . $this->ttl . '&type=' . $this->type . '&class=' . $this->class . '&rdata=' . $this->rdata . '">' . $this->ttl . '&nbsp;' . $this->class . '&nbsp;' . $this->type . '</a>';

		$html .= '&nbsp;';
		switch ($this->type) {
			case 'A':
			case 'AAAA':
				$html .= $this->rdata->toHtml();
			break;

			case 'NS':
			case 'CNAME':
				$html .= '<a target="_blank" href="http://' . $this->rdata . '">' . $this->rdata . '</a>';
			break;

			default:
				$html .= $this->rdata;
			break;
		}

		return $html;
	}
}

?>
