<?php
/**
 * Emailing
 *
 * @author Anakeen 2005
 * @version $Id: fdl_pubmail.php,v 1.2 2005/04/15 16:21:17 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */




include_once("FDL/mailcard.php");
include_once("FDL/modcard.php");


function fdl_pubmail(&$action) {

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
  };

  $zonebodycard="FDL:FDL_PUBSENDMAIL:S";
  $subject=$doc->getValue("pubm_title");
  $body=$doc->getValue("pubm_body");
  if (preg_match("/\[us_[a-z0-9_]+\]/i",$body)) {
    foreach ($t as $k=>$v) {
      $mail=getv($v,"us_mail");
      if ($mail != "") {
	$zoneu=$zonebodycard."?uid=".$v["id"];
	$to=$mail;	
	$cc="";
	$err=sendCard(&$action,
		      $doc->id,
		      $to,$cc,$subject,
		      $zoneu);
      }
    }
  } else {
    $tmail=array();
    foreach ($t as $k=>$v) {
      $mail=getv($v,"us_mail");
      if ($mail != "") $tmail[]=$mail;
    }
    $to="";
    $bcc=implode(",",$tmail);
    $cc="";
    $err=sendCard(&$action,
		  $doc->id,
		  $to,$cc,$subject,
		  $zonebodycard,false,"","",$bcc);
  }
  if ($err) $action->AddWarningMsg($err);


  
}


?>
