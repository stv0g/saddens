<?php
/**
 * Admin frontend
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

require_once '../include/init.php';

$output = Output::start('html');

$output->add('hits since launch', 'notice', $site['hits']);

if (isAuthentificated()) {
	$output->add('authetificated as', 'notice', $_SERVER['PHP_AUTH_USER']);
}

?>

<div id="admin">
<div style="float: right;"><a href="http://dev.0l.de"><img src="../images/nulll_small.png" alt="/dev/nulll" /></a></div>
<h1>Tiny DNS & URL</h1>
<h3>Administration interface</h3>
<p>by <a href="http://www.steffenvogel.de">Steffen Vogel</a></p>
<hr style="clear: both;" />

<ul>
	<li><a href="cleanup">cleanup</a></li>
	<li>get: <a href="get?data=hosts">hosts</a>, <a href="get?data=records">records</a>, <a href="get?data=uris">uris</a>, <a href="get?data=logs">logs</a>, <a href="get?data=queries">queries</a></li>
	<li><a href="parse">parse</a></li>
	<li><a href="sync">sync</a></li>
	<li>stats: <a href="stats/hosts.php">hosts</a>, <a href="stats/types.png">types</a></li>
</ul>

<hr />
<a href="/expert">expert mode</a> - 
<a href="http://dev.0l.de/projects/sddns/usage">usage</a> - 
<a href="http://dev.0l.de/projects/sddns">wiki</a> - 
<a href="javascript:u='http://d.0l.de/add.html?type=URL&rdata='+encodeURIComponent(location.href);h=encodeURIComponent(window.getSelection().toString().replace(/[\s\x21\x22\x23$
<a href="javascript:installSearchEngine('<?php echo $site['url']; ?>/opensearch.xml');">search plugin</a>
<address><?php echo $_SERVER['SERVER_SIGNATURE']; ?></address>
</div>

<?php
$output->send();
?>
