<?php
// ---------------------------------------------------------------
// $Id: Class.WDocIncident.php,v 1.7 2003/01/17 16:54:24 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Incident/Attic/Class.WDocIncident.php,v $
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

$CLASS_DOCINCIDENT_PHP = '$Id: Class.WDocIncident.php,v 1.7 2003/01/17 16:54:24 eric Exp $';


include_once("FDL/Class.WDoc.php");





define ("recorded", "recorded");   # N_("recorded")
define ("qualified", "qualified"); # N_("qualified")
define ("analyzed", "analyzed");   # N_("analyzed")
define ("traited", "traited");     # N_("traited")
define ("rejected", "rejected");   # N_("rejected")
define ("closed", "closed");       # N_("closed")
define ("suspended", "suspended");       # N_("suspended")


define ("Trecorded",  "Trecorded");   # N_("Trecorded")
define ("Tqualified", "Tqualified");  # N_("Tqualified")
define ("Tanalyzed",  "Tanalyzed");   # N_("Tanalyzed")
define ("Ttraited",   "Ttraited");    # N_("Ttraited")
define ("Trejected",  "Trejected");   # N_("Trejected")
define ("Tclosed",    "Tclosed");     # N_("Tclosed")
define ("Tsuspended", "Tsuspended");  # N_("Tsuspended")


Class WDocIncident extends WDoc
{
  
  

  // ------------
  var $defClassname="WDocIncident";
  var $attrPrefix="IWF"; // prefix attribute


    var $transitions = array("Trecorded" =>array("m1"=>"",
					       "m2"=>"SendMailByState"),
			     "Tqualified" =>array("m1"=>"",
					       "m2"=>"SendMailByState"),
			     "Tanalyzed" =>array("m1"=>"",
					       "m2"=>"SendMailByState"),
			     "Ttraited" =>array("m1"=>"",
					       "m2"=>"SendMailByState"),
			     "Trejected" =>array("m1"=>"",
					       "m2"=>""),
			     "Tsuspended" =>array("m1"=>"",
					       "m2"=>""),
			     "Tclosed" =>array("m1"=>"",
					     "m2"=>""));
    var $cycle = array(
			  array("e1"=>"",
				"e2"=>recorded, 
				"t"=>Trecorded),			  

			  array("e1"=>recorded,
				"e2"=>qualified,
				"t"=>Tqualified),			  

			  array("e1"=>recorded,
				"e2"=>rejected,
				"t"=>Trejected),

			  array("e1"=>recorded,
				"e2"=>suspended,
				"t"=>Tsuspended),

			  array("e1"=>recorded,
				"e2"=>traited,
				"t"=>Ttraited),

			  array("e1"=>qualified,
				"e2"=>analyzed,
				"t"=>Tanalyzed),

			  array("e1"=>qualified,
				"e2"=>suspended,
				"t"=>Tsuspended),
			 

			  array("e1"=>analyzed,
				"e2"=>traited,
				"t"=>Ttraited),

			  array("e1"=>analyzed,
				"e2"=>suspended,
				"t"=>Tsuspended),

			  array("e1"=>qualified,
				"e2"=>rejected,
				"t"=>Trejected),			  

			  array("e1"=>qualified,
				"e2"=>traited,
				"t"=>Ttraited),

			  array("e1"=>traited,
				"e2"=>analyzed,
				"t"=>Tanalyzed),

			  array("e1"=>traited,
				"e2"=>closed, 
				"t"=>Tclosed),

			  array("e1"=>analyzed,
				"e2"=>qualified,
				"t"=>Tqualified),

			  array("e1"=>suspended,
				"e2"=>traited, 
				"t"=>Ttraited),

			  array("e1"=>suspended,
				"e2"=>analyzed, 
				"t"=>Tanalyzed),

			  array("e1"=>suspended,
				"e2"=>qualified, 
				"t"=>Tqualified));
				    


 
	

  


  function SendMailByState($newstate) {
    global $action;

    switch ($newstate) {
    case recorded:

      //------------------------------
      // send recorded mail to clients
      $this->sendHtmlmail(
			  sprintf(_("[%s] incident registration"), $this->doc->initid), 
			  "incident_mailrecord.xml");
    break;

    case qualified:
    $mail =  $this->doc->getValue("IN_ANALMAIL"); // send mail to analyzer
    $this->sendmail($mail , 
		    sprintf(_("Freedom : incident %s : transition to %s"),$this->doc->title,_($newstate)),
		    $action->Getparam("CORE_PUBURL")."/index.php?sole=A&app=INCIDENT&action=GENERIC_CARD&id=".$this->doc->id);
    break;

    case analyzed:
    $mail =  $this->doc->getValue("IN_TRTMAIL");// send mail to realyser
    $this->sendmail($mail , 
		    sprintf(_("Freedom : incident %s : transition to %s"),$this->doc->title,_($newstate)),
		    $action->Getparam("CORE_PUBURL")."/index.php?sole=A&app=INCIDENT&action=GENERIC_CARD&id=".$this->doc->id);
    break;

    case traited:
      //------------------------------
      // send traited mail to clients
      $this->sendHtmlmail(
			  sprintf(_("[%s] incident traited"), $this->doc->initid), 
			  "incident_mailtraited.xml");

      
    break;
    }


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
      $mailaddr = $this->doc->GetValue( "IN_CALLMAIL");
      if ($mailaddr == "") return; // no mail to deliver
      $title = stripslashes($this->doc->GetValue( "IN_TITLE"));// the title


   

      if ($action->GetParam("CORE_LANG") == "fr_FR") { // date format depend of locale
	setlocale (LC_TIME, "fr_FR");
	$sdate= strftime ("%A %d %B %Y");
      } else {
	$sdate= strftime ("%x");
      }

      
     
      $incidentmail = new Layout($action->GetLayoutFile($layout),$action);
      $incidentmail->set("title", $title);
      $incidentmail->set("ref", $this->doc->initid);
      $incidentmail->set("date", $sdate);
      $incidentmail->set("contactname",$this->doc->GetValue( "IN_CALLNAME"));
      $incidentmail->set("contract",$this->doc->GetValue("IN_CONTRACT"));
      $incidentmail->set("site",$this->doc->GetValue("IN_SITE"));
      $incidentmail->set("frommail",$action->GetParam("FROM_MAIL_INCIDENT"));
      $incidentmail->set("datesept",strftime("%A %d %B %Y", $this->doc->revdate+24*3600*7)); // date + 7days


      // search ccmail from site
      $ccmail = "";
      $idsite = $this->doc->GetValue("IN_IDSITE");

      if ($idsite > 0) {
	$site = new Doc($this->doc->dbaccess, $idsite);
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