<?php
/**
 * Detect file which are not indexed and index them
 *
 * @author Anakeen 2004
 * @version $Id: FullFileIndex.php,v 1.1 2007/06/18 15:58:10 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
ini_set("max_execution_time", "36000");


include_once('FDL/Class.Doc.php');
include_once("FDL/Lib.Dir.php");

define("REDCOLOR",'[1;31;40m');
define("UPDTCOLOR",'[1;32;40m');
define("STOPCOLOR",'[0m');

$force=getHttpVars("force")=="yes";

$dbaccess=GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}
$o=new DbObj($dbaccess);
$q=new QueryDb($dbaccess,"DocAttr");
$q->AddQuery("type = 'file'");
//$q->AddQuery("frameid not in (select id from docattr where type~'array')");
$la=$q->Query(0,0,"TABLE");





foreach ($la as $k=>$v) {
  $docid=$v["docid"];
  $aid=$v["id"];

  $filter=array();
  $filter[]="$aid is not null";
  if (!$force) $filter[]="{$aid}_txt is null";
  $ldoc = getChildDoc($dbaccess, 0,0,"ALL", $filter,$action->user->id,"ITEM",$docid);
  $c=countDocs($ldoc);
  print "\n-- Family $docid, Attribute : $aid\n";
  while ($doc=getNextDoc($dbaccess,$ldoc)) {
    print "$c)".$doc->title."- $aid -".$doc->id.'- '.$doc->fromid."\n";
    $c--;
    $err=$doc->recomputeLatinFiles($aid);
    if ($err) print REDCOLOR.$err.STOPCOLOR;
  }
  
  
}



//print "$sqlicon\n";

?>
