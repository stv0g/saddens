<?php
/**
 * Exception class
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

class CustomException extends Exception {

	protected $data;

	function __construct($message = '', $data = array(), $code = 0) {
		$this->data = $data;
		parent::__construct($message, $code);
	}

	public function getData() {
		return $this->data;
	}

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

class NameServerException extends CustomException {}
class UserException extends CustomException {
}
class AuthentificationException extends UserException {}
class ValidationException extends UserException {}
class MissingArgumentException extends UserException {}
