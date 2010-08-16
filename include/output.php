<?php

class XmlOutput extends Output {
	public function __construct($debug) {
		parent::__construct('text/xml', 'UTF-8', $debug);
	}

	protected function getOutput() {
		$doc = new DomDocument('1.0', $this->encoding);
		$doc->formatOutput = true;
		$xmlSddns = $doc->createElement('sddns');
		$doc->appendChild($xmlSddns);
		
		foreach ($this->getMessages() as $index => $message) {
			$xmlMessage = $doc->createElement('message');
			$xmlMessage->setAttribute('type', $message['type']);

			if ($message['type'] == 'debug')
				$xmlMessage->setAttribute('level', $message['level']);

			$xmlMessage->appendChild($doc->createElement('description', $message['description']));
			
			
			foreach ($message['data'] as $object) {
				if (method_exists($object, 'toXml')) {
					$xmlMessage->appendChild($object->toXml($doc));
				}
				else {
					$xmlMessage->appendChild($doc->createElement(gettype($object), $object));
				}
			}
			$xmlSddns->appendChild($xmlMessage);
		}
		return $doc->saveXML();
	}
}


// needs JPGraph >= 3.0.7!
class GraphOutput extends Output {
	private $graph;

	public function __construct($debug) {
		parent::__construct('text/html', 'UTF-8', $debug);
		$site = Registry::get('site');
		
		require_once 'jpgraph/jpgraph.php';
	}

	public function getGraph($width, $height, $type = 'line') {
		switch ($type) {
			case 'line':
				$this->graph = new Graph($width, $height);
				break;
			case 'pie':
				$this->graph = new PieGraph($width, $height);
				break;
		}		

		return $this->graph;
	}


	public function showGraph() {
		if (@isset($this->graph))
			$this->graph->Stroke();
	}

	protected function getOutput() {	// TODO beautify
		if (count($this->getMessages()) > 0) {
			echo '<pre>';
			print_r($this->getMessages());
			echo '</pre>';
		}
	}
}

class GifOutput extends Output {
	public function __construct() {
		parent::__construct('image/gif');
	}

	protected function getOutput() {
		$im = imagecreate(1, 1);
		$red = imagecolorallocate($im, 255, 0, 0);
		$green = imagecolorallocate($im, 0, 255, 0);
		
		imagefill($im, 0, 0, (count($this->getMessages(false, array('error', 'exception'))) > 0) ? $red : $green);
		imagegif($im);
		imagedestroy($im);
	}
}

class PlainLineOutput extends Output {
	public function __construct($debug, $fields = array('index', 'time', 'type', 'description', 'data'), $delimiter = "\t", $lineDelimiter = "\n", $escape = true) {
		parent::__construct('text/plain', 'UTF-8', $debug);
		$this->fields = $fields;
		$this->delimiter = $delimiter;
		$this->lineDelimiter = $lineDelimiter;
	}

	protected function getOutput() {
		$str = '';

		foreach($this->getMessages() as $index => $message) {
			foreach ($this->fields as $field) {
				switch ($field) {
					case 'type':
						$str .= $message['type'];
						break;
						
					case 'index':
						$str .= $index;
						break;
						
					case 'time':
						$str .= date('Y-m-d H:i:s', $message['time']);
						break;
						
					case 'description':
							$str .= $message['description'];
						break;
						
					case 'data':
						foreach ($message['data'] as $object) {
							$str .= $this->delimiter . $object;
						}
						break;
				}
				$str .= $this->delimiter;
			}
			$str = substr($str, 0, -1) . $this->lineDelimiter;
		}
		return $str;
	}
}

class DynDnsOutput extends Output {
	public function __construct() {
		parent::__construct('text/plain');
		
		if (!isset($_SERVER['PHP_AUTH_PW']))
			header('WWW-Authenticate: Basic realm="DynDNS API Access"');
	}

	protected function getOutput() {
		$message = current($this->getMessages(false, array('error', 'success')));
		return $message['description'];
	}
}

class HtmlOutput extends Output {
	public function __construct($debug) {
		parent::__construct('text/html', 'UTF-8', $debug);
		
		ob_start();
	}

	protected function getOutput() {
		$site = Registry::get('site');
		
		$str = '<?xml version="1.0" ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title>/dev/nulll - Simple Dynamic Domain Name Service</title>
		<script src="' . $site['path']['web'] . '/include/script.js" type="text/javascript"></script>
		<link rel="stylesheet" type="text/css" href="' . $site['path']['web'] . '/include/style.css" />
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<link rel="shortcut icon" href="/favicon.png" type="image/png" />
		<link rel="icon" href="/favicon.png" type="image/png" />
		<link rel="search" type="application/opensearchdescription+xml" title="Tiny DNS &amp; URL" href="' . $site['path']['web'] . '/opensearch.xml" />
	</head>
	<body>';
					
		$maxDataCount = 0;
		$messages = $this->getMessages();
		foreach ($messages as $message) {
			if (count($message['data']) > $maxDataCount)
				$maxDataCount = count($message['data']);
		}
		
		if (count($messages)) {
			$str .= '<table id="messages">
						<tr><th>type</th><th>index</th><th>time</th><th>description</th><th colspan="100">data</th></tr>';
			
			foreach ($messages as $index => $message) {
				$str .= '<tr class="' . $message['type'] . '">
						<td><img alt="' . $message['type'] . '" src="' . $site['path']['web'] . '/images/' . $message['type'] . '.png" title="' . @$message['level'] . '" /></td>
						<td>#' . $index . '</td>
						<td>' . date('Y-m-d H:i:s', $message['time']) . '</td>
						<td>' . $message['description'] . '</td>';
				
				for($i = 0; $i < $maxDataCount; $i++) {
					$str .= '<td>';
					if (isset($message['data'][$i])) {
						$data = $message['data'][$i];
						if (method_exists($data, 'toHtml')) {
							$str .= $data->toHtml();
						}
						else {
							$str .= $data;
						}
					}
					$str .= '</td>';
				}
				
				$str .= '</tr>';
			}
	
			$str .= '</table>';
		}
		
		$str .= '<div id="content">';
		$str .= ob_get_clean();
		$str .=	'</div>
			</body>
		</html>';
		
		return $str;
	}
}

abstract class Output {
	protected $messages = array();
	public $debug = 0;
	public $format;
	protected $contentType;
	protected $encoding;
	
	function __construct($contentType = 'text/plain', $encoding = 'UTF-8', $debug = 0) {
		$this->contentType = $contentType;
		$this->encoding = $encoding;	
		$this->debug = $debug;
		
		if ($this->contentType != null)
			header('Content-type: ' . $this->contentType . (($this->encoding != null) ? '; charset=' . $this->encoding : ''));
	}

	function add($description, $type = 'notice') {
		$message['time'] = time();
		$message['description'] = $description;
		$message['type'] = $type;
		$message['data'] = array();
		
		$argv = func_get_args();
		$argc = count($argv);
		
		if ($type == 'debug')
			$message['level'] = $argv[2];

		for ($i = ($type == 'debug') ? 3 : 2; $i < $argc; $i++) {
			if (empty($argv[$i]))
				continue;

			if (!is_array($argv[$i])) {
				$message['data'][] = $argv[$i];
			}
			else {
				$message['data'] = array_merge($message['data'], array_values($argv[$i]));
			}
		}

		array_push($this->messages, $message);
	}
	
	protected function getMessages($exclude = true, $args = null) {
		$types = array('notice', 'success', 'error', 'exception', 'warning', 'data'); // 'debug');

		if ($args == null)
			$args = array();
		
		if ($exclude)
			$types = array_diff($types, $args);
		else
			$types = $args;
		
		$messages = array();
		foreach ($this->messages as $message) {
			if (in_array($message['type'], $types) || ($message['type'] == 'debug' && $message['level'] <= $this->debug)) {
				$messages[] = $message;
			}
		}
		
		return $messages;
	}

	static function getInstance($format = null, $debug = 0) {
		switch (strtolower($format)) {
			case 'xml':
				return new XmlOutput($debug);
				break;
			
			case 'txt':
				return new PlainLineOutput($debug, array('index', 'time', 'type', 'description', 'data'), "\t", "\n");
				break;
				
			case 'csv':
				return new PlainLineOutput($debug, array('time', 'type', 'description', 'data'), ";", "\n");
				break;
				
			case 'dyndns':
				return new DynDnsOutput();
				break;

			case 'png':
				return new GraphOutput($debug);
				break;

			case 'gif':
				return new GifOutput();
				break;

			case 'html':
			case 'php':
			default:
				return new HtmlOutput($debug);
				break;
		}
	}

	function exception_handler($exception) {
		$this->add('unhandled ' . get_class($exception), 'exception', $exception);
	}

	function error_handler($errno, $errstr, $errfile, $errline) {
		if (($errno & error_reporting()) == 0) {
			return;
		}
		else {
			switch ($errno) {
				case E_USER_WARNING:
				case E_WARNING:
					$type = 'warning';
					$str = $type;
					break;

				case E_USER_NOTICE:
				case E_NOTICE:
					$type = 'warning';
					$str = 'notice';
					break;
				
				case E_USER_ERROR:
				case E_ERROR:
				default:
					$type = 'error';
					$str = $type;
					break;
				}	
			$this->add($str . ' in script', $type, $errstr . ' in ' . $errfile . ':' . $errline);
		}
	}
	
	abstract protected function getOutput();

	public function __destruct() {
		echo $this->getOutput();
	}
}

?>