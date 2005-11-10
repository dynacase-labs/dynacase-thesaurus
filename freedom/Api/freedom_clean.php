<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_clean.php,v 1.7 2005/11/10 15:57:20 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



// remove all tempory doc and orphelines values
include_once("FDL/Class.Doc.php");


$appl = new Application();
$appl->Set("FDL",	   $core);


$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}


global $_SERVER;
$dir=dirname($_SERVER["argv"][0]);
$real=(getHttpVars("real")=="yes");

$dbfree=php2DbSql($dbaccess);
if ($real) system("psql $dbfree -f ".$dir."/API/freedom_realclean.sql"); 
else system("psql $dbfree -f ".$dir."/API/freedom_clean.sql"); 


?>
