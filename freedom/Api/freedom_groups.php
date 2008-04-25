<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_groups.php,v 1.14 2008/04/25 09:18:15 jerome Exp $
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

$pgservice_core = getServiceCoreFromEnv();
$pgservice_freedom = getServiceFreedomFromEnv();

$big=false; // need to set to true when table count > 20000

if ($big) system("PGSERVICE=\"$pgservice_freedom\" psql -c 'DROP INDEX groups_idx2;DROP INDEX groups_idx1;'");

system("PGSERVICE=\"$pgservice_freedom\" psql -c 'delete from groups;delete from docperm where upacl=0 and unacl=0;update docperm set cacl=0 where cacl != 0;'");

system("PGSERVICE=\"$pgservice_core\" pg_dump -a --disable-triggers -t groups | PGSERVICE=\"$pgservice_freedom\" psql");

if ($big) system("PGSERVICE=\"$pgservice_core\" psql 'CREATE unique INDEX groups_idx2 on groups(iduser,idgroup);CREATE INDEX groups_idx1 on  groups(iduser);' | PGSERVICE=\"freedom\" psql");

//system("echo 'vacuum  docperm;vacuum  groups' | psql $dbfree");
//system("echo 'select getuperm(userid, docid) from docperm' | psql freedom anakeen");

?>