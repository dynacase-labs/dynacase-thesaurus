<html>
<head><style type="text/css"><!--
body {background-color: #ffffff; color: #000000;}
body, table, td, th, h1, h2 {border:0px; font-family: sans-serif;}
//--></style>
</head>
<body>
<?php
global $SERVER_NAME;
global $SERVER_PORT;
global $SERVER_ADDR;
global $REQUEST_URI;
include_once("Lib.WgcalSync.php");
$action = @WSyncAuthent();
$version = $action->GetParam("WGCAL_SYNCVERSION","0");
?>
<table style="border:2px inset green;">
<tr><th colspan="3">Mire de controle de l'outil de synchronisation Outlook/Agenda de groupe</th></tr>
<tr><td align="right">Serveur </td><td>:</td><td> <?php echo $SERVER_NAME." (".$SERVER_ADDR.")"; ?></td></tr>
<tr><td align="right">Client </td><td>:</td><td> <?php global $REMOTE_ADDR; echo $REMOTE_ADDR; ?></td></tr>
<tr><td align="right">Utilisateur </td><td>:</td> <td><?php global $PHP_AUTH_USER; global $PHP_AUTH_PW; $pass=($PHP_AUTH_PW==""?"[none]":"***********"); echo $PHP_AUTH_USER." / ".$pass;?></td></tr>
<tr><td align="right">Version </td><td>:</td><td> <?php echo $version; ?></td></tr></table>
<br><br>
<table style="border:2px inset green;">
<tr><th colspan="3">Configuration de votre synchroniseur</th></tr>
<tr><td align="right">Serveur</td><td>:</td><td> <?php echo $SERVER_NAME; ?></td></tr>
<tr><td align="right">Utilisateur</td><td>:</td><td> <?php global $PHP_AUTH_USER; echo $PHP_AUTH_USER; ?></td></tr>
<tr><td align="right">Mot de passe</td><td>:</td><td>**********</td></tr>
<tr><td align="right">Période</td><td>:</td><td>Nombre de mois synchronisé avant la date du jour</td></tr>


</table>
</html>
