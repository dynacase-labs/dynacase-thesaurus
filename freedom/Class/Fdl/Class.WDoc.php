<?php
// ---------------------------------------------------------------
// $Id: Class.WDoc.php,v 1.29 2003/07/29 13:09:33 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Class.WDoc.php,v $
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


$CLASS_DOC_PHP = '$Id: Class.WDoc.php,v 1.29 2003/07/29 13:09:33 eric Exp $';

include_once('FDL/Class.Doc.php');

// Work Flow Classe
Class WDoc extends Doc {

  

  var $acls = array("view","edit","delete");

	

  var $usefor='W';
  var $defDoctype='W';
  var $defClassname='WDoc';
  var $attrPrefix="WF"; // prefix attribute
  // --------------------------------------------------------------------
  //----------------------  TRANSITION DEFINITION --------------------
  var $transitions = array();// set by childs classes
  var $cycle = array();// set by childs classes
  var $firstState=""; // first state in workflow

  function WDoc($dbaccess='', $id='',$res='',$dbid=0) {
    // first construct acl array

    $ka = POS_WF;
    while (list($k, $trans) = each($this->transitions)) {
      $this->dacls[$k]=array("pos"=>$ka,
			     "description" =>_($k));
      $this->acls[]=$k;
      $ka++;
    }
    if (isset($this->fromid)) $this->defProfFamId=$this->fromid; // it's a profil itself



    // don't use Doc constructor because it could call this constructor => infinitive loop
    DocCtrl::DocCtrl($dbaccess, $id, $res, $dbid);

    $this->postConstructor();
  }

  function Set(&$doc) {
    $this->doc= &$doc;
    if ($doc->state == "") {
      $doc->state=$this->firstState;
      $this->changeProfil($doc->state);
    }
  }
  
  function postConstructor() {
    // to modify variable (attribute of class)
    // must be defined by child classes
  }


  function changeProfil($newstate) {

    if ($newstate != "") {
      $profid=intval($this->getValue($this->attrPrefix."_ID".strtoupper($newstate)));
      
      if ($profid > 0) {
	// change only if new profil
	$this->doc->setProfil($profid);
      }
    }
  }

  function CreateProfileAttribute() {

    if ($this->doctype=='C') $cid = $this->id;
    else $cid = $this->fromid;

  
      
    $ordered=100;

    $this->getStates();
    reset($this->states);
    while (list($k, $state) = each($this->states)) {

      // --------------------------
      // frame
      $aidframe=strtolower($this->attrPrefix."_FR".strtoupper($state));
      $oattr = new DocAttr($this->dbaccess, array($cid,$aidframe));
      $oattr->docid=$cid;
      $oattr->visibility="F";
      $oattr->type="frame";
      $oattr->id=$aidframe;
      $oattr->frameid=$oattr->id;
      $oattr->labeltext=sprintf(_("parameters for %s state"),_($state));
      $oattr->link="";
      $oattr->phpfunc="";
      $oattr->ordered=$ordered++;
      if ($oattr->isAffected()) $oattr->Modify();
      else $oattr->Add();
     


      // --------------------------
      // profil id
      $aid=strtolower($this->attrPrefix."_".strtoupper($state));
      $aidprofilid=strtolower($this->attrPrefix."_ID".strtoupper($state));
      $oattr = new DocAttr($this->dbaccess, array($cid,$aidprofilid));
      $oattr->docid=$cid;
      $oattr->visibility="H";
      $oattr->type="text";
      $oattr->id=$aidprofilid;
      $oattr->labeltext=sprintf(_("id %s profile"),_($state));
      $oattr->link="";
      $oattr->frameid=$aidframe;
      $oattr->phpfunc="::getTitle($aidprofilid):$aid";
      $oattr->ordered=$ordered++;
      if ($oattr->isAffected()) $oattr->Modify();
      else $oattr->Add();


      // --------------------------
      // profil user comprehensive
      $oattr = new DocAttr($this->dbaccess, array($cid,$aid));
      $oattr->docid=$cid;
      $oattr->visibility="W";
      $oattr->type="text";
      $oattr->link="%S%app=FDL&action=FDL_CARD&id=%".$aidprofilid."%";
      $oattr->phpfile="fdl.php";
      $oattr->id=$aid;
      $oattr->frameid=$aidframe;
      $oattr->phpfunc="lprofil(D,{$oattr->id}):$aidprofilid,{$oattr->id}";
      $oattr->labeltext=sprintf(_("%s profile"),_($state));
      $oattr->ordered=$ordered++;
      if ($oattr->isAffected()) $oattr->Modify();
      else $oattr->Add();
      
      // --------------------------
      // mask id
      $aidmaskid=strtolower($this->attrPrefix."_MSKID".strtoupper($state));
      $aid=strtolower($this->attrPrefix."_MSK".strtoupper($state));
      $oattr = new DocAttr($this->dbaccess, array($cid,$aidmaskid));
      $oattr->docid=$cid;
      $oattr->visibility="H";
      $oattr->type="text";
      $oattr->id=$aidmaskid;
      $oattr->labeltext=sprintf(_("id %s mask"),_($state));
      $oattr->link="";
      $oattr->frameid=$aidframe;
      $oattr->phpfunc="::getTitle($aidmaskid):$aid";
      $oattr->ordered=$ordered++;
      if ($oattr->isAffected()) $oattr->Modify();
      else $oattr->Add();


      // --------------------------
      // mask user comprehensive
      $oattr = new DocAttr($this->dbaccess, array($cid,$aid));
      $oattr->docid=$cid;
      $oattr->visibility="W";
      $oattr->type="text";
      $oattr->link="%S%app=FDL&action=FDL_CARD&id=%".$aidmaskid."%";
      $oattr->phpfile="fdl.php";
      $oattr->id=$aid;
      $oattr->frameid=$aidframe;
      $oattr->ordered=$ordered++;
      $oattr->phpfunc="lmask(D,{$oattr->id}):$aidmaskid,{$oattr->id}";
      $oattr->labeltext=sprintf(_("%s mask"),_($state));
      if ($oattr->isAffected()) $oattr->Modify();
      else $oattr->Add();

    }
    refreshPhpPgDoc($this->dbaccess, $cid);
   
    
  }
  // --------------------------------------------------------------------
  function ChangeState ($newstate, $addcomment="", $force=false) {
      
    // if ($this->doc->state == $newstate) return ""; // no change => no action
    // search if possible change in concordance with transition array
    $foundFrom = false;
    $foundTo = false;
    reset($this->cycle);
    while (list($k, $trans) = each($this->cycle)) {
      if (($this->doc->state == $trans["e1"])  ) { 
	// from state OK
	$foundFrom = true;
	if ($newstate == $trans["e2"]) {
	  $foundTo = true;
	  $tr = $this->transitions[$trans["t"]];
	  $tname=$trans["t"];
	}
	  
      }
    }
      
    if  ($this->userid!=1) {// admin an go to any states 

      if (! $foundTo) return (sprintf(_("ChangeState :: the new state '%s' is not known or is not allowed"), _($newstate)));
      if (! $foundFrom) return (sprintf(_("ChangeState :: the initial state '%s' is not known"), _($this->doc->state)));
    }
    // verify if completed doc
    $err = $this->doc->isCompleteNeeded();
    if ($err != "") return $err;

    // verify if privilege granted

    $err=$this->control($tname);
    if ($err != "") return $err;

    if ($tr["m1"] != "")  {
      // apply first method (condition for the change)
	  
      if (! method_exists($this, $tr["m1"])) return (sprintf(_("the method '%s' is not known for the object class %s"), $tr["m1"], get_class($this)));
	
      $err = call_user_method ($tr["m1"], $this, $newstate);
	
      if ($err == "->") {
	if ($force) {
	  $err=""; // it is the return of the report	    
	  SetHttpVar("redirect_app",""); // override the redirect
	  SetHttpVar("redirect_act","");
	} else return ""; //it is not a real error, but don't change state (reported)
      }
      if ($err != "") return (sprintf(_("ChangeState :: the method '%s' has the following error %s"), $tr["m1"], $err));
	
	
    }
      
    // change the state
    $oldstate = $this->doc->state==""?" ":$this->doc->state;
    $this->doc->state = $newstate;
    $this->changeProfil($newstate);
    $this->doc->disableEditControl();
    $err = $this->doc->Modify();   // don't control edit permission
    if ($err != "") return $err;
      
    $revcomment = sprintf(_("change state : %s to %s"), _($oldstate), _($newstate));
    if ($addcomment != "") $this->doc->AddComment($addcomment);
      
    $err=$this->doc->AddRevision($revcomment);
    if ($err != "") return $err;
    AddLogMsg(sprintf(_("%s new state %s"),$this->doc->title, _($newstate)));
      
    $this->doc->enableEditControl();
    // post action
    if ($tr["m2"] != "") {
      if (! method_exists($this, $tr["m2"])) return (sprintf(_("the method '%s' is not known for the object class %s"), $tr["m2"], get_class($this)));
      $err = call_user_method ($tr["m2"], $this, $newstate);

	  
      if ($err == "->") $err=""; //it is not a real error
      if ($err != "") return (sprintf(_("ChangeState :: the state has been realized but the post method '%s' has the following error %s"), $tr["m2"], $err));
	  
    }
    return ""; // its OK 
  }
  
  
  // --------------------------------------------------------------------
  function GetFollowingStates () {
    // search if following states in concordance with transition array
    if ($this->doc->locked == -1) return array(); // no next state for revised document
    if (($this->doc->locked > 0)&&($this->doc->locked != $this->doc->userid)) return array(); // no next state if locked by another person
      
    $fstate = array();
    if ($this->doc->state == "") $this->doc->state=$this->firstState;
      
    if ($this->userid==1) return $this->getStates(); // only admin can go to any states from anystates
      
    reset($this->cycle);
    while (list($k, $tr) = each($this->cycle)) {
      if ($this->doc->state == $tr["e1"]) {
	// from state OK
	if ($this->control($tr["t"]) == "")
	  $fstate[] = $tr["e2"];
      }
    }
    return $fstate;
  }
  
  
  function getStates() {
    if (! isset($this->states)) {
      $this->states=array();
      reset($this->cycle);
      while (list($k, $tr) = each($this->cycle)) {
	if ($tr["e1"] != "") $this->states[$tr["e1"]]=$tr["e1"];
	if ($tr["e2"] != "") $this->states[$tr["e2"]]=$tr["e2"];
      }
      
    }
    return $this->states;
  }
  

  function changeStateOfDocid($docid, $newstate, $comment="") {
    $cmd= new Doc($this->dbaccess, $docid);
    $cmdid=$cmd->latestId(); // get the latest
    $cmd= new Doc($this->dbaccess,$cmdid );
    
    if ($cmd->wid > 0) {
      $wdoc = new Doc($this->dbaccess,$cmd->wid);

      if (!$wdoc) $err=sprintf(_("cannot change state of document #%d to %s"),$cmd->wid,$newstate);
      if ($err != "")  return $err;
      $wdoc->Set($cmd);      
      $err=$wdoc->ChangeState($newstate,sprintf(_("automaticaly by change state of %s\n%s"),
						$this->doc->title, $comment ));
      if ($err != "")  return $err;
      
    }
    
  }


  
}

?>
