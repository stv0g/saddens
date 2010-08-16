<?php

class UserException extends CustomException {}
class ValidationException extends UserException {}
class NameServerException extends CustomException {}

class CustomException extends Exception {
	public function toXml(DOMDocument $doc) {
		$xmlRecord = $doc->createElement('exception');
		$xmlRecord->setAttribute('code', $this->code);

		$xmlRecord->appendChild($doc->createElement('message', $this->message));
		$xmlRecord->appendChild($doc->createElement('line', $this->line));
		$xmlRecord->appendChild($doc->createElement('file', $this->file));
		
		$xmlRecord->appendChild(backtrace2xml($this->getTrace(), $doc)); 

		return $xmlRecord;
	}

	public function toHtml() {
		return $this->message . ' in ' . $this->file . ':' . $this->line;
	}
}

?>
