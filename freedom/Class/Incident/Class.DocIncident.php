<?php
// ---------------------------------------------------------------
// $Id: Class.DocIncident.php,v 1.3 2002/03/14 14:56:55 eric Exp $
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

$CLASS_DOCINCIDENT_PHP = '$Id: Class.DocIncident.php,v 1.3 2002/03/14 14:56:55 eric Exp $';


include_once("FDL/Class.Doc.php");


define ("FAM_INCIDENT", 103);
define("ATTR_TITRE",151);
define("ATTR_IDSITE",154);
define("ATTR_SITE",152);
define("ATTR_N°CONTRAT",153);
define("ATTR_IDCONTRAT",163);
define("ATTR_NOM1",156);
define("ATTR_TÉLÉPHONE1",164);
define("ATTR_EMAIL1",165);
define("ATTR_NOM2",158);
define("ATTR_TÉLÉPHONE2",159);
define("ATTR_EMAIL2",160);
define("ATTR_PRODUIT",162);
define("ATTR_IDPRODUIT",166);
define("ATTR_DESCRIPTION",183);
define("ATTR_GRAVITÉ",172);
define("ATTR_PRIORITÉ",173);
define("ATTR_SOLUTION",184);
define("ATTR_PROBLÈMESRENCONTRÉS",182);


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

			  array("e1"=>qualified,
				"e2"=>traited,
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
				"e2"=>suspended,
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
				    


  var $hidden_attributes_state = array(recorded => array(ATTR_DESCRIPTION,
							   ATTR_GRAVITÉ,
							   ATTR_PRIORITÉ,
							   ATTR_SOLUTION,
							   ATTR_PROBLÈMESRENCONTRÉS));
						    
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
    $oval = new DocValue($this->dbaccess, array($this->id, 165));
    $mail =  $oval->value; // send mail to client

    $oval = new DocValue($this->dbaccess, array($this->id, 187));
    $ref =  $oval->value; 

    if ($action->GetParam("CORE_LANG") == "fr") { // date format depend of locale
      setlocale (LC_TIME, "fr_FR");
      $sdate= strftime ("%A %d %B %H:%M");
    } else {
      $sdate= strftime ("%x %T");
    }
    $this->sendmail($mail , 
    		    sprintf(_("receipt call %s"), $ref),
		    sprintf(_("The incident call '%s' has been recorded on %s.\nIts reference is %s"),
			    $this->title,
			    $sdate,
			    $ref)
		    );
    break;
    case qualified:
      $this->profid=112;
    $oval = new DocValue($this->dbaccess, array($this->id, 124));
    $mail =  $oval->value; // send mail to analyzer
    $this->sendmail($mail , 
		    sprintf(_("Freedom : incident %s : transition to %s"),$this->title,_($newstate)),
		    $action->Getparam("CORE_PUBURL")."/index.php?sole=A&app=INCIDENT&action=INCIDENT_CARD&id=".$this->id);
    break;
    case rejected:
      $this->profid=113;
    break;
    case analyzed:
      $this->profid=114;
    $oval = new DocValue($this->dbaccess, array($this->id, 122));
    $mail =  $oval->value;// send mail to realyser
    $this->sendmail($mail , 
		    sprintf(_("Freedom : incident %s : transition to %s"),$this->title,_($newstate)),
		    $action->Getparam("CORE_PUBURL")."/index.php?sole=A&app=INCIDENT&action=INCIDENT_CARD&id=".$this->id);
    break;
    case traited:
      $this->profid=115;
    break;
    case closed:
      $this->profid=116;
    break;
    }


    return ($this->modify());
  }

  function sendmail($addr,  $object="Freedom", $body="") {
    if ($addr != "") {
      mail($addr,
	   $object,
	   $body,
	   "From: support@i-cesam.com\r\n".
	   "X-Mailer: PHP/" . phpversion());
    }
  }

 
}

?>