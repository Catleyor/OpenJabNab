<?php 
$Ztamps = $ojnAPI->GetListofZtamps(false);
$FList = $ojnAPI->getApiList("bunny/".$_SESSION['bunny']."/wizzflux/listflux?".$ojnAPI->getToken());
if(!empty($_POST['a'])) {
    if($_POST['a']=="webcast") { 
		if(!empty($_POST['webcastT']) && !empty($_POST['webcastP'])) {
			$retour = $ojnAPI->getApiString("bunny/".$_SESSION['bunny']."/wizzflux/addwebcast?name=".urlencode($_POST['webcastP'])."&time=".$_POST['webcastT']."&".$ojnAPI->getToken());
			$_SESSION['message'] = isset($retour['ok']) ? $retour['ok'] : "Error : ".$retour['error'];
		}
	}
	else if($_POST['a']=="play") { 
		if(!empty($_POST['playN'])) {
			$retour = $ojnAPI->getApiString("bunny/".$_SESSION['bunny']."/wizzflux/play?name=".urlencode($_POST['playN'])."&".$ojnAPI->getToken());
			$_SESSION['message'] = isset($retour['ok']) ? $retour['ok'] : "Error : ".$retour['error'];
		}
	}
    else if($_POST['a'] == "rfidplay") {
		if(!empty($_POST['PresetN_Rfp']) && !empty($_POST['Tag_Rfp']) && isset($Ztamps[$_POST['Tag_Rfp']])) {
			$retour = $ojnAPI->getApiString("bunny/".$_SESSION['bunny']."/wizzflux/addrfid?tag=".$_POST['Tag_Rfp']."&name=".urlencode($_POST['PresetN_Rfp'])."&".$ojnAPI->getToken());
			$_SESSION['message'] = isset($retour['ok']) ? $retour['ok'] : "Error : ".$retour['error'];
		} 
	} else if($_POST['a'] == "rfidd") {
		if(!empty($_POST['Tag_Rfd']) && isset($Ztamps[$_POST['Tag_Rfd']])) {
			$retour = $ojnAPI->getApiString("bunny/".$_SESSION['bunny']."/wizzflux/removerfid?tag=".$_POST['Tag_Rfd']."&".$ojnAPI->getToken());
			$_SESSION['message'] = isset($retour['ok']) ? $retour['ok'] : "Error : ".$retour['error'];
		}
	} else if($_POST['a'] == "default") {
        if(!empty($_POST['d'])) {
            $retour = $ojnAPI->getApiString("bunny/".$_SESSION['bunny']."/wizzflux/setdefault?name=".urlencode($_POST['d'])."&".$ojnAPI->getToken());
            $_SESSION['message'] = isset($retour['ok']) ? $retour['ok'] : "Error : ".$retour['error'];
        }
    }
    header("Location: bunny_plugin.php?p=wizzflux");
}
else if(!empty($_GET['rw'])) {
	$retour = $ojnAPI->getApiString("bunny/".$_SESSION['bunny']."/wizzflux/removewebcast?time=".$_GET['rw']."&".$ojnAPI->getToken());
	$_SESSION['message'] = isset($retour['ok']) ? $retour['ok'] : "Error : ".$retour['error'];
	header("Location: bunny_plugin.php?p=wizzflux");
} 
$default = $ojnAPI->getApiString("bunny/".$_SESSION['bunny']."/wizzflux/getdefault?".$ojnAPI->getToken());
$default = isset($default['value']) ? (string)($default['value']) : '';
$wList = $ojnAPI->getApiString("bunny/".$_SESSION['bunny']."/wizzflux/listwebcast?".$ojnAPI->getToken());
?>

<form method="post">
<fieldset>
	<legend>Actions</legend>

	<span class="fieldName">Play <input type="radio" name="a" value="play" /></span>Flux: 
	<select name="playN">
		<option value=""></option>
<?php if(!empty($FList))
	foreach($FList as $item) { ?>
		<option value="<?php echo $item; ?>"><?php echo ucfirst($item); ?></option>
<?php } ?>
	</select><br />

	<hr />
	<span class="fieldName">Set default flux <input type="radio" name="a" value="default" /></span>Flux: 
		<select name="d">
<?php if(!empty($FList))
	foreach($FList as $item) { ?>
			<option value="<?php echo $item; ?>" <?php echo $default == $item ? 'selected="selected"' : ''; ?>><?php echo ucfirst($item); ?></option>
<?php } ?>
		</select><br />

	<hr />
	<span class="fieldName">Add a webcast <input type="radio" name="a" value="webcast" /></span>at: <input type="text" name="webcastT" maxlength="5" style="width:50px" /> <i>(hh:mm)</i> &nbsp;&nbsp;&nbsp; for flux <select name="webcastP">
	<option value=""></option>
<?php if(!empty($FList))
        foreach($FList as $item) { ?>
		<option value="<?php echo $item; ?>"><?php echo ucfirst($item); ?></option>
	<?php } ?>
</select><br />

	<hr />
	<span class="fieldName">Play flux <input type="radio" name="a" value="rfidplay" /></span>Flux: 
	<select name="PresetN_Rfp">
		<option value=""></option>
<?php if(!empty($FList))
	foreach($FList as $item) { ?>
		<option value="<?php echo $item; ?>"><?php echo ucfirst($item); ?></option>
<?php } ?>
	</select> 
 	&nbsp;&nbsp;&nbsp;<br /> 
	<span class="fieldName"></span>on Ztamp: 
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
		<td width="15%" class="remove"><a href="bunny_plugin.php?p=wizzflux&rw=<?php echo $item->key ?>">Remove</a></td>
	</tr>
<?php  } ?>
</table>
<?php } ?>
