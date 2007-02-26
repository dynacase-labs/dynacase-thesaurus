<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_groups.php,v 1.13 2007/02/26 14:10:05 eric Exp $
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

$doc = new_Doc($dbaccess);
$dbfree=php2DbSql($dbaccess);
$dbankcoord=php2DbSql(getDbAccess(),false);
$dbankname=getDbName(getDbAccess());

$big=false; // need to set to true when table count > 20000
if ($big) system("echo 'DROP INDEX groups_idx2;DROP INDEX groups_idx1;' | psql $dbfree");

system("echo 'delete from groups;delete from docperm where upacl=0 and unacl=0;update docperm set cacl=0 where cacl != 0;' | psql $dbfree");
system("pg_dump -a --disable-triggers -t groups $dbankcoord $dbankname | psql $dbfree");

if ($big) system("echo 'CREATE unique INDEX groups_idx2 on groups(iduser,idgroup);CREATE INDEX groups_idx1 on  groups(iduser);' | psql $dbfree");

//system("echo 'vacuum  docperm;vacuum  groups' | psql $dbfree");
//system("echo 'select getuperm(userid, docid) from docperm' | psql freedom anakeen");



?>