<?php
/**
 * Construct vault index database
 *
 * @author Anakeen 2004
 * @version $Id: SetDocVaultIndex.php,v 1.2 2005/03/30 17:42:09 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
ini_set("include_path", ".:/home/httpd/what:/home/httpd/what/WHAT:/usr/share/pear");
ini_set("max_execution_time", "36000");

include_once('Class.Action.php');
include_once('Class.Application.php');
include_once('Class.Session.php');
include_once('Class.Log.php');

include_once('FDL/Class.Doc.php');
include_once('FDL/Class.DocVaultIndex.php');

$appl = new Application();
$appl->Set("FDL", $core);
$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}


$dvi = new DocVaultIndex($dbaccess);

$doc = new Doc($dbaccess);
$doc->exec_query("select * from doc where id > 0 and doctype!='Z'");
$idoc = $doc->numrows();

loclog("Base $dbaccess, ".$idoc." document".($idoc?"s":"")." to process");
$dvi->exec_query("select * from docvaultindex");
loclog("Doc/Vault Index contains ".$dvi->numrows()." associations");


for ($c=0; $c < $idoc; $c++) {
  $row = $doc->fetch_array($c,PGSQL_ASSOC);
  $tdoc = new Doc($dbaccess, $row["id"]);
  UpdateVaultIndex($c, $tdoc, $dvi);
  unset($tdoc);
}
$dvi->exec_query("select * from docvaultindex");
loclog("Doc/Vault Index contains ".$dvi->numrows()." associations");

exit;


function UpdateVaultIndex($i, &$doc, &$dvi) {
  $vl = ""; $vic=0;
  $err = $dvi->DeleteDoc($doc->id);
  $fa = $doc->GetFileAttributes();
  foreach ($fa as $aid=>$oattr) {
    if ($oattr->inArray()) {
      $ta=$doc->getTValue($aid);
    } else {
      $ta=array($doc->getValue($aid));
    }
    foreach ($ta as $k=>$v) {
      $vid="";
      if (ereg ("(.*)\|(.*)", $v, $reg)) {
        $vid=$reg[2];
        $dvi->docid = $doc->id;
        $dvi->vaultid = $vid;
        $dvi->Add();
	$vl .= " ".$vid;
	$vic++;
      }
    }
  }
  if ($vic>0)  loclog("[$i] document [".$doc->id."::".$doc->title."]  added vault file".($vic>1?"s":"")." ".$vl);
  
}


function loclog($s) {
  echo "SetDocVaultIndex> $s\n";
}

?>
