<?php
/**
 * Modify a document
 *
 * @author Anakeen 2000 
 * @version $Id: modoption.php,v 1.1 2004/11/19 09:55:05 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */




include_once("FDL/modcard.php");

include_once("FDL/Class.DocFam.php");
include_once("FDL/Class.Dir.php");


// -----------------------------------
function modoption(&$action) {
  // -----------------------------------

  // Get all the params      
  $docid=GetHttpVars("id");  // document id
  $aid=GetHttpVars("aid"); // linked attribute id
  

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc = new Doc($dbaccess,$docid);
  if (!$doc->isAlive()) $action->exitError(sprintf(_("modoption: document [%d] is not alive"),$docid));

  
  $err = setPostVars($doc);


  if ($err != "")  $action->AddWarningMsg($err);
  else {   
    $action->lay->set("aid",$aid);
    $action->lay->set("docid",$docid);
    $listattr = $doc->GetNormalAttributes();
    $vo="";
    foreach($listattr as $k=>$v) {
      if ($v->usefor=="O") {
	$vo .= "[".$v->id."|".$doc->getValue($v->id)."]";
      }
    }
    $action->lay->set("vo",$vo);
    $action->lay->set("uuvo",urlencode($vo));
  }
  
  
  
}


?>
