<?php

require_once '../../include/init.php';
$output = Output::start();

$result = $db->query('SELECT COUNT(*) AS count FROM queries', 1)->first();
$count = $result['count'];

$result = $db->query('SELECT type, COUNT(id) AS sum FROM queries GROUP BY type ORDER BY sum DESC');
	
if ($output instanceof GraphOutput) {
	require_once $site['path']['server'] . '/include/jpgraph/jpgraph_pie.php';

	$data = array();
	$graph = $output->getGraph(500, 400, 'pie');

	foreach ($result as $type) {
	        $data[] = $type['sum'];
	        $legend[] = $type['type'];
	}

	$graph->title->Set('record types');
	$graph->title->SetFont(FF_VERDANA, FS_BOLD, 14);
	$graph->legend->SetFont(FF_VERDANA, FS_NORMAL, 10);
	$graph->SetAntiAliasing();

	$pie = new PiePlot($data);
	$pie->value->SetFont(FF_VERDANA, FS_NORMAL, 9);
	$pie->value->SetColor('black');
	$pie->SetLabelPos(0.7);
	$pie->setLegends($legend);
	$pie->setTheme('sand');
	$pie->SetGuideLines(true, false);
	$pie->SetGuideLinesAdjust(1.4);
	$pie->setCenter(0.32, 0.5);

	$graph->Add($pie);

	$output->showGraph();
}
else {
	foreach ($result as $row) {
		$output->add($row['type'], 'data', round(($row['sum'] / $count) * 100, 5) . ' %', $row['sum']);
	}
}

Output::send();

?>
