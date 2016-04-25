<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr-FR">
	<head>
		<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
		<meta name="author" content="openJabNab" />
		<meta name="generator" content="openJabNab" />
		<link rel="icon" href="favicon.ico" />
		<link rel="top" href="./index.php" title="Homepage" />
		<link rel="stylesheet" type="text/css" href="css/openjabnab.css" media="screen" title="openJabNab" />
		<title><!!TITLE!!></title>
	</head>

	 <body>
		<!-- Header ############################################################################################################################################# -->
		<div id="wrapper">

			<div id="header">
				<div id="headerBg">

					<div class="superHeader">
						<span>Direct links:</span>
						<a href="http://ojnwiki.psnet.fr/" title="openJabNab Wiki">openJabNab Wiki</a> |
						<a href="http://freenabaztag.com/" title="Nabaztags directory">openJabNab directory</a>
					</div>

					<div class="midHeader">
						<h1 class="headerTitle" alt="<!!ALTTITLE!!>" title="<!!ALTTITLE!!>"><img src="/ojn_admin/images/logo.png"></h1>
						<div class="headerSubTitle"><!!SUBTITLE!!></div>
						<div class="headerLinks"></div>
					</div>

					<div class="subHeader">
						<span class="doNotDisplay">Navigation:</span>
							<!!MENU!!>
					</div>

				</div>
			</div>

			<!-- MAIN:  Liquid Layout ################################################################################################################################ -->
			<!--  http://matthewjamestaylor.com/blog/ultimate-2-column-left-menu-pixels.htm -->
			
			<div class="colmask leftmenu" id="main">
			    <div class="colright">
			        <div class="col1wrap">
			            <div class="col1">
							<!-- Content ############################################################################################################################# -->
							<div id="content">
								<!!CONTENT!!>
							</div>
            			</div>
        			</div>
        			<div class="col2">
						<!-- SIDE ############################################################################################################################# -->
						<ul id="sidebar">
							<!!BUNNIES!!>
						</ul>
					</div>
				</div>
			</div>


			<!-- Footer ############################################################################################################################################# -->
			<div id="footer">
				<span class="doNotPrint">
					<span>Bunnies: <strong><!!LAPINS!!></strong></span>
					<span>Ztamps: <strong><!!ZTAMPS!!></strong></span>
					<span>Plugins: <strong><!!PLUGINS!!></strong></span>
				</span>
			</div>
		</div>
	</body>
</html>
