<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_groups.php,v 1.6 2004/08/05 09:47:20 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



// refreah for a classname
// use this only if you have changed title attributes

include_once("FDL/Class.Doc.php");
include_once("Lib.Common.php");



$appl = new Application();
$appl->Set("FDL",	   $core);


$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}

$doc = new Doc($dbaccess);
$dbname=getDbName($dbaccess);
$dbuser=getDbUser($dbaccess);
$dbank=getDbName(getDbAccess());

system("echo 'delete from groups;delete from docperm where upacl=0 and unacl=0;update docperm set cacl=0 where cacl != 0;; ' | psql $dbname $dbuser");
system("pg_dump -a --disable-triggers -t groups $dbank -U $dbuser | psql $dbname $dbuser");
//system("echo 'select getuperm(userid, docid) from docperm' | psql freedom anakeen");



?>