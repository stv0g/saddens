<?php

interface DBObject extends Object {
	
	public static function get(Database $db, $filter);
	public function delete();
	public function __destruct();
	public function update();
	
}
?>
