<?php
/**
 * Emailing
 *
 * @author Anakeen 2005
 * @version $Id: fdl_pubnavpreview.php,v 1.1 2005/05/03 16:55:22 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */




include_once("FDL/mailcard.php");
include_once("FDL/modcard.php");

/**
 * Preview of each document to be printed
 * @param Action &$action current action
 * @global docid Http var : folder id (generaly an action)
 * @global fromedit Http var : (Y|N) if Y action comes from edition else from viewing
 * @global zonebodycard Http var : definition of the zone used for print
 */
function fdl_pubnavpreview(&$action) {

  // GetAllParameters

  $docid = GetHttpVars("id");
  $zonebodycard = GetHttpVars("zone","FDL:FDL_PUBPRINTONE:S"); // define view zone
  $fromedit = (GetHttpVars("fromedit","Y")=="Y"); // need to compose temporary doc

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc = new Doc($dbaccess, $docid);
  $t=$doc->getContent(true,array(),true);
  if ($fromedit) {
    $doc = $doc->copy(true,false);
    $err=setPostVars($doc);
    $doc->modify();
  };

  

    
  foreach ($t as $k=>$v) {
    $tlay[]=array("udocid"=>$v["id"],
		  "utitle"=>$v["title"]);      
  }
  
  if ($err) $action->AddWarningMsg($err);

  $action->lay->setBlockData("DOCS",$tlay);
  $action->lay->set("dirid",$docid);
  


  
}


?>
