<?php
/**
 * Reinit vault files
 *
 * @author Anakeen 2004
 * @version $Id: DocRelInit.php,v 1.1 2007/06/29 18:46:21 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
ini_set("max_execution_time", "36000");


include_once('FDL/Class.Doc.php');
include_once('FDL/Class.DocFam.php');
include_once('FDL/Class.DocVaultIndex.php');
include_once('VAULT/Class.VaultFile.php');


$dbaccess=GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}
$o=new DbObj($dbaccess);
$q=new QueryDb($dbaccess,"DocAttr");
$q->AddQuery("type = 'docid'");
//$q->AddQuery("frameid not in (select id from docattr where type~'array')");
$la=$q->Query(0,0,"TABLE");
if ($q->nb > 0) {
  $o->exec_query("delete from docrel");
 }




foreach ($la as $k=>$v) {
  $docid=$v["docid"];
  $aid=$v["id"];

  $sql="insert into docrel (cinitid, sinitid, type ) (SELECT id, {$aid}::int, '$aid' from doc$docid where $aid ~ '^[0-9]+$');" ;
  $o->exec_query($sql);
  // print "$sql\n";
  
  $sql2="SELECT docrelreindex(id, $aid,'$aid') from doc$docid where $aid is not null and $aid ~ '^[^\n]*[0-9]\n.*[0-9]$';" ;
   $o->exec_query($sql2);
    print "$sql2\n";
  
  
}
// Folders


$sql2="insert into docrel (cinitid, sinitid, type ) ( SELECT dirid, childid ,'folder' from fld where qtype='S')";
$o->exec_query($sql2);

print "stitle\n";
$sql="UPDATE docrel set stitle = (select title from docread where id=sinitid) where stitle is  null;";
  $o->exec_query($sql);
print "ctitle\n";
$sql="UPDATE docrel set ctitle = (select title from docread where id=cinitid) where ctitle is  null;";
  $o->exec_query($sql);
print "cicon\n";
$sql="UPDATE docrel set cicon = (select icon from docread where id=cinitid) where cicon is  null;";
  $o->exec_query($sql);
print "sicon\n";
$sql="UPDATE docrel set sicon = (select icon from docread where id=sinitid)  where sicon is  null;";
  $o->exec_query($sql);

?>
