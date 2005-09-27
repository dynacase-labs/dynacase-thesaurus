<?php
/**
 * Workflow Class Document
 *
 * @author Anakeen 2002
 * @version $Id: Class.WDoc.php,v 1.49 2005/09/27 13:37:16 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */





include_once('FDL/Class.Doc.php');

/**
 * WorkFlow Class
 */
Class WDoc extends Doc {

  
  /**
   * WDoc has its own special access depend on transition
   * by default the three access are always set
   *
   * @var array
   */
  var $acls = array("view","edit","delete");

	

  var $usefor='W';
  var $defDoctype='W';
  var $defClassname='WDoc';
  var $attrPrefix="WF"; // prefix attribute
  // --------------------------------------------------------------------
  //----------------------  TRANSITION DEFINITION --------------------
  var $transitions = array();// set by childs classes
  var $cycle = array();// set by childs classes
  var $autonext = array();// set by childs classes
  var $firstState=""; // first state in workflow
  var $viewnext="list"; // view interface as select list may be (list|button)
  var $nosave=array(); // states where it is not permitted to save and stay (force next state)
  function __construct($dbaccess='', $id='',$res='',$dbid=0) {
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
    DocCtrl::__construct($dbaccess, $id, $res, $dbid);

    $this->postConstructor();
  }

  function Set(&$doc) {
    if ((! isset($this->doc)) || ($this->doc->id != $doc->id) ) {
      $this->doc= &$doc;
      if (($doc->doctype!='C')&&($doc->state == "")) {	
	$doc->state=$this->firstState;
	$this->changeProfil($doc->state);
      }
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

  
      
    $ordered=1000;

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
      $oattr->phpfunc="lmask(D,{$oattr->id},WF_FAMID):$aidmaskid,{$oattr->id}";
      $oattr->labeltext=sprintf(_("%s mask"),_($state));
      if ($oattr->isAffected()) $oattr->Modify();
      else $oattr->Add();

    }
    refreshPhpPgDoc($this->dbaccess, $cid);
   
    
  }
  /**
   * change state of a document
   * the method {@link set()} must be call before
   * @param string $newstate the next state
   * @param string $comment comment to be set in history (describe why change state)
   * @param bool $force is true when it is the second passage (without interactivity)
   * @param bool $withcontrol set to false if you want to not verify control permission ot transition
   * @param bool $wm1 set to false if you want to not apply m1 methods
   * @param bool $wm2 set to false if you want to not apply m2 methods
   * @return string error message, if no error empty string
   */
  function ChangeState ($newstate, $addcomment="", $force=false,$withcontrol=true,$wm1=true,$wm2=true) {
      
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
      
    if  ($this->userid!=1) {// admin can go to any states 

      if (! $foundTo) return (sprintf(_("ChangeState :: the new state '%s' is not known or is not allowed from %s"), _($newstate),_($this->doc->state)));
      if (! $foundFrom) return (sprintf(_("ChangeState :: the initial state '%s' is not known"), _($this->doc->state)));
    }
    // verify if completed doc
    $err = $this->doc->isCompleteNeeded();
    if ($err != "") return $err;

    // verify if privilege granted

    if ($withcontrol) $err=$this->control($tname);
    if ($err != "") return $err;

    if ($wm1 && ($tr["m1"] != ""))  {
      // apply first method (condition for the change)
	  
      if (! method_exists($this, $tr["m1"])) return (sprintf(_("the method '%s' is not known for the object class %s"), $tr["m1"], get_class($this)));
	
      $err = call_user_method ($tr["m1"], $this, $newstate,$this->doc->state,$addcomment);
	
      if ($err == "->") {
	if ($force) {
	  $err=""; // it is the return of the report	    
	  SetHttpVar("redirect_app",""); // override the redirect
	  SetHttpVar("redirect_act","");
	} else {
	  if ($addcomment != "") $this->doc->AddComment($addcomment); // add comment now because it will be lost 
	  return ""; //it is not a real error, but don't change state (reported)
	}
      }
      if ($err != "") return (sprintf(_("The change state to %s has been aborted.\n%s"), 
				      _($newstate), $err));
	
	
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
    if (isset($tr["ask"])) {
      foreach ($tr["ask"] as $vpid) {
	$pv=$this->getValue($vpid);
	if ($pv != "") {
	  $oa=$this->getAttribute($vpid);
	  $revcomment.="\n-".$oa->labelText.":".$pv;
	}
      }
    }
    
    $err=$this->doc->AddRevision($revcomment);
    if ($err != "") return $err;
    AddLogMsg(sprintf(_("%s new state %s"),$this->doc->title, _($newstate)));
      
    $this->doc->enableEditControl();
    // post action
    if ($wm2 && ($tr["m2"] != "")) {
      if (! method_exists($this, $tr["m2"])) return (sprintf(_("the method '%s' is not known for the object class %s"), $tr["m2"], get_class($this)));
      $err = call_user_method ($tr["m2"], $this, $newstate,$oldstate,$addcomment);

	  
      if ($err == "->") $err=""; //it is not a real error
      if ($err != "") return (sprintf(_("The change state to %s has been realized. But the following warning is appeared.\n%s"),  _($newstate), $err));
	  
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
    $cmd= new_Doc($this->dbaccess, $docid);
    $cmdid=$cmd->latestId(); // get the latest
    $cmd= new_Doc($this->dbaccess,$cmdid );
    
    if ($cmd->wid > 0) {
      $wdoc = new_Doc($this->dbaccess,$cmd->wid);

      if (!$wdoc) $err=sprintf(_("cannot change state of document #%d to %s"),$cmd->wid,$newstate);
      if ($err != "")  return $err;
      $wdoc->Set($cmd);      
      $err=$wdoc->ChangeState($newstate,sprintf(_("automaticaly by change state of %s\n%s"),
						$this->doc->title, $comment ));
      if ($err != "")  return $err;
      
    }
    
  }

  /**
   * get transition array for the transition between $to and $from states
   * @param string $to first state
   * @param string $from next state
   * @return array transition array (false if not found)
   */
  function getTransition($from,$to) {
    foreach ($this->cycle as $v) {
      if (($v["e1"] == $from) && ($v["e2"] == $to)) {
	$t=$this->transitions[$v["t"]];
	$t["id"]=$v["t"];
	return $t;
      }
    }
    return false;
  }

  function DocControl($aclname) {
    return Doc::Control($aclname);
  }

  /**
   * Special control in case of dynamic controlled profil
   */
  function Control($aclname) {
    $err= Doc::Control($aclname);
    if ($err == "") return $err; // normal case

    if ($this->getValue("DPDOC_FAMID") > 0) {
      // special control for dynamic users
      if (! isset($this->pdoc)) {
	$pdoc = createDoc($this->dbaccess,$this->fromid,false);
	$pdoc->doctype="T"; // temporary
	//	$pdoc->setValue("DPDOC_FAMID",$this->getValue("DPDOC_FAMID"));
	$err=$pdoc->Add();
	if ($err != "") return "WDoc::Control:".$err; // can't create profil
	$pdoc->setProfil($this->profid, $this->doc);

	$this->pdoc = &$pdoc;
      }


      $err=$this->pdoc->DocControl($aclname);

    }
    return $err;
  }
  
}

?>
