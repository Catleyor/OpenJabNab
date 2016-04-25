<?php
define('ROOT_SITE', realpath(dirname(__FILE__)."/../")."/");
define('ROOT_WWW_ADMIN', 'http://<HOSTNAME>/ojn_admin/');
define('ROOT_WWW_API', 'http://<HOSTNAME>/ojn_api/');
define('ADMIN_EMAIL', '<EMAIL>');

// #############################################################################################################
$session_duration=45; //session duration (minutes)

// Please notice that if you use a linux system that use a cron job to clean the sessions files,
// you will have to change the 'session.cache_expire' directly in php.ini

//override the default expiration time (we are not a bank and 3min is a bit short)
ini_set('session.cache_expire', 60*$session_duration);
// we also have to set the cookie expiration to the browser session
ini_set('session.cookie_lifetime', 0);
//So we have to tell GarbageCollector to not delete the session before it expire
ini_set('session.gc_maxlifetime', 60*$session_duration + 15*60 );
// We're fine : Start the session
session_start('openJabNab');

require_once(ROOT_SITE.'class/api.class.php');
require_once(ROOT_SITE.'class/template.class.php');
$ojnAPI = new ojnApi();
$ojnTemplate = new ojnTemplate($ojnAPI);
$Infos = array('token' => '','login'=>'guest','usename'=>'Guest','isAdmin'=>false,'isValid'=>true);
if(isset($_SESSION['token']) && !strpos($_SERVER['REQUEST_URI'],"logout")) {
    if(isset($_SESSION['token']) && isset($_SESSION['login'])) {
        $Infos = $ojnAPI->getApiMapped('accounts/infos?user='.$_SESSION['login'].'&'.$ojnAPI->getToken());
		/* Fix to convert 'true' to true, etc */
		if(isset($Infos['isAdmin']))
			$Infos['isAdmin'] = $Infos['isAdmin'] != 'false' ? true : false;
		if(isset($Infos['isValid']))
			$Infos['isValid'] = $Infos['isValid'] != 'false' ? true : false;
	}
	if(!$Infos['isValid'])
		header('Location: index.php?logout');
}
$translations = array();
if(isset($Infos['language']))
{
	if(file_exists(ROOT_SITE.'class/translations.'.$Infos['language'].'.php'))
		require_once(ROOT_SITE.'class/translations.'.$Infos['language'].'.php');
}
$ojnTemplate->setUInfos($Infos);
ob_start(array($ojnTemplate,'display'));
?>