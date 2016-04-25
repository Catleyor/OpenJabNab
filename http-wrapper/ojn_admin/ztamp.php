<?php
require_once "include/common.php";
if(!isset($_SESSION['token']))
	header('Location: index.php');
if(isset($_GET['z']) && empty($_GET['z'])) {
	unset($_SESSION['ztamp']);
	unset($_SESSION['ztamp_name']);
	header("Location: ztamp.php");
}elseif(!empty($_GET['z'])) {
		$_SESSION['ztamp'] = $_GET['z'];
		$ztamps = $ojnAPI->getListOfZtamps(false);
		$_SESSION['ztamp_name'] = !empty($ztamps[$_GET['z']]) ? $ztamps[$_GET['z']] : '';
		header("Location: ztamp.php");
}elseif((!empty($_GET['plug']) && !empty($_GET['stat'])) || (!empty($_POST['plug']) && !empty($_POST['stat']))) {
	$a = !empty($_GET['stat']) ? $_GET : $_POST;
	$function = $a['stat'] == 'register' ? 'register' : 'unregister';
	$_SESSION['message'] = $ojnAPI->getApiString('ztamp/'.$_SESSION['ztamp'].'/'.$function.'Plugin?name='.$a['plug'].'&'.$ojnAPI->getToken());
	header('Location: ztamp.php');
} elseif(isset($_GET['resetown'])) {
	$_SESSION['message'] = $ojnAPI->getApiString("ztamp/".$_SESSION['ztamp']."/resetOwner?".$ojnAPI->getToken());
	header('Location: ztamp.php');
}	else if(!empty($_GET['ztamp_name'])) {
	$_SESSION['message'] = $ojnAPI->getApiString("ztamp/".$_SESSION['ztamp']."/setZtampName?name=".urlencode($_GET['ztamp_name'])."&".$ojnAPI->getToken());
	$_SESSION['ztamp_name'] = $_GET['ztamp_name'];
	header('Location: ztamp.php');
}

if(empty($_SESSION['ztamp'])) {
?>
<div id="pageZtamp" class="choices">	
<h1>Choix du ztamp &agrave; configurer</h1>
<ul>
<?php
	$ztamps = $ojnAPI->getListOfZtamps(false);
    if(!empty($ztamps))
	foreach($ztamps as $ztamp => $nom)	{
?>
	<li><b><?php echo $nom; ?></b> (<?php echo $ztamp; ?>) <a href="ztamp.php?z=<?php echo $ztamp; ?>" class="button">Choisir ce Ztamp</a></li>
<?php
	}
?>
</ul>
<?php
	$bunnies = $ojnAPI->getListOfBunnies(false);
    if(!empty($bunnies))
		foreach($bunnies as $mac => $nom)	{
			$lastZ = $ojnAPI->getApiString("plugin/rfid/getLastTagForBunny?sn=".$mac."&".$ojnAPI->getToken());
			if(!empty($lastZ['value'])) {
?>
<div class="lastZtamp">
	Dernier Ztamp utilis&eacute;  par <b><?php echo $nom; ?></b> (<?php echo $mac; ?>): <b><?php echo (!empty($ztamps) && isset($ztamps[$lastZ['value']])) ? $ztamps[$lastZ['value']]: ''; ?></b> (<?php echo $lastZ['value']; ?>)
</div>
<?php
		}
	}
} 
else {
?>
<div id="pageZtamp">	
<h1 id="ztamp">Configuration du ztamp '<?php echo !empty($_SESSION['ztamp_name']) ? $_SESSION['ztamp_name'] : $_SESSION['ztamp']; ?>'</h1>
<h2>Le ztamp</h2>

<fieldset>
	<legend>Configuration</legend>
	<form>
<?php
$plugins = $ojnAPI->getListOfPlugins(false);
$ztampPlugins = $ojnAPI->getListOfZtampEnabledPlugins(false);
$actifs = $ojnAPI->ztampListOfPlugins($_SESSION['ztamp'],false);
?>
	<span class="fieldName">Nom</span><input type="text" name="ztamp_name" value="<?php echo $_SESSION['ztamp_name']; ?>" size="35"> <input type="submit" value="Enregistrer">
	</form>
</fieldset>

<?php if($Infos['isAdmin']): ?>

<fieldset>
	<legend>Debug features</legend>
	<form method="get">
		<input name="resetown" type="submit" value="Liberer le ztamp de ce compte">
	</form>
</fieldset>

<?php endif; ?>

<h2>Plugins</h2>
<?php
if(isset($_SESSION['message']) && empty($_GET)) {
	if(isset($_SESSION['message']['ok'])) { ?>
	<div class="ok_msg">
	<?php	echo $_SESSION['message']['ok'];
	} else { ?>
	<div class="error_msg">
	<?php	echo $_SESSION['message']['error'];
	}
	if(empty($_GET))
		unset($_SESSION['message']);
	echo "</div>";
}
?>
	<table class="tablePlugins" cellspacing=0>
	<tr>
		<th>Plugin</th>
		<th colspan="2">Actions</th>
	</tr>
<?php
	$i = 0;
	foreach($ztampPlugins as $plugin){
?>
	<tr<?php echo $i++ % 2 ? " class='l2'" : "" ?>>
		<td><?php echo $plugins[$plugin]; ?></td>
		<td <?php echo in_array($plugin, $actifs) ? 'width="20%" class="remove"' : 'colspan="2" class="add"'; ?>><a href='ztamp.php?stat=<?php echo in_array($plugin, $actifs) ? "unregister" : "register"; ?>&plug=<?php echo $plugin; ?>'><?php echo in_array($plugin, $actifs) ? "D&eacute;sa" : "A"; ?>ctiver le plugin</a></td>
		<?php if(in_array($plugin, $actifs)): ?><td width="20%" class="config"><?php echo in_array($plugin, $actifs)?"<a href='ztamp_plugin.php?p=$plugin'>Configurer / Utiliser</a>":""?></td><?php endif; ?>
	</tr>
<?php } ?>
	</table>
<?php }
?>
</div>
<?php
require_once "include/append.php";
?>