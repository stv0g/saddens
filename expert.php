<?php
/**
 * Expert frontend
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

require_once 'include/init.php';

$output = Output::start('html');

$output->add('hits since launch', 'notice', $site['hits']);

if (isAuthentificated()) {
	$output->add('authentificated as', 'notice', $_SERVER['PHP_AUTH_USER']);
}

$ttl = (!empty($_REQUEST['ttl'])) ? $_REQUEST['ttl'] : $config['sddns']['std']['ttl'];
$lifetime = (!empty($_REQUEST['lifetime'])) ? $_REQUEST['lifetime'] : (isAuthentificated()) ? 0 : $config['sddns']['std']['lifetime'];
$class = (!empty($_REQUEST['class'])) ? $_REQUEST['class'] : $config['sddns']['std']['class'];
$rdata = (!empty($_REQUEST['rdata'])) ? $_REQUEST['rdata'] : $_SERVER['REMOTE_ADDR'];

// type
if (!empty($_REQUEST['type'])) {
	if (in_array($_REQUEST['type'], $config['sddns']['types'])) {
		$type = $_REQUEST['type'];
	}
	else {
		throw new UserException('invalid type');
	}
}
else if (IpV4::isValid($rdata)) {
	$type = 'A';
}
else if (IpV6::isValid($rdata)) {
	$type = 'AAAA';
}
else {
	$type = $config['sddns']['std']['type'];
}
?>

<div id="expert">
	<div style="float: right;">
		<a href="http://dev.0l.de">
			<img src="images/nulll_small.png" alt="/dev/nulll" />
		</a>
	</div>
	<h1>Tiny DNS & URL</h1>
	<h3>Expert interface</h3>
	<p>by <a href="http://www.steffenvogel.de">Steffen Vogel</a></p>
	<hr style="clear: both;" />
	<form onsubmit="submit_expert(this);" method="post" autocomplete="off" >
	<table>
		<tr>
			<td><label for="command">operation</label></td>
			<td><select name="command" size="1">
				<?php foreach ($config['sddns']['cmds'] as $cmd) {
					echo '<option' . ((@$_REQUEST['command'] == $cmd) ? ' selected="selected"' : '') . ' value="' . $cmd . '">' . $cmd . '</option>';
				} ?>
			</select></td>
		</tr>
		<tr>
			<td><label for="format">format</label></td>
			<td><select name="format" size="1">
				<?php foreach ($config['sddns']['formats'] as $format) {
					echo '<option  value="' . $format . '">' . $format . '</option>';
				} ?>
				<option><i>none</i></option>
			</select></td>
			<td><input type="checkbox" name="debug" value="1" /> include debugging information</td>
		</tr>
		<tr>
			<td><label for="host">hostname</label></td>
			<td><input type="text" name="host" value="<?php echo @$_REQUEST['host']; ?>" size="40" />.<select name="zone" size="1">';
				<?php foreach ($config['sddns']['zones'] as $zone) {
					echo '<option' . (($_REQUEST['zone'] == $zone->name) ? ' selected="selected"' : '') . ' value="' . $zone->name . '">' . $zone->name . '</option>';
				} ?>
			</select></td>
			<td>optional; random or servername</td>
		</tr>
		<tr>
			<td><label for="ttl">ttl</label></td>
			<td><input type="text" name="ttl" value="<?php echo $ttl; ?>" size="10"/> seconds</td>
			<td>time to live in cache; max <?php echo $config['sddns']['max_ttl']; ?> seconds</td>
		</tr>
		<tr>
			<td><label for="lifetime">lifetime</label></td>
			<td><input type="text" name="lifetime" value="<?php echo $lifetime; ?>" size="10" /> seconds</td>
			<td>lifetime of a record/url without being updated/touched; max <?php echo (isAuthentificated()) ? '0 (unlimited)' : $config['sddns']['max_lifetime']; ?></td>
		</tr>
		<tr>
			<td><label for="class">class</label></td>
			<td><select name="class" size="1">
				<?php foreach ($config['sddns']['classes'] as $c) {
					echo '<option' . (($c == $class) ? ' selected="selected"' : '') . ' value="' . $c . '">' . $c . '</option>';
				} ?>
			<option><i>all</i></option></select></td>
		</tr>
		<tr>
			<td><label for="type">type</label></td>
			<td><select name="type" size="1">
				<?php foreach ($config['sddns']['types'] as $t) {
					echo '<option' . (($t == $type) ? ' selected="selected"' : '') . ' value="' . $t . '">' . $t . '</option>';
				} ?>
			<option value=""><i>all</i></option></select></td>
		</tr>
		<tr>
			<td><label for="rdata">rdata</label></td>
			<td><input value="<?php echo $rdata; ?>" type="text" size="50" name="rdata" /></td>
			<td><input type="checkbox" value="1" name="frame" /> hide uri in a frameset</td>
		</tr>
		<tr>
			<td><label for="pw">password</label></td>
			<td><input type="password" name="pw" size="50" /></td>
			<td>optional; random generated</td>
		</tr>
	</table>
	<input type="submit" />
</form>

<hr />
<a href="/simple">simple mode</a> - 
<?php if (isAuthentificated()) echo '<a href="/admin">admin</a> - '; ?>
<?php if (!isAuthentificated()) echo '<a href="/login">login</a> - '; ?>
<a href="http://dev.0l.de/projects/sddns/usage">usage</a> - 
<a href="http://dev.0l.de/projects/sddns">wiki</a> - 
<a href="javascript:u='http://d.0l.de/add.html?type=URL&rdata='+encodeURIComponent(location.href);h=encodeURIComponent(window.getSelection().toString().replace(/[\s\x21\x22\x23\x24\x25\x26\x27\x28\x29\x2A\x2B\x2C\x2E\x2F\x3A\x3B\x3C\x3D\x3F\x40\x5B\x5C\x5D\x5E\x5F\x60\x7B\x7C\x7C\x7D\x7E]+/gi,'-').replace(/^\-+/,'').replace(/\-+$/,''));if(!h){h=prompt('Subdomain','');}if(h){u+='&host='+h;}location.href=u">bookmarklet</a> - 
<a href="javascript:installSearchEngine('<?php echo $site['url']; ?>/opensearch.xml');">search plugin</a>
<address><?php echo $_SERVER['SERVER_SIGNATURE']; ?></address>

</div>
