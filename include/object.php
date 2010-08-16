<?php

interface Object {

	public function __toString();
	public function toXml(DOMDocument $doc);
	public function toHtml();

}

?>
