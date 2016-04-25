<?php
/*

Basic Display Class

*/

class ojnTemplate {
	private $titre_alt	= "";
	private $titre	= "openJabNab";
	private $soustitre	= "Configuration";
	private $Api;
	private $UInfos;

	// ### CONSTRUCT ##############################################################################################
	public function __construct(&$api) {
		$this->Api=$api;
	}

	// ### PUBLIC METHODS #########################################################################################

	// ------------------------------------------------------------------------------------------------------------
	public function setUInfos($v) {
		$this->UInfos = $v;
	}

	// ------------------------------------------------------------------------------------------------------------
	public function display($buffer) {
		$template = file_get_contents(ROOT_SITE.'class/template.tpl.php');
		$Stats = $this->Api->getStats(false);

		$pattern = array(
				"|<!!TITLE!!>|",
				"|<!!ALTTITLE!!>|",
				"|<!!SUBTITLE!!>|",
				"|<!!CONTENT!!>|",
				"|<!!LAPINS!!>|",
				"|<!!PLUGINS!!>|",
				"|<!!ZTAMPS!!>|",
				"|<!!MENU!!>|",
				"|<!!BUNNIES!!>|",
			);
		$replace = array(
				$this->titre,
				$this->titre_alt,
				$this->soustitre,
				$buffer,
				$Stats['connected_bunnies']."/".$Stats['bunnies'],
				$Stats['enabled_plugins']."/".$Stats['plugins'],
				$Stats['ztamps'],
				$this->makeMenu(),
				$this->makeBunnyMenu()
			);

		$template = preg_replace($pattern, $replace, $template);
		if(preg_match("|<body>(.*)</body>|s", $template, $match)){
			$body = $this->translate($match[1]);
			$template = preg_replace("|<body>(.*)</body>|s", $body, $template);
		}
		return $template;
	}

	// ### PRIVATES METHODS #########################################################################################
	// ------------------------------------------------------------------------------------------------------------
	private function translate($page){
		global $translations;
		return preg_replace(array_map('toRegex', array_keys($translations)), $translations, $page);
	}

	// ------------------------------------------------------------------------------------------------------------
	private function makeMenu($current_url='') {
		$menu = '<a href="index.php">Accueil</a>';
		$menus['Accueil']='index.php';
		if(isset($_SESSION['token']))	{
			$menus['Compte']='account.php';
			$menus['Lapin']	='bunny.php';
			$menus['Ztamps']='ztamp.php';
			if($this->UInfos['isAdmin']) {
				$menus['Serveur']	='server.php';
				$menus['Raw API call']	='api.php';
			}
			$menus['Logout ('.$this->UInfos['username'].')']='index.php?logout';
		}

		$i=count($menus);
		$separator=' | '; 	//old menu
		$separator=''; 		//removed
		$current_url or $current_url=basename($_SERVER['REQUEST_URI']);
		$current_url = preg_replace('#^$#','index.php',$current_url);
		foreach ($menus as $name => $url) {
			if($url==$current_url){$class="selected";}else{$class="";}
			$html .="<a href='$url' class='$class'>$name</a>";
			$i--;
			$i and $html .=$separator;
		}
		return $html;
	}

	// ------------------------------------------------------------------------------------------------------------
	private function makeBunnyMenu()	{
		$menu = "";
		if($this->UInfos['token'] != '') {
			$online = $this->Api->getListOfConnectedBunnies(false);
			$bunnies = $this->Api->getListOfBunnies(false);
			if(!empty($bunnies)){
				asort($bunnies);
				foreach($bunnies as $mac => $bunny){
					if($mac == $_SESSION['bunny']){$class="selected";}else{$class="";}
					$menu .= "<li class=\"$class".(isset($online[$mac]) ? ' online' : '').'"><span><a href="bunny.php?b='.$mac.'" alt="'.$mac.'" title="'.$mac.'">'.($bunny != "Bunny" ? $bunny : $mac).'</a></span></li>';
				}
			}
			$menu or $menu = '<li class="none">No Bunny !</li>';			
		}
		return $menu;
	}

}

// ### GLOBAL FUNCTIONS #########################################################################################

// ------------------------------------------------------------------------------------------------------------
function toRegex($str) {
	return "|".$str."|";
}
?>
