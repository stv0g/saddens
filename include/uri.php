<?php

class Uri implements Object {
	
	public $uri, $host;
	
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

		$sql = 'INSERT INTO ' . $config['db']['tbl']['uris'] . ' (host_id, uri, lifetime, last_accessed, created, ip) VALUES(
					' . $this->host->id . ',
					\'' . $this->uri . '\',
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
