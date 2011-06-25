<?php
require_once 'include/init.php';
$output = Output::start('html');
?>        
<div id="simple">

<header>
  <a href="http://0l.de"><img src="images/nulll_small.png" alt="/dev/nulll" /></a>
  <h1>Tiny DNS &amp; URL</h1>
</header>

<form name="formular" target="ifr" action="add.php" method="post" onsubmit="submit_simple(this);">
  <dl>
    <dt><label for="host"><a href="http://de.wikipedia.org/wiki/Domain">Domain</a></label></dt>
    <dd>
     <input type="text" name="host" value="<?= @$_REQUEST['host'] ?>" tabindex="1" size="19" style="width:134px" />.
     <select name="zone" size="1">
      <?php foreach ($config['sddns']['zones'] as $zone) {
        $selected = $_REQUEST['zone'] == $zone->name ? ' selected="selected"' : '';   ?>
       <option <?= $selected ?> value="<?= $zone->name ?>"><?= $zone->name ?></option>
        <?php  } ?>
     </select>
    </dd>

    <dt><label for="rdata"><a href="http://de.wikipedia.org/wiki/IP-Adresse">IP</a> / <a href="http://de.wikipedia.org/wiki/Uniform_Resource_Locator">URL</a></label></dt>
    <dd><input style="width:217px" tabindex="2" value="<?= isset($_REQUEST['rdata']) ? $_REQUEST['rdata'] : $_SERVER['REMOTE_ADDR'] ?>" type="text" name="rdata" /></dd>

<?php
if (!isAuthentificated())
	echo '<dt><label for="pw">Password</label></dt>
		<dd><input style="width:217px" tabindex="3" type="password" name="pw" /></dd>';
?>

  </dl>

  <input type="submit" tabindex="4" value="register" />
  <input type="hidden" name="type" value="A" />
</form>

<iframe name="ifr" id="ifr"></iframe>

<footer>
  <p>by <a href="http://www.steffenvogel.de">Steffen Vogel</a> - <a href="expert">expert mode</a> - <a href="http://0l.de/projects/sddns/usage">usage help</a> - <a href="javascript:installSearchEngine('<?= $site['url']?>/opensearch.xml');">searchplugin</a></p>
  <a href="javascript:u='http://d.0l.de/add.html?type=URL&rdata='+encodeURIComponent(location.href);h=encodeURIComponent(window.getSelection().toString().replace(/[\s\x21\x22\x23\x24\x25\x26\x27\x28\x29\x2A\x2B\x2C\x2E\x2F\x3A\x3B\x3C\x3D\x3F\x40\x5B\x5C\x5D\x5E\x5F\x60\x7B\x7C\x7C\x7D\x7E]+/gi,'-').replace(/^\-+/,'').replace(/\-+$/,''));if(!h){h=prompt('Subdomain','');}if(h){u+='&host='+h;}location.href=u"><img style="margin-bottom: -6px;" src="images/bookmarklet.gif" alt="/dev/nulll/url bookmarklet" /></a> drag this button into your bookmarks for creating tiny urls easily!<p>
</footer>

</div>

<?php
$output->send();
?>
