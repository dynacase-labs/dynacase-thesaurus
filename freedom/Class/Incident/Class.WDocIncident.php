<?php
/**
 * Incident Workflow
 *
 * @author Anakeen 2002
 * @version \$Id: Class.WDocIncident.php,v 1.14 2004/01/15 16:31:43 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage INCIDENT
 */
/**
 */
include_once("FDL/Class.WDoc.php");


define ("recorded", "recorded");   # N_("recorded")
define ("qualified", "qualified"); # N_("qualified")
define ("analyzed", "analyzed");   # N_("analyzed")
define ("traited", "traited");     # N_("traited")
define ("rejected", "rejected");   # N_("rejected")
define ("closed", "closed");       # N_("closed")
define ("suspended", "suspended");       # N_("suspended")
define ("draft", "draft");       # N_("draft")


define ("Trecorded",  "Trecorded");   # N_("Trecorded")
define ("Tqualified", "Tqualified");  # N_("Tqualified")
define ("Tanalyzed",  "Tanalyzed");   # N_("Tanalyzed")
define ("Ttraited",   "Ttraited");    # N_("Ttraited")
define ("Trejected",  "Trejected");   # N_("Trejected")
define ("Tclosed",    "Tclosed");     # N_("Tclosed")
define ("Tsuspended", "Tsuspended");  # N_("Tsuspended")

/**
 * Incident Workflow
 *
 */
     Class WDocIncident extends WDoc {
  
  

  // ------------
  var $defClassname="WDocIncident";
  var $attrPrefix="IWF"; // prefix attribute
  var $firstState=draft;

  var $transitions = array("Trecorded" =>array("m1"=>"",
					       "m2"=>"SendMailByState"),
			   "Tqualified" =>array("m1"=>"",
						"m2"=>"SendMailByState"),
			   "Tanalyzed" =>array("m1"=>"",
					       "m2"=>"SendMailByState"),
			   "Ttraited" =>array("m1"=>"isCompleteIncident",
					      "m2"=>"SendMailByState"),
			   "Trejected" =>array("m1"=>"",
					       "m2"=>""),
			   "Tsuspended" =>array("m1"=>"",
						"m2"=>""),
			   "Tclosed" =>array("m1"=>"",
					     "m2"=>""));
  var $cycle = array(
		     array("e1"=>draft,
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
			   "e2"=>qualified,
			   "t"=>Tqualified),

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
    include_once("FDL/mailcard.php");
    global $action;
    $err="";

    switch ($newstate) {
    case recorded:

      //------------------------------
      // send recorded mail to clients

      $this->sendOfficialMail(
			      sprintf(_("[%s] incident registration"), $this->doc->initid), 
			      "INCIDENT:INCIDENT_MAILRECORD:S");
      break;

    case qualified:

      if ($action->getParam("INCIDENT_SENDMAIL") == "yes") {
	// send mail to analyze the incident
	$to =  $this->doc->getValue("IN_ANALMAIL"); // send mail to analyzer 
	$cc = "";
	$subject=sprintf(_("Incident : ask for analyze %s"),$this->doc->title,_($newstate));

	sendCard(&$action,
		 $this->doc->id,
		 $to,$cc,$subject,"INCIDENT:MAILTOANALYZER:S",true);
      }

      break;

    case analyzed:
      

      if ($action->getParam("INCIDENT_SENDMAIL") == "yes") {
	// send mail to perform the incident
	$to =  $this->doc->getValue("IN_TRTMAIL");// send mail to realyser $cc = "";
	$subject=sprintf(_("Incident : ask for perform %s"),$this->doc->title,_($newstate));

	sendCard(&$action,
		 $this->doc->id,
		 $to,$cc,$subject,"INCIDENT:MAILTOPERFORM:S",true);
      }
    
      break;

    case traited:
      //------------------------------
      // send traited mail to clients
      $err=$this->isCompleteIncident();
      if ($err == "") {

	$this->sendOfficialMail(
				sprintf(_("[%s] incident traited"), $this->doc->initid), 
				"INCIDENT:INCIDENT_MAILTRAITED:S");
      }
      
      break;
    }

    return $err;

  }
  

  
  function isCompleteIncident() {
    
    // the value IN_TRTPB must be set
    $err="";
    $trtpb = $this->doc->getValue("IN_TRTPB");
    

    
    if ($trtpb=="") $err=sprintf(_("the %s attribute must be set."),$this->doc->getLabel("in_trtpb"));
    return $err;
  }






  function sendOfficialMail( $subject, $zone) {
    global $action;

    include_once("FDL/mailcard.php");

    
    $to = $this->doc->GetValue( "IN_CALLMAIL");

    $idcontract = $this->doc->GetValue("IN_IDCONTRACT");
    $bcc = $action->GetParam("BCC_MAIL_INCIDENT");
    $cc="";
    if ($idcontract > 0) {
      $contract = new Doc($this->doc->dbaccess,$idcontract );
      if ($contract->getValue("CO_CLTCOPYMAIL1")=="1")
	$cc = $this->doc->GetValue("IN_RTECHMAIL");	
    }
    $comment="";
    $from=$action->GetParam("FROM_MAIL_INCIDENT");
    sendCard(&$action,
	     $this->doc->id,
	     $to,$cc,$subject,$zone,true,$comment,
	     $from,$bcc);
       
    AddLogMsg(sprintf(_("send official mail to %s (cc: %s - bcc:%s)"),
		      $to,
		      $cc,
		      $bcc));
    return "";

  }
 
}

?>
