<?php
require_once "include/common.php";
if(!isset($_SESSION['token']) || !$Infos['isAdmin'])
	header('Location: index.php');
?>
<div id="pageApi">

	<h1>Raw API Call</h1>
	<p>DEVELOPMENT Feature: On this page, you can make a direct API call, your token will be added automatically</p>
	<br />
	<form method="post">
		<input type="text" style="width:80%" name="r" value="<?php echo !empty($_POST['r']) ? $_POST['r'] : '' ?>"/>
		<input type="submit" value="Go !" />
	</form>

<pre>
<?php

function printr($a,$l=0) {
	if(is_array($a))
		foreach($a as $b=>$sa) {
			if(count($sa) < 2) {
				if(!is_array($sa))
					echo str_repeat('    ',$l).$b." => ".$sa."\n";
				else if(!is_numeric($b))
					echo str_repeat('    ',$l).$b."\n";
			}else
				echo str_repeat('    ',$l).$b."\n";
				printr($sa,$l+1);
		}
}

if(!empty($_POST['r'])) {
	$r = $ojnAPI->getApiXMLArray($_POST['r'].(strstr($_POST['r'],'?') ? '&': '?').$ojnAPI->getToken());
	printr($r);
} else
	echo "Make your request.";
?>
</pre>

</div>

<?php
require_once "include/append.php"
?>