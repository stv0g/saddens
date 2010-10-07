<?php

require_once '../../include/init.php';
$output = Output::start();

$result = $db->query('SELECT DISTINCT hostname, COUNT(hostname) AS sum FROM queries GROUP BY hostname ORDER BY sum DESC', (empty($_GET['n'])) ? 1000 : (int) $_GET['n']);

foreach ($result as $row) {
	$output->add($row['hostname'], 'data', $row['sum']);
}

Output::send();

?>
