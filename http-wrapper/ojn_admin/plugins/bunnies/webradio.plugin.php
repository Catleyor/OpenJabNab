<?php 
$Ztamps = $ojnAPI->GetListofZtamps(false);
if(!empty($_POST['a'])) {
	if($_POST['a']=="preset") {
		if(!empty($_POST['presetN']) && !empty($_POST['presetU'])) {
			$retour = $ojnAPI->getApiString("bunny/".$_SESSION['bunny']."/webradio/addpreset?name=".urlencode($_POST['presetN'])."&url=".urlencode($_POST['presetU'])."&".$ojnAPI->getToken());
			$_SESSION['message'] = isset($retour['ok']) ? $retour['ok'] : "Error : ".$retour['error'];
			header("Location: bunny_plugin.php?p=webradio");
		}
	}
	else if($_POST['a']=="webcast") { 
		if(!empty($_POST['webcastT']) && !empty($_POST['webcastP'])) {
			$retour = $ojnAPI->getApiString("bunny/".$_SESSION['bunny']."/webradio/addwebcast?name=".urlencode($_POST['webcastP'])."&time=".$_POST['webcastT']."&".$ojnAPI->getToken());
			$_SESSION['message'] = isset($retour['ok']) ? $retour['ok'] : "Error : ".$retour['error'];
			header("Location: bunny_plugin.php?p=webradio");
		}
	}
	else if($_POST['a']=="play") { 
		if(!empty($_POST['playN'])) {
			$retour = $ojnAPI->getApiString("bunny/".$_SESSION['bunny']."/webradio/play?name=".urlencode($_POST['playN'])."&".$ojnAPI->getToken());
			$_SESSION['message'] = isset($retour['ok']) ? $retour['ok'] : "Error : ".$retour['error'];
			header("Location: bunny_plugin.php?p=webradio");
		}
	}
	else if($_POST['a']=="playurl") { 
		if(!empty($_POST['playU'])) {
			$retour = $ojnAPI->getApiString("bunny/".$_SESSION['bunny']."/webradio/playurl?url=".$_POST['playU']."&".$ojnAPI->getToken());
			$_SESSION['message'] = isset($retour['ok']) ? $retour['ok'] : "Error : ".$retour['error'];
			header("Location: bunny_plugin.php?p=webradio");
		}
	} else if($_POST['a'] == "rfidplay") {
		if(!empty($_POST['PresetN_Rfp']) && !empty($_POST['Tag_Rfp']) && isset($Ztamps[$_POST['Tag_Rfp']])) {
			$retour = $ojnAPI->getApiString("bunny/".$_SESSION['bunny']."/webradio/addrfid?tag=".$_POST['Tag_Rfp']."&name=".urlencode($_POST['PresetN_Rfp'])."&".$ojnAPI->getToken());
			$_SESSION['message'] = isset($retour['ok']) ? $retour['ok'] : "Error : ".$retour['error'];
			header("Location: bunny_plugin.php?p=webradio");
		} 
	} else if($_POST['a'] == "rfidd") {
		if(!empty($_POST['Tag_Rfd']) && isset($Ztamps[$_POST['Tag_Rfd']])) {
			$retour = $ojnAPI->getApiString("bunny/".$_SESSION['bunny']."/webradio/removerfid?tag=".$_POST['Tag_Rfd']."&".$ojnAPI->getToken());
			$_SESSION['message'] = isset($retour['ok']) ? $retour['ok'] : "Error : ".$retour['error'];
			header("Location: bunny_plugin.php?p=webradio");
		}
	}
}
else if(!empty($_GET['rp'])) {
	$retour = $ojnAPI->getApiString("bunny/".$_SESSION['bunny']."/webradio/removepreset?name=".urlencode($_GET['rp'])."&".$ojnAPI->getToken());
	$_SESSION['message'] = isset($retour['ok']) ? $retour['ok'] : "Error : ".$retour['error'];
	header("Location: bunny_plugin.php?p=webradio");
}
else if(!empty($_GET['d'])) {
	$retour = $ojnAPI->getApiString("bunny/".$_SESSION['bunny']."/webradio/setdefault?name=".urlencode($_GET['d'])."&".$ojnAPI->getToken());
	$_SESSION['message'] = isset($retour['ok']) ? $retour['ok'] : "Error : ".$retour['error'];
	header("Location: bunny_plugin.php?p=webradio");
}
else if(!empty($_GET['rw'])) {
	$retour = $ojnAPI->getApiString("bunny/".$_SESSION['bunny']."/webradio/removewebcast?time=".$_GET['rw']."&".$ojnAPI->getToken());
	$_SESSION['message'] = isset($retour['ok']) ? $retour['ok'] : "Error : ".$retour['error'];
	header("Location: bunny_plugin.php?p=webradio");
} 
$default = $ojnAPI->getApiString("bunny/".$_SESSION['bunny']."/webradio/getdefault?".$ojnAPI->getToken());
$default = isset($default['value']) ? (string)($default['value']) : '';
$pList = $ojnAPI->getApiString("bunny/".$_SESSION['bunny']."/webradio/listpreset?".$ojnAPI->getToken());
$wList = $ojnAPI->getApiString("bunny/".$_SESSION['bunny']."/webradio/listwebcast?".$ojnAPI->getToken());
?>
<form method="post">
<fieldset>
	<legend>Actions</legend>
	<span class="fieldName">Play a preset <input type="radio" name="a" value="play" /></span>Preset: 
	<select name="playN">
		<option value=""></option>
	<?php foreach($pList['list']->item as $item) { ?>
		<option value="<?php echo urldecode(preg_replace("|^OJN_|", "", $item->key)) ?>"><?php echo urldecode(preg_replace("|^OJN_|", "", $item->key)); ?></option>
	<?php } ?>
	</select><br />

	<hr />
	<span class="fieldName">Play an url <input type="radio" name="a" value="playurl" /></span>Url: <input type="text" name="playU" size="60"><br />
	
	<hr />
	<span class="fieldName">Add a preset <input type="radio" name="a" value="preset" /></span>Preset: <input type="text" name="presetN"> &nbsp;&nbsp;&nbsp; for url: <input type="text" name="presetU" size="36"><br />
	
	<hr />
	<span class="fieldName">Add a webcast <input type="radio" name="a" value="webcast" /></span>at: <input type="text" name="webcastT" maxlength="5" style="width:50px" /> <i>(hh:mm)</i> &nbsp;&nbsp;&nbsp; for preset 
	<select name="webcastP">
		<option value=""></option>
	<?php foreach($pList['list']->item as $item) { ?>
		<option value="<?php echo urldecode($item->key) ?>"><?php echo urldecode($item->key); ?></option>
	<?php } ?>
	</select><br />

	<hr />
	<span class="fieldName">Play preset <input type="radio" name="a" value="rfidplay" /></span>Preset: 
	<select name="PresetN_Rfp">
		<option value=""></option>
	<?php foreach($pList['list']->item as $item) { ?>
		<option value="<?php echo urldecode(preg_replace("|^OJN_|", "", $item->key)) ?>"><?php echo urldecode(preg_replace("|^OJN_|", "", $item->key)); ?></option>
	<?php } ?>
</select>
 	&nbsp;&nbsp; on Ztamp: 
	<select name="Tag_Rfp">
    	<option value=""></option>
<?php foreach($Ztamps as $k=>$v): ?>
		<option value="<?php echo $k; ?>"><?php echo $v; ?> (<?php echo $k; ?>)</option>
<?php endforeach; ?>
	</select><br />

	<hr />
	<span class="fieldName">Delete Ztamp association <input type="radio" name="a" value="rfidd" /></span>Ztamp: 
	<select name="Tag_Rfd">
    	<option value=""></option>
<?php foreach($Ztamps as $k=>$v): ?>
		<option value="<?php echo $k; ?>"><?php echo $v; ?> (<?php echo $k; ?>)</option>
<?php endforeach; ?>
	</select><br />

	<hr />
	<input type="submit" value="Enregister">
</fieldset>
</form>

<?php
if(isset($pList['list']->item)) {
?>

<hr />
<table class="tablePlugins" cellspacing=0>
	<tr>
		<th colspan="4">Presets</th>
	</tr>
	<tr>
		<th>Name</th>
		<th>Url</th>
		<th colspan="2">Actions</th>
	</tr>
<?php
	$i = 0;
	foreach($pList['list']->item as $item) {
?>
	<tr<?php echo $i++ % 2 ? " class='l2'" : "" ?>>
<?php if(preg_match("|^OJN_|", $item->key)) { ?>
		<td><?php echo urldecode(preg_replace("|^OJN_|", "", $item->key)) ?></td>
		<td><?php echo $item->value ?></td>
		<td width="15%">&nbsp;</td>
		<td width="15%"><?php if("OJN_".$default != $item->key) { ?><a href="bunny_plugin.php?p=webradio&d=<?php echo urlencode(preg_replace("|^OJN_|", "", $item->key)) ?>">Set as default</a><?php } else { ?>Default preset<?php } ?></td>
<?php } else { ?>
		<td><?php echo urldecode($item->key) ?></td>
		<td><?php echo $item->value ?></td>
		<td width="15%" class="remove"><a href="bunny_plugin.php?p=webradio&rp=<?php echo $item->key ?>">Remove</a></td>
		<td width="15%" class="config"><?php if($default != $item->key) { ?><a href="bunny_plugin.php?p=webradio&d=<?php echo $item->key ?>">Set as default</a><?php } else { ?>Default preset<?php } ?></td>
	</tr>
<?php } ?>
</table>
<?php
}
if(isset($wList['list']->item)){
?>

<hr />
<table class="tablePlugins" cellspacing=0>
	<tr>
		<th colspan="3">Webcast</th>
	</tr>
	<tr>
		<th>Time</th>
		<th>Name</th>
		<th>Actions</th>
	</tr>
<?php
	$i = 0;
	foreach($wList['list']->item as $item) {
?>
	<tr<?php echo $i++ % 2 ? " class='l2'" : "" ?>>
		<td><?php echo urldecode($item->key) ?></td>
		<td><?php echo $item->value ?></td>
		<td width="15%" class="remove"><a href="bunny_plugin.php?p=webradio&rw=<?php echo $item->key ?>">Remove</a></td>
	</tr>
<?php  } ?>
</table>
<?php } ?>
