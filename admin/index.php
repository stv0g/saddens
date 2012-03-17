<?php
require_once '../include/init.php';

$output = Output::start('html');

$output->add('hits since launch', 'notice', $site['hits']);

if (isAuthentificated()) {
	$output->add('authetificated as', 'notice', $_SERVER['PHP_AUTH_USER']);
}

?>

<div id="admin">
<div style="float: right;"><a href="http://0l.de"><img src="../images/nulll_small.png" alt="/dev/nulll" /></a></div>
<h1>Tiny DNS & URL</h1>
<h3>Administration interface</h3>
<p>by <a href="http://www.steffenvogel.de">Steffen Vogel</a></p>
<hr style="clear: both;" />

<ul>
	<li><a href="cleanup">cleanup</a></li>
	<li><a href="get">get</a></li>
	<li><a href="parse">parse</a></li>
	<li><a href="sync">sync</a></li>
</ul>

<hr />
<a href="../expert">expert mode</a> - 
<?php if (isAuthentificated()) echo '<a href="admin/">admin</a> - '; ?>
<a href="http://0l.de/projects/sddns/usage">usage</a> - 
<a href="http://0l.de/projects/sddns/">wiki</a> - 
<a href="javascript:u='http://d.0l.de/add.html?type=URL&rdata='+encodeURIComponent(location.href);h=encodeURIComponent(window.getSelection().toString().replace(/[\s\x21\x22\x23$
<a href="javascript:installSearchEngine('<?php echo $site['url']; ?>/opensearch.xml');">search plugin</a>
<address><?php echo $_SERVER['SERVER_SIGNATURE']; ?></address>
</div>

<?php
$output->send();
?>
