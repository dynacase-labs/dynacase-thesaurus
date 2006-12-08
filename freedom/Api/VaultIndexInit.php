<?php
/**
 * Examine vault files
 *
 * @author Anakeen 2004
 * @version $Id: VaultIndexInit.php,v 1.1 2006/12/08 17:51:28 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
ini_set("max_execution_time", "36000");


include_once('FDL/Class.Doc.php');
include_once('FDL/Class.DocVaultIndex.php');
include_once('VAULT/Class.VaultFile.php');


$dbaccess=GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}
$q=new QueryDb($dbaccess,"DocAttr");
$q->AddQuery("type = 'file' or type='image'");
//$q->AddQuery("frameid not in (select id from docattr where type~'array')");
$la=$q->Query(0,0,"TABLE");
foreach ($la as $k=>$v) {
  $docid=$v["docid"];
  $aid=$v["id"];

  $sql="insert into docvaultindex (docid,vaultid) (SELECT id, ltrim(split_part($aid,'|',2),' ')::int from doc$docid where $aid is not null and $aid ~ ''^[^\n]*[0-9]$');" ;
  print "$sql\n";
  
}



?>
