<?php session_start();?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<script type="text/javascript" src="../js/jquery.js"></script>
	<script type="text/javascript" src="../js/jquery.ui.js"></script>
	<script type="text/javascript" src="../js/jquery.ui.touch-punch.js"></script>

	<link rel="stylesheet" href="../css/jquery.ui.css" />
	<link rel="stylesheet" href="../css/generic.css" />
	<link rel="stylesheet" href="xdms.css" />

	<script type="text/javascript" src="../js/generic.js"></script>

	<script>
		var borderSetA = {
			"border-top": "5px solid white",
			"border-bottom": "5px solid white"
		}
		var borderSetB = {
			"border-left": "5px solid white",
			"border-right": "5px solid white"
		}

		$(document).ready(function () {
			nv.widgetNew("#UserBoxOpen", "click", "#UserBox", { width: $("#UserBoxOpen").parent().outerWidth(), widgetStyle: borderSetB });
			nv.widgetNew("#ProjectsBoxOpen", "click", "#ProjectsBox", { width: $("#ProjectsBoxOpen").parent().outerWidth(), widgetStyle: borderSetB });
		});
	</script>

	<title>nVentis ℠ - xDMS ™</title>
</head>

<body class="MaxSpace" style="min-width:1024px;">
	<div id="Header" class="uiElement withHover">
		<div id="nVLogo"></div>
		<div style="width:80px"><a href="Dev/">Dateien</a></div>
		<div style="width:100px">
			<a class="nvWidgetTrigger" id="ProjectsBoxOpen">Projekte</a>
		</div>
		<div style="width:100px"><a href="About.htm">Kontakte</a></div>
		<div style="width:auto; border:none; box-sizing:border-box; display:table; height:64px">
			<div class="noHover" style="display:table-cell; border:0; margin-left:5px; width:100%"><input class="Metro Darken" style="height:32px; width:100%; border:1px solid white; box-sizing:border-box; padding-left:5px; padding-right:5px" type="text" /></div>
			<div class="noHover" style="display:table-cell; border:0; margin-right:5px; width:100px"><button class="Metro Darken" style="height:32px; width:100px; font-size:17px; letter-spacing:0.5px; font-weight:600; border:1px solid white; box-sizing:border-box;">Suchen</button></div>
		</div>
		<div style="width:150px">
			<div class="nvWidgetTrigger" id="UserBoxOpen"><?php if(isset($_SESSION["username"])) echo $_SESSION["username"]; else echo "Benutzer"; ?></div>
		</div>
	</div>

	<div id="ProjectsBox" class="nvWidget">
		<a href="#">Durchsuchen</a>
		<a href="#">Erstellen</a>
	</div>

	<div id="UserBox" class="nvWidget">
		<?php if(isset($_SESSION["username"])) echo "<div>Profil</div><div>Ausloggen</div>"; else echo "<a href='../login.php'><div>Einloggen</div></a><a href='../register.php'><div>Registrieren</div></a>"; ?>
	</div>
	
	<div id="Content" style="position:absolute; top:70px; left:70px; bottom:0; right:0; padding:10px">
		
	</div>
</body>
</html>