<?php
/**
 * Emailing
 *
 * @author Anakeen 2005
 * @version $Id: fdl_pubmail.php,v 1.1 2005/04/14 14:30:40 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */




include_once("FDL/mailcard.php");
include_once("FDL/modcard.php");


// -----------------------------------
// -----------------------------------
function fdl_pubmail(&$action) {
  // -----------------------------------

  // GetAllParameters

  $docid = GetHttpVars("id");
  $zonebodycard = GetHttpVars("zone"); // define view action
  $fromedit = (GetHttpVars("fromedit","Y")=="Y"); // need to compose temporary doc

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc = new Doc($dbaccess, $docid);
  $t=$doc->getContent();
  if ($fromedit) {
    $doc = $doc->copy(true,false);
    $err=setPostVars($doc);
    $doc->modify();
  }
  $tmail=array();
  foreach ($t as $k=>$v) {
    $mail=getv($v,"us_mail");
    if ($mail != "") $tmail[]=$mail;
  }


  print_r2($tmail);
  $to=implode(",",$tmail);
  $zonebodycard="FDL:FDL_PUBSENDMAIL:S";
  $cc="";
  $subject=$doc->getValue("pubm_title");
  $err=sendCard(&$action,
		$doc->id,
		$to,$cc,$subject,
		$zonebodycard);
  print "err=$err";

  
}


?>
