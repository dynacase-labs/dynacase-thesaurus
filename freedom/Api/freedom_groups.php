<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_groups.php,v 1.4 2004/03/01 09:04:17 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



// refreah for a classname
// use this only if you have changed title attributes

include_once("FDL/Class.Doc.php");



$appl = new Application();
$appl->Set("FDL",	   $core);


$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}

$doc = new Doc($dbaccess);

system("echo 'delete from groups;delete from docperm where upacl=0 and unacl=0;update docperm set cacl=0;' | psql freedom anakeen");
system("pg_dump -a -t groups anakeen -U anakeen | psql freedom anakeen");
//system("echo 'select getuperm(userid, docid) from docperm' | psql freedom anakeen");



?>