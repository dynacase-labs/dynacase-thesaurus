<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Method.Incident.php,v 1.6 2004/02/03 08:41:23 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage INCIDENT
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: Method.Incident.php,v 1.6 2004/02/03 08:41:23 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Incident/Attic/Method.Incident.php,v $
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



var $cviews=array("INCIDENT:VIEWABSTRACTCARD",
		  "INCIDENT:INCIDENT_MAILRECORD:S",
		  "INCIDENT:INCIDENT_MAILTRAITED:S");
var $eviews=array("USERCARD:EDITUSERCARD");
  
var $defaultabstract= "INCIDENT:VIEWABSTRACTCARD";
  
// -----------------------------------
function viewabstractcard($target="finfo",$ulink=true,$abstract="Y") {
     // -----------------------------------
     doc::viewabstractcard($target,$ulink,$abstract);
     $this->viewprop($target,$ulink,$abstract);


     if ($this->state != "") $this->lay->set("state", _($this->state));
     else $this->lay->set("state","");

     global $action;
     $action->parent->AddJsRef("INCIDENT/Layout/incident_list.js"); // to color border
}

function SpecRefresh() {
  // compute reference

  //--------------------------------------
  //R_reference(T,IN_IDSITE):IN_REF

  $this->AddParamRefresh("","IN_REF,IN_CREATEDATE,IN_LOCKER");


  $idsite=$this->getValue("in_idsite");
  if ($idsite > 0) {
    $osite = new Doc($this->dbaccess, $idsite);
    $society = $osite->getValue("SI_SOCIETY");
  } else $society= $this->getValue("in_site");

  $ncontrat= $this->GetValue('IN_CONTRACT');

  if (($society == "") && ($ncontrat == ""))  $ref="FI/".$this->initid."/".$this->GetValue('IN_TITLE');
  else $ref = "FI/".$this->initid."/$society/$ncontrat";
  
  $this->setValue("IN_REF", $ref);

  //--------------------------------------
  //R_createdate(T,A):IN_CREATEDATE

  $doc = new Doc($this->dbaccess, $this->initid);

  if (GetParam("CORE_LANG") == "fr_FR") { 
    // date format depend of locale
      setlocale (LC_TIME, "fr_FR");
    $revdate = strftime ("%a %d %b %Y %H:%M",$doc->revdate);
  } else {
     $revdate =  strftime ("%x %T",$doc->revdate);
  }
  $this->setValue("IN_CREATEDATE", $revdate);

  //--------------------------------------
  //R_locker(T):IN_LOCKER
  if ($this->islocked()) {
    $user = new User("", abs($this->locked));
    $this->setValue("IN_LOCKER",$user->firstname." ".$user->lastname);
  } else $this->setValue("IN_LOCKER", " ");



  // refresh contrat latest
  if (( $idc=$this->getValue("IN_IDCONTRACT")) > 0) {
    $doc = new Doc($this->dbaccess, $idc);
    
    if ($doc->locked == -1) { // get latest contract
      $doc= new Doc($this->dbaccess, $doc->latestId());
      $this->setValue("IN_IDCONTRACT", $doc->id);
    }

    // search technical responsable
    $respid=$doc->getValue("CO_IDCLT1");
    if ($respid > 0) {
      $resp=new Doc($this->dbaccess, $respid);
      $this->setValue("IN_IDRTECH", $resp->id);
      $this->setValue("IN_RTECHMAIL", $resp->getValue("US_MAIL"));
      $this->setValue("IN_RTECHNAME", $resp->title);
    }
  }
}


function PostModify() {
  // lock the doc if not 
  // an incident is affected by the editor until he unlock or revise the incident
  $err = $this->lock();
  
}

  


// -----------------------------------
function incident_mailrecord($target="_self",$ulink=true,$abstract=false) {
  
  if (GetParam("CORE_LANG") == "fr_FR") { // date format depend of locale
    setlocale (LC_TIME, "fr_FR");
    $sdate= strftime ("%A %d %B %Y");
  } else {
    $sdate= strftime ("%x");
  }
  $this->lay->set("title", stripslashes($this->GetValue( "IN_TITLE")));
  $this->lay->set("ref", $this->initid);
  $this->lay->set("date", $this->GetValue( "IN_CREATEDATE"));
  $this->lay->set("contactname",$this->GetValue( "IN_CALLNAME"));
  $this->lay->set("contract",$this->GetValue("IN_CONTRACT"));
  $this->lay->set("site",$this->GetValue("IN_SITE"));
  $this->lay->set("frommail",GetParam("FROM_MAIL_INCIDENT"));
  $this->lay->set("datesept",strftime("%A %d %B %Y", $this->revdate+24*3600*7)); // date + 7days

  $this->viewattr($target,$ulink,$abstract);
  return;
}

// -----------------------------------
function incident_mailtraited($target="_self",$ulink=true,$abstract=false) {
  
  return $this->incident_mailrecord($target,$ulink,$abstract); 
}
?>