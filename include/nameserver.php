<?php

class NameServer implements Object {
	protected $process;
	protected $pipes;

	private $queue;

	public $hostname;
	public $port;

	public function __construct($hostname, $port = 53) {
		$config = Registry::get('config');

		$this->hostname = $hostname;
		$this->port = $port;
	}

	protected function initialize() {
		$output = Registry::get('output');
		$config = Registry::get('config');

		$descriptorspec = array(0 => array('pipe', 'r'),	// stdin
		1 => array('pipe', 'w'),	// stdout
		2 => array('pipe', 'w'));	// stderr

		if ($this->isRunning()) {
			$this->close();
		}

		$this->process = proc_open('nsupdate -d -v', $descriptorspec, $this->pipes);
		$output->add('ns initialized', 'debug', 1);

		$this->queueCommand('server ' . $this->hostname . ' ' . $this->port);
		$this->queueCommand('class ' . $config['sddns']['std']['class']);
	}

	protected final function close() {
		$output = Registry::get('output');
	
		if ($this->isRunning()) {
			fclose($this->pipes[0]);
				
			$return['stdout'] = stream_get_contents($this->pipes[1]);
			$return['stderr'] = stream_get_contents($this->pipes[2]);
				
			fclose($this->pipes[1]);
			fclose($this->pipes[2]);
				
			$return['code'] = proc_close($this->process);
				
			$this->process = null;
			
			$output->add('connection to ns closed', 'debug', 1);
				
			return $return;
		}
		else {
			throw new CustomException('There is no running process to close.');
		}
	}

	protected final function sendQueue() {
		$output = Registry::get('output');
		$site = Registry::get('site');
	
		if ($output->debug > 2)
			$this->queueCommand('show');
		$this->queueCommand('send');
		$output->add('send queue to ns', 'debug', 1);
		
		return $this->close();
	}

	protected final function queueCommand($command) {
		$output = Registry::get('output');
		
		if (!$this->isRunning()) {
			$this->initialize();
		}

		fwrite($this->pipes[0], $command . "\n");
		$this->queue[] = $command;
		
		if (substr($command, 0, 3) != 'key') {
			$output->add('added command to ns queue', 'debug', 3, $command);
		}
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
		$output = Registry::get('output');
		$config = Registry::get('config');
		
		$cli = 'dig -c ' . $class . ' -t ' . $type . ' ' . $host . ' @' . $this->hostname . ' +noall +answer';
		$output->add('execute dig', 'debug', 2, $cli);
		exec(escapeshellcmd($cli), $output, $returnCode);
		
		if ($returnCode != 0)
			throw new NameServerException('dig query failed with code: ' . $returnCode);

		$results = array();
		
		foreach ($output as $line) {
			if (substr($line, 0, 1) != ';' && !empty($line)) {
				$result = array();
				foreach (explode("\t", $line) as $column) {
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

?>
