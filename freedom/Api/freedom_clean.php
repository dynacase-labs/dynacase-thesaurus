<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_clean.php,v 1.4 2003/08/18 15:47:04 eric Exp $
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


global $HTTP_SERVER_VARS;
$dir=dirname($HTTP_SERVER_VARS["argv"][0]);


system("psql freedom anakeen -f ".$dir."/API/freedom_clean.sql"); 

?>