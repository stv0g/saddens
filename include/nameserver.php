<?php
/**
 * Nameserver class
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

class NameServer implements Object {

	protected $process;
	protected $pipes;

	private $queue;

	public $hostname;
	public $port;

	public function __construct($hostname, $port = 53) {
		global $config;

		$this->hostname = $hostname;
		$this->port = $port;
	}

	protected function open() {
		global $output;

		$descriptorspec = array(
			0 => array('pipe', 'r'),	// stdin
			1 => array('pipe', 'w'),	// stdout
			2 => array('pipe', 'w')		// stderr
		);

		if ($this->isRunning()) {
			throw new NameserverException('ns connection' , 'already established');
		}

		$this->process = proc_open('nsupdate -d -v', $descriptorspec, $this->pipes);

		if ($this->isRunning()) {
			$output->add('ns connection', 'debug', 3, 'established');
		}
		else {
			throw new NameserverException('ns connection', 'failed');
		}

		return true;
	}

	protected function close() {
		global $output;

		if (!$this->isRunning()) {
			throw new NameserverException('there is no running process to close');
		}

		fclose($this->pipes[0]);

		$result['stdout'] = stream_get_contents($this->pipes[1]);
		$result['stderr'] = stream_get_contents($this->pipes[2]);

		fclose($this->pipes[1]);
		fclose($this->pipes[2]);

		$result['code'] = proc_close($this->process);

		$output->add('ns connection', 'debug', 3, 'closed');

		$this->process = null;

		return $result;
	}

	protected function initQueue() {
		global $config;

		$this->queue = array();

		$this->queueCommand('server ' . $this->hostname . ' ' . $this->port);
		$this->queueCommand('class ' . $config['sddns']['std']['class']);
		$this->queueCommand('ttl ' . $config['sddns']['std']['ttl']);
	}

	private function sendQueue() {
		while ($command = array_shift($this->queue)) {
			$this->sendCommand($command);
		}
	}

	protected function commitQueue() {
		$this->open();

		$this->queueCommand('show');
		$this->queueCommand('send');
		$this->queueCommand('answer');

		$this->sendQueue();

		return $this->close();
	}

	private function sendCommand($command) {
		global $output;

                fwrite($this->pipes[0], $command . "\n");

		if (substr($command, 0, 3) != 'key') {
			$output->add('ns command', 'debug', 3, $command);
		}
	}

	protected function queueCommand($command) {
		$this->queue[] = $command;
	}

	protected function add(Record $record) {
		$this->queueCommand('update add ' . $record);
	}

	protected function delete(Record $record) {
		$this->queueCommand('update delete ' . $record);
	}

	private function isRunning() {
		return is_resource($this->process);
	}

	public function query($host, $type = 'A', $class = 'IN') {
		global $output;
		global $config;

		$cli = 'dig -c ' . $class . ' -t ' . $type . ' ' . $host . ' @' . $this->hostname . ' +noall +answer';
		$output->add('execute dig', 'debug', 2, $cli);
		exec(escapeshellcmd($cli), $return, $returnCode);

		if ($returnCode != 0) {
			throw new NameServerException('dig query', 'failed', $returnCode);
		}

		$results = array();

		foreach ($return as $line) {
			if (substr($line, 0, 1) != ';' && !empty($line)) {
				$result = array();
				foreach (preg_split("/\s+/", $line) as $column) {
					if (!empty($column))
					$result[] = $column;
				}
				$results[] = $result;
			}
		}
		return $results;
	}

	/*
	 * Output
	 */
	public function __toString() {
		return $this->hostname . ':' . $this->port;
	}

	public function toXml(DOMDocument $doc, $tagName = 'nameserver') {
		$xmlNs = $doc->createElement($tagName);

		$xmlNs->appendChild($doc->createElement('hostname', $this->hostname));
		$xmlNs->appendChild($doc->createElement('port', $this->port));

		return $xmlNs;
	}

	public function toHtml() {
		return 'nameserver: ' . $this;
	}
}

