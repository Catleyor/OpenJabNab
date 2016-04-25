<?php 
if(!empty($_POST['a']) && !empty($_POST['airqualitytime']) && !empty($_POST['airqualitycity']) ) {
	if($_POST['a']=="addwbc")
		$retour = $ojnAPI->getApiString("bunny/".$_SESSION['bunny']."/airquality/addwebcast?time=".$_POST['airqualitytime']."&city=".urlencode($_POST['airqualitycity'])."&".$ojnAPI->getToken());
	else
		$retour['error'] = "Incorrect Parameters";
	$_SESSION['message'] = isset($retour['ok']) ? $retour['ok'] : "Error : ".$retour['error'];
	header("Location: bunny_plugin.php?p=airquality");
}
else if(!empty($_GET['rmwbc'])) {
	$retour = $ojnAPI->getApiString("bunny/".$_SESSION['bunny']."/airquality/removewebcast?time=".$_GET['rmwbc']."&".$ojnAPI->getToken());
	$_SESSION['message'] = isset($retour['ok']) ? $retour['ok'] : "Error : ".$retour['error'];
	header("Location: bunny_plugin.php?p=airquality");
} 
?>
<form method="post">
<fieldset>
	<legend>Actions</legend>
	<span class="fieldName">Webcast</span><input type="radio" name="a" value="addwbc" checked="true" /> Add<br />
	<span class="fieldName"></span><input type="radio" name="a" value="rmwbc" /> Remove<br /><br />

	<span class="fieldName">Time</span><input type="text" name="airqualitytime" /><br />
	<span class="fieldName">City</span><select name="airqualitycity">
<?php
$cities = $ojnAPI->getApiList("bunny/".$_SESSION['bunny']."/airquality/getcitieslist?".$ojnAPI->getToken());
sort($cities);
foreach($cities as $city)
{
?>
<option value="<?php echo $city ?>"><?php echo $city ?></option>
<?php
}
?>
</select>
<input type="submit" value="Enregister">

<?php
$webcasts = $ojnAPI->getApiList("bunny/".$_SESSION['bunny']."/airquality/getwebcastslist?".$ojnAPI->getToken());
if($webcasts){
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
	foreach($webcasts as $item) {
?>
	<tr<?php echo $i++ % 2 ? " class='l2'" : "" ?>>
		<td><?php echo urldecode($item->key) ?></td>
		<td><?php echo $item->value ?></td>
		<td width="15%" class="remove"><a href="bunny_plugin.php?p=airquality&rmwbc=<?php echo $item->key ?>">Remove</a></td>
	</tr>
<?php  } ?>
	</table>

<?php } ?>

</fieldset>
</form>
