<?php
// ---------------------------------------------------------------
// $Id: Class.WDoc.php,v 1.8 2002/11/26 13:53:46 eric Exp $
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


$CLASS_DOC_PHP = '$Id: Class.WDoc.php,v 1.8 2002/11/26 13:53:46 eric Exp $';

include_once('FDL/Class.Doc.php');

// Work Flow Classe
Class WDoc extends Doc {

  

  var $acls = array("view","edit","delete");

	

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
    }

    function Set(&$doc) {
      $this->doc= &$doc;
      if ($doc->state == "") {
	$doc->state=$this->firstState;
	$this->changeProfil($doc->state);
      }
    }
  

  function changeProfil($newstate) {

    if ($newstate != "") {
      $this->doc->profid=intval($this->getValue($this->attrPrefix."_ID".strtoupper($newstate)));
      if ($this->doc->profid > 0) {
	
	// make sure that the profil is activated
	$pdoc=new Doc($this->dbaccess, $this->doc->profid );

	if ($pdoc->profid == 0) $this->doc->profid = -$this->doc->profid; // inhibition
      }
    }

  }
  function CreateProfileAttribute() {

    if ($this->doctype='C') $cid = $this->id;
    else $cid = $this->fromid;

    $oattr = new DocAttr($this->dbaccess);
    $oattr->docid=$cid;

    // create frame attribute

      $oattr->id=$this->attrPrefix."_FR_PROFIL";
      $oattr->type="frame";
      $oattr->ordered=100;
      $oattr->labeltext=_("state profile");
    $oattr->Add();
      $oattr->frameid=$oattr->id;
      
    $this->getStates();
    reset($this->states);
    while (list($k, $state) = each($this->states)) {
      $aprofilid=$this->attrPrefix."_".strtoupper($state);
      $aidprofilid=$this->attrPrefix."_ID".strtoupper($state);

      // id
      $oattr->visibility="H";
      $oattr->type="text";
      $oattr->id=$aidprofilid;
      $oattr->labeltext=sprintf(_("id %s profile"),_($state));
      $oattr->link="";
      $oattr->phpfile="fdl.php";
      $oattr->phpfunc="gettitle(D,$aidprofilid):$aprofilid";
      $oattr->Add();

      $oattr->ordered++;

      // user comprehensive
      $oattr->visibility="W";
      $oattr->type="enum";
      $oattr->link="%S%app=FREEDOM&action=FREEDOM_CARD&id=%".$aidprofilid."%";
      $oattr->phpfile="fdl.php";
      $oattr->id=$aprofilid;
      $oattr->phpfunc="lprofil(D,{$oattr->id}):$aidprofilid,{$oattr->id}";
      $oattr->labeltext=sprintf(_("%s profile"),_($state));
      $oattr->Add();

      $oattr->ordered++;

    }
    
    
  }
  // --------------------------------------------------------------------
    function ChangeState ($newstate, $addcomment="", $force=false) {
      
      if ($this->doc->state == $newstate) return ""; // no change => no action
	// search if possible change in concordance with transition array
	  $foundFrom = false;
      $foundTo = false;
      reset($this->cycle);
      while (list($k, $trans) = each($this->cycle)) {
	if ($this->doc->state == $trans["e1"]) {
	  // from state OK
	    $foundFrom = true;
	  if ($newstate == $trans["e2"]) {
	    $foundTo = true;
	    $tr = $this->transitions[$trans["t"]];
	    $tname=$trans["t"];
	  }
	  
	}
      }
      
      if (! $foundFrom) return (sprintf(_("ChangeState :: the initial state '%s' is not known"), $this->doc->state));
      if (! $foundTo) return (sprintf(_("ChangeState :: the new state '%s' is not known or is not allowed"), $newstate));
      

      // verify if privilege granted

	$err=$this->control($tname);
      if ($err != "") return $err;

      if (($tr["m1"] != "") && (!$force)) {
	// apply first method (condition for the change)
	  
	  if (! method_exists($this, $tr["m1"])) return (sprintf(_("the method '%s' is not known for the object class %s"), $tr["m1"], get_class($this)));
	
	$err = call_user_method ($tr["m1"], $this, $newstate);
	
	if ($err == "->") return ""; //it is not a real error, but don't change state (reported)
	if ($err != "") return (sprintf(_("ChangeState :: the method '%s' has the following error %s"), $tr["m1"], $err));
	
	
      }
      
      // change the state
	$this->doc->state = $newstate;
      $this->changeProfil($newstate);
      $this->doc->disableEditControl();
      $err = $this->doc->Modify();   // don't control edit permission
      if ($err != "") return $err;
      
      $revcomment = sprintf(_("change state to %s"), _($newstate));
      if ($addcomment != "") $revcomment.= "\n".$addcomment;
      
      $this->doc->AddRevision($revcomment);
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
	
	$fstate = array();
      if ($this->doc->state == "") $this->doc->state=$this->firstState;
      
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
  
}

?>
