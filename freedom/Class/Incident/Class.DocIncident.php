<?php
// ---------------------------------------------------------------
// $Id: Class.DocIncident.php,v 1.11 2002/08/19 12:18:23 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Incident/Attic/Class.DocIncident.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2001
// O*O  Anakeen development team
//  O   dev@anakeen.com
// ---------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------

$CLASS_DOCINCIDENT_PHP = '$Id: Class.DocIncident.php,v 1.11 2002/08/19 12:18:23 eric Exp $';


include_once("FDL/Class.Doc.php");





define ("recorded", "recorded");   # N_("recorded")
define ("qualified", "qualified"); # N_("qualified")
define ("analyzed", "analyzed");   # N_("analyzed")
define ("traited", "traited");     # N_("traited")
define ("rejected", "rejected");   # N_("rejected")
define ("closed", "closed");       # N_("closed")
define ("suspended", "suspended");       # N_("suspended")

Class DocIncident extends Doc
{
    // --------------------------------------------------------------------
  //---------------------- OBJECT CONTROL PERMISSION --------------------
  
  var $obj_acl = array (
			array(
			      "name"		   =>"view",
			      "description"	   =>"view incident", # N_("view incident")
			      "group_default"      =>"Y"),
			array(
			      "name"               =>"edit",
			      "description"        =>"edit incident"),# N_("edit incident")
			array(
			      "name"               =>"delete",
			      "description"        =>"delete incident",# N_("delete incident")
			      "group_default"      =>"N"),
			array(
			      "name"               =>"edit",
			      "description"        =>"edit incident"),# N_("edit incident")

			// -------- for transitions ----------
			array(
			      "name"               =>"trans",
			      "description"        =>"transition")// N_("transition")
			
			);

  // ------------
  var $defDoctype='F';
  var $defClassname='DocIncident';

  var $transitions = array(
			  array("e1"=>"",
				"e2"=>recorded, 
				"m1"=>"controlTrans",
				"m2"=>"changeTransProfil"),			  

			  array("e1"=>recorded,
				"e2"=>qualified,
				"m1"=>"controlTrans",
				"m2"=>"changeTransProfil"),			  

			  array("e1"=>recorded,
				"e2"=>rejected,
				"m1"=>"controlTrans",
				"m2"=>"changeTransProfil"),

			  array("e1"=>recorded,
				"e2"=>suspended,
				"m1"=>"controlTrans",
				"m2"=>"changeTransProfil"),

			  array("e1"=>recorded,
				"e2"=>traited,
				"m1"=>"controlTrans",
				"m2"=>"changeTransProfil"),

			  array("e1"=>qualified,
				"e2"=>analyzed,
				"m1"=>"controlTrans",
				"m2"=>"changeTransProfil"),

			  array("e1"=>qualified,
				"e2"=>suspended,
				"m1"=>"controlTrans",
				"m2"=>"changeTransProfil"),
			 

			  array("e1"=>analyzed,
				"e2"=>traited,
				"m1"=>"controlTrans",
				"m2"=>"changeTransProfil"),

			  array("e1"=>analyzed,
				"e2"=>suspended,
				"m1"=>"controlTrans",
				"m2"=>"changeTransProfil"),

			  array("e1"=>qualified,
				"e2"=>rejected,
				"m1"=>"controlTrans",
				"m2"=>"changeTransProfil"),			  

			  array("e1"=>qualified,
				"e2"=>traited,
				"m1"=>"controlTrans",
				"m2"=>"changeTransProfil"),

			  array("e1"=>traited,
				"e2"=>analyzed,
				"m1"=>"controlTrans",
				"m2"=>"changeTransProfil"),

			  array("e1"=>traited,
				"e2"=>closed, 
				"m1"=>"controlTrans",
				"m2"=>"changeTransProfil"),

			  array("e1"=>analyzed,
				"e2"=>qualified,
				"m1"=>"controlTrans",
				"m2"=>"changeTransProfil"),

			  array("e1"=>suspended,
				"e2"=>traited, 
				"m1"=>"controlTrans",
				"m2"=>"changeTransProfil"),

			  array("e1"=>suspended,
				"e2"=>analyzed, 
				"m1"=>"controlTrans",
				"m2"=>"changeTransProfil"),

			  array("e1"=>suspended,
				"e2"=>qualified, 
				"m1"=>"controlTrans",
				"m2"=>"changeTransProfil"));
				    


 
						    
  function Docincident($dbaccess='', $id='',$res='',$dbid=0) {
    // don't use Doc constructor because it could call this constructor => infinitive loop
     DbObjCtrl::DbObjCtrl($dbaccess, $id, $res, $dbid);
  }

  

  function controlTrans($newstate) {
    return $this->control("trans");
  }

  function changeTransProfil($newstate) {
    global $action;

    switch ($newstate) {
    case recorded:
      $this->profid=111;

      //------------------------------
      // send recorded mail to clients
      $this->sendHtmlmail(
			  sprintf(_("[%s] incident registration"), $this->initid), 
			  "incident_mailrecord.xml");
    break;



    case qualified:
      $this->profid=112;
    $mail =  $this->getValue("IN_ANALMAIL"); // send mail to analyzer
    $this->sendmail($mail , 
		    sprintf(_("Freedom : incident %s : transition to %s"),$this->title,_($newstate)),
		    $action->Getparam("CORE_PUBURL")."/index.php?sole=A&app=INCIDENT&action=INCIDENT_CARD&id=".$this->id);
    break;
    case rejected:
      $this->profid=113;
    break;
    case analyzed:
      $this->profid=114;
    $mail =  $this->getValue("IN_TRTMAIL");// send mail to realyser
    $this->sendmail($mail , 
		    sprintf(_("Freedom : incident %s : transition to %s"),$this->title,_($newstate)),
		    $action->Getparam("CORE_PUBURL")."/index.php?sole=A&app=INCIDENT&action=INCIDENT_CARD&id=".$this->id);
    break;
    case traited:
      $this->profid=115;
      //------------------------------
      // send tratited mail to clients
      $this->sendHtmlmail(
			  sprintf(_("[%s] incident traited"), $this->initid), 
			  "incident_mailtraited.xml");

      
    break;
    case closed:
      $this->profid=116;
    break;
    case suspended:
      $this->profid=117;
    break;
    }


    return ($this->modify());
  }

  function sendmail($addr,  $object="Freedom", $body="") {
    if ($addr != "") {


      global $action;

      if ($action->getParam("INCIDENT_SENDMAIL") != "yes") return;

     
      $mailok=mail($addr,
		$object,
		$body,
		"From: ".$action->GetParam("FROM_MAIL_INCIDENT")."\r\n".
		"Bcc: ".$action->GetParam("BCC_MAIL_INCIDENT")."\r\n".
		"X-Mailer: PHP/" . phpversion());
      if (! $mailok) $action->exitError("mail cannot be sent");
      AddLogMsg(sprintf(_("send internal mail to %s (bcc:%s)"),$addr,$action->GetParam("BCC_MAIL_INCIDENT")));
    }
  }
  function sendHtmlmail(  $object, $layout) {



      global $action;

      if ($action->getParam("INCIDENT_SENDMAIL") != "yes") return;
      $mailaddr = $this->GetValue( "IN_CALLMAIL");
      if ($mailaddr == "") return; // no mail to deliver
      $title = stripslashes($this->GetValue( "IN_TITLE"));// the title

   

      if ($action->GetParam("CORE_LANG") == "fr_FR") { // date format depend of locale
	setlocale (LC_TIME, "fr_FR");
	$sdate= strftime ("%A %d %B %Y");
      } else {
	$sdate= strftime ("%x");
      }

      
     
      $incidentmail = new Layout($action->GetLayoutFile($layout),$action);
      $incidentmail->set("title", $title);
      $incidentmail->set("ref", $this->initid);
      $incidentmail->set("date", $sdate);
      $incidentmail->set("contactname",$this->GetValue( "IN_CALLNAME"));
      $incidentmail->set("contract",$this->GetValue("IN_CONTRACT"));
      $incidentmail->set("site",$this->GetValue("IN_SITE"));
      $incidentmail->set("frommail",$action->GetParam("FROM_MAIL_INCIDENT"));
      $incidentmail->set("datesept",strftime("%A %d %B %Y", $this->revdate+24*3600*7)); // date + 7days


      // search ccmail from site
      $ccmail = "";
      $idsite = $this->GetValue("IN_IDSITE");

      if ($idsite > 0) {
	$site = new Doc($this->dbaccess, $idsite);
	$ccmail = $site->GetValue("SI_CCMAIL");	
      }

      // insert logo image
      $logofile=$action->GetImageFile("logocesam.gif");
      $fd = fopen($logofile, "r");
      $logocontent=fread($fd, filesize($logofile));
      fclose($fd);
      $incidentmail->set("imgdata",base64_encode($logocontent));
      $body = $incidentmail->gen();

      $bcc = $action->GetParam("BCC_MAIL_INCIDENT");

      // send bcc if activated

      if ($action->getParam("FDL_BCC") == "yes") {
	
	include_once("Class.MailAccount.php");
	$ma = new MailAccount("",$action->user->id);
	if ($ma->isAffected()) {
	  $dom = new Domain("",$ma->iddomain);
	  $umail = $ma->login."@".$dom->name;
print "umail=$umail";
	  if ($bcc == "") $bcc = $umail;
	  else $bcc .= ",$umail";
	}
      }
      $mailok=mail($mailaddr,
		   $object, // object
		   $body,
		   "From: ".$action->GetParam("FROM_MAIL_INCIDENT")."\r\n".
		   "Bcc: ".$bcc."\r\n".
		   "Cc: ".$ccmail."\r\n".
		   "Content-Type: multipart/alternative; boundary=\"=_alternative 003C044E00256A9A_=\"\r\n".
		   "X-Mailer: PHP/" . phpversion());
      if (! $mailok) $action->exitError("mail cannot be sent");      
      AddLogMsg(sprintf(_("send official mail to %s (cc: %s - bcc:%s)"),
			$mailaddr,
			$ccmail,
			$bcc));
  }

 
}

?>