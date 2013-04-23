<?php
/**
 * Uri class
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

class Uri implements Object {

	public $uri, $host, $frame;

	/*
	 * Constructor
	 */
	function __construct($uri, DBHost $host) {
		$this->uri = $uri;
		$this->host = $host;
	}

	/*
	 * Database
	 */
	public function add(Database $db, $lifetime) {
		$config = Registry::get('config');
		$db = Registry::get('db');

		$sql = 'INSERT INTO ' . $config['db']['tbl']['uris'] . ' (host_id, uri, frame, lifetime, last_accessed, created, ip) VALUES(
					' . $this->host->id . ',
					\'' . $this->uri . '\',
					' . (($this->frame) ? 1 : 0) . ',
					' . $lifetime . ',
					NOW(),
					NOW(),
					\'' . $_SERVER['REMOTE_ADDR'] . '\')';


		$db->execute($sql);

		return new DBUri($db->lastId(), $db);
	}

	/*
	 * Checks
	 */
	 static function isValid($uri) {
	 	 return true; // TODO
	 }

	/*
	 * Setter & Getter
	 */
	public function setUri($uri) {
                if ($this->isValid($uri)) {
			$this->uri = $uri;
		}
		else {
			throw new ValidationException('Invalid uri: ' . $uri);
		}
	}


	/*
	 * Output
	 */
	public function __toString() {
		return $this->uri;
	}

	public function toHtml() {
		return '<a target="_blank" href="' . $this . '">' . $this . '</a>';
	}

	public function toXml(DOMDocument $doc) {
		$xmlUri = $doc->createElement('uri');

		$xmlUri->appendChild($doc->createElement('uri', $this->uri));
		$xmlUri->appendChild($this->host->toXml($doc));

		return $xmlUri;
	}
}

?>
