<?php
/**
 * Get Values in XML form
 *
 * @author Anakeen 2006
 * @version $Id: getdocvalues.php,v 1.2 2006/06/22 16:18:05 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage FDC
 */
 /**
 */



include_once("FDL/Class.Doc.php");


/**
 * Get  doc attributes values
 * @param Action &$action current action
 * @global id Http var : document id to view
 */
function getdocvalues(&$action) {
  header('Content-type: text/xml; charset=utf-8'); 

  $mb=microtime();
  $docid = GetHttpVars("id");
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $action->lay->set("warning","");

  $doc=new_doc($dbaccess,$docid);
  $tvalues=array();
  
  if (! $doc->isAlive()) $err=sprintf(_("document [%s] not found"));
  if ($err == "") {
    $err=$doc->control("view");
    if ($err == "") {
      $values=$doc->getValues();
      foreach ($values as $aid=>$v) {
	$a=$doc->getAttribute($aid);
	if ($a->visibility != "I") {
	  $tvalues[]=array("attrid"=>$aid,
			   "value"=>utf8_encode($v));
	}
      }
    }
  }
  if ($err) $action->lay->set("warning",utf8_encode($err));
  
  $action->lay->setBlockData("VALUES",$tvalues);
  $action->lay->set("CODE","OK");
  $action->lay->set("count",1);
  $action->lay->set("delay",microtime_diff(microtime(),$mb));					

}