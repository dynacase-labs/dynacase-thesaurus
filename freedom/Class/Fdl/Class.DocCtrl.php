<?php
/**
 * Control Access Document
 *
 * @author Anakeen 2002
 * @version $Id: Class.DocCtrl.php,v 1.25 2005/08/18 09:19:27 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 */
 /**
 */





include_once("Class.DbObj.php");
include_once("FDL/Class.DocPerm.php");
include_once("FDL/Class.VGroup.php");

define ("POS_INIT", 0);
define ("POS_VIEW", 1);
define ("POS_EDIT", 2);
define ("POS_DEL",  3);
define ("POS_SEND", 4);
// common part are 0-4 and 7-8
define ("POS_OPEN", 5);
define ("POS_EXEC", 5); // idem OPEN : alias
define ("POS_CONT", 6); // view containt
define ("POS_VACL", 7);
define ("POS_MACL", 8);
define ("POS_ULCK", 9);
define ("POS_CONF", 10); // confidential

// family profil
define ("POS_CREATE", 5);
define ("POS_ICREATE", 6);
//  11 undefined for the moment

define ("POS_WF", 12); // begin of workflow privilege definition 
// end of privilege is 31 : (coded on 32bits)
/**
 * Control Access Document Class
 * @package FREEDOM
 *
 */
Class DocCtrl extends DbObj
{ 
  var $isCacheble= false;
  // --------------------------------------------------------------------
  //---------------------- OBJECT CONTROL PERMISSION --------------------
  
  // access privilege definition 
  var $dacls = array (
			"init" =>array("pos"		=>POS_INIT,
					"description"	=>"control initialized"), 
			      
			"view" =>array("pos"		=>POS_VIEW,  # N_("view document")
				       "description"	=>"view document"), 	#  N_("view")      
			"send" =>array("pos"               =>POS_SEND, # N_("send document") 
				       "description"        =>"send document"), # N_("send")
			"edit" =>array("pos"               =>POS_EDIT, # N_("edit document")
					 "description"        =>"edit document"), #  N_("edit")  
			"delete"  =>array("pos"               =>POS_DEL, # N_("delete document")
					 "description"        =>"delete document"),#  N_("delete")  
			"open" =>array("pos"               => POS_OPEN, # N_("open folder")
					 "description"        =>"open folder"),#  N_("open")
			"execute" =>array("pos"               =>POS_EXEC, # N_("execute search")
					 "description"        =>"execute search"),#  N_("execute")

			"modify" =>array("pos"               =>POS_CONT, # N_("modify folder")
					 "description"        =>"modify folder"),#  N_("modify")

			"viewacl" =>array("pos"               =>POS_VACL, # N_("view acl")
					 "description"        =>"view acl"),#  N_("viewacl")

			"modifyacl" =>array("pos"               =>POS_MACL, # N_("modify acl")
					 "description"        =>"modify acl"), #  N_("modifyacl")
			"create" =>array("pos"               =>POS_CREATE, # N_("modify acl")
					 "description"        =>"create doc"), #  N_("create doc")
			"unlock" =>array("pos"               =>POS_ULCK, # N_("unlock")
					 "description"        =>"unlock unowner locked doc"), #  N_("unlock unowner locked doc")
			"icreate" =>array("pos"               =>POS_ICREATE, # N_("icreate")
					 "description"        =>"create doc manually"), #  N_("create doc manually")
			"confidential" =>array("pos"               =>POS_CONF, # N_("confidential")
					 "description"        =>"view confidential") #  N_("view confidential")
			
			);

  // --------------------------------------------------------------------
  function __construct($dbaccess='', $id='',$res='',$dbid=0) {
    // --------------------------------------------------------------------

    global $action; // necessary to see information about user privilege

    if (isset($action)) {
      $this->userid=$action->parent->user->id;
    }
    parent::__construct($dbaccess, $id,$res,$dbid);      
  }

  function isControlled() {
    return ($this->profid != 0) ;
  }

  function UnsetControl() {
    if ($this->id == $this->profid) {
      // inhibated all doc references this profil
      $this->exec_query("update doc set profid=-profid where profid=".$this->id." and locked != -1;");
    }
      $this->profid = "0";      
      $this->modify();
  }
  /**
   * activate access specific control 
   * @param bool $userctrl if true add all acls for current user
   */
  function SetControl($userctrl=true) {
    if ($userctrl) {
      $perm = new DocPerm($this->dbaccess, array($this->id,$this->userid));
      $perm->docid=$this->id;
      $perm->userid=$this->userid;
      $perm->upacl= -2; // all privileges
      $perm->unacl=0;
      $perm->cacl=0;
      if (! $perm -> IsAffected()) {
	// add all privileges to current user
	$perm->Add();
      } else {
	$perm->Modify();
      }
    }
    // reactivation of doc with its profil
    $this->exec_query("update doc set profid=-profid where profid=-".$this->id." and locked != -1;");
  }

  /**
   * set profil for document
   *
   * @param int profid identificator for profil document
   */
  function setProfil($profid, $fromdocidvalues=0) {

    $this->profid = $profid;
    if (($profid > 0) && ($profid != $this->id)) {
      // make sure that the profil is activated
      $pdoc=new_Doc($this->dbaccess, $profid);
      if ($pdoc->getValue("DPDOC_FAMID") > 0) {
	// dynamic profil
	$this->dprofid = $profid;
	$this->computeDProfil($this->dprofid,$fromdocidvalues);
	unset($this->uperm); // force recompute privileges
      }
      if ($pdoc->profid == 0) $this->profid = -$profid; // inhibition
    }
  }

  /**
   * reset right for dynamic profil
   *
   * @param int dprofid identificator for dynamic profil document
   */
  function computeDProfil($dprofid=0,$fromdocidvalues=0) {
    if ($this->id == 0) return;
    if ($dprofid == 0) $dprofid=$this->dprofid;
    if ($dprofid == 0) return;
    
    $pdoc=new_Doc($this->dbaccess, $dprofid);
    $pfamid=  $pdoc->getValue("DPDOC_FAMID");
    if ($pfamid > 0) {
      if ($this->profid != $this->id) {
	$this->profid = $this->id; //private profil
	$this->modify(true,array("profid"));
      }

      $query=new QueryDb($this->dbaccess,"DocPerm");
      $query->AddQuery("docid=".$pdoc->id);
      $tacl=$query->Query(0,0,"TABLE");
      if (! is_array($tacl)) {
	//	print "err $tacl";
	$tacl=array();
      }
      $tgnum=array(); // list of virtual user/group
      foreach ($tacl as $v) {
	if ($v["userid"] >= STARTIDVGROUP) {
	  $tgnum[]=$v["userid"];	  		  
	}
      }
      if (count($tgnum)>0) {
	$query=new QueryDb($this->dbaccess,"VGroup");
	$query->AddQuery(GetSqlCond($tgnum,"num",true));
	$tg=$query->Query(0,0,"TABLE");
	if ($query->nb>0) {
	  foreach ($tg as $vg) {
	    $tnum[$vg["num"]]=$vg["id"];
	  }
	}
      }
      $this->exec_query("delete from docperm where docid=".$this->id);

      if ($fromdocidvalues==0) $fromdocidvalues=&$this;
      foreach ($tacl as $v) {
	if ($v["userid"] <STARTIDVGROUP) {
	  $uid=$v["userid"];
	  //  $vupacl[$uid]=$v["upacl"];
	  //$vunacl[$uid]=$v["unacl"];
	} else {
	  $aid=$tnum[$v["userid"]];
	  $duid=$fromdocidvalues->getValue($aid);
	  if ($duid == "") $duid=$fromdocidvalues->getParamValue($aid);
	  if ($duid > 0) {
	    $docu=getTDoc($fromdocidvalues->dbaccess,intval($duid)); // not for idoc list for the moment
	    $uid=$docu["us_whatid"];
	    //print "<br>$aid:$duid:$uid";
	  }
	    
	}
	  // add right in case of multiple use of the same user : possible in dynamic profile
	  $vupacl[$uid]=(intval($vupacl[$uid]) | intval($v["upacl"]));
	  $vunacl[$uid]=(intval($vunacl[$uid]) | intval($v["unacl"]));
	  
	
	if ($uid>0) {
	    $perm = new DocPerm($this->dbaccess, array($this->id,$uid));
	    $perm->cacl="0";
	    $perm->upacl=$vupacl[$uid];
	    $perm->unacl=$vunacl[$uid];

	    //print "<BR>$uid : ".$this->id."/".$perm->upacl;
	    if ($perm -> isAffected()) $err=$perm ->modify();
	    else $err=$perm->Add();


	}
      }
 
      
    }
  }

  /**
   * add control for a specific user
   *
   * @param int uid user identificator 
   * @param string $aclname name of the acl (edit, view,...)
   */
  function AddControl($uid,$aclname) {
    if (! isset($this->dacls[$aclname])) {
      return sprintf(_("unknow privilege %s"),$aclname);
    }    
    $pos=$this->dacls[$aclname]["pos"];
    if (! is_numeric($uid)) {
      // logical name
      $vg = new VGroup($this->dbaccess,strtolower($uid));
      if (! $vg->isAffected()) {
	// try to add 
	$ddoc=new_Doc($this->dbaccess, $this->getValue("dpdoc_famid"));
	$oa=$ddoc->getAttribute($uid);
	if ($oa->type=="docid") {
	  $vg->id=$oa->id;
	  $vg->Add();
	  $uid=$vg->num;	  
	} else $err=sprintf(_("unknow virtual user identificateur %s"),$uid);
      } else {
	$uid=$vg->num;
      }
    }

    if ($uid > 0) {      
      $perm = new DocPerm($this->dbaccess, array($this->id,$uid));
      $perm->SetControlP($pos);
      if ($perm->isAffected()) $err=$perm->modify();
      else {
	$err=$perm->Add();
      }
    } 
    return $err;
  }

  /**
   * set control view for document
   *
   * @param int cvid identificator for control view document
   */
  function setCvid($cvid) {

    $this->cvid = $cvid;
  }

  /**
   * use to know if current user has access privilege
   *
   * @param int $docid profil identificator
   * @param string $aclname name of the acl (edit, view,...)
   * @return string if empty access granted else error message
   */
  function ControlId ($docid,$aclname) {
    
    if (! isset($this->uperm)) {
      
      $perm = new DocPerm($this->dbaccess, array($docid,$this->userid));

      if ($perm -> IsAffected()) $this->uperm = $perm->uperm;
      else $this->uperm = $perm->getUperm($docid,$this->userid);
            
    }
   
    return $this->ControlUp($this->uperm,$aclname);
  }



  /**
   * use to know if permission has access privilege
   *
   * @param int $uperm permission mask
   * @param string $aclname name of the acl (edit, view,...)
   * @return string if empty access granted else error message
   */
  function ControlUp($uperm, $aclname) {
    if (isset($this->dacls[$aclname])) {
      return (($uperm & (1 << ($this->dacls[$aclname]["pos"] ))) != 0 )?"":sprintf(_("no privilege %s"),$aclname);
    } else {
      return sprintf(_("unknow privilege %s"),$aclname);
    }
    
  }


//   // --------------------------------------------------------------------
//   function ControlUserId ($userid,$aclname) {
//     // --------------------------------------------------------------------     
        
//     if (isset($this->dacls[$aclname])) {
      
//       $perm = new DocPerm($this->dbaccess, array($this->id,$userid));

//       if ($perm -> IsAffected()) $uperm = $perm->uperm;
//       else $uperm = $perm->getUperm($this->id,$userid);
      
//       return (($uperm & (1 << ($this->dacls[$aclname]["pos"] ))) != 0)?"":sprintf(_("no privilege %s"),$aclname);
//     } else {
//       return sprintf(_("unknow privilege %s"),$aclname);
//     }
//   }


  static public function parseMail($Email) {   
    $sug=array(); // suggestions
    $err="";

    if ($Email != "") {
      if ($Email[0] == "<") {
	$sug[]=_("<it's a message>");
      } else {      
	if (ereg("^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,6}$", $Email)) {      
	  return true;
	}
	$err= _("the email syntax  is like : john.doe@anywhere.org");
    
	if (eregi("^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,6}$", $Email)) {      
	  $sug[]=strtolower($Email);
	  $err="";
	}
	if (! ereg("@", $Email)) {      
	  $err= _("the email must containt the @ character");
	}
      }
    }
    return array("err"=>$err,
		 "sug"=>$sug);

  }
  /** 
   * return true if the date is in the future (one day after at less)
   * @param string date date JJ/MM/AAAA
   */
  static public function isFutureDate($date) {

    $err="";
    $sug=array(); // suggestions
    if ($date != "") {
      if (! ereg("^[0-9]{2}/[0-9]{2}/[0-9]{4}", $date)) {       
	$err= _("the date syntax must be like : DD/MM/AAAA");

      } else {

	list($dd,$mm,$yy) = explode("/",$date);
	$yy = intval($yy);
	$mm = intval($mm); 
	$dd = intval($dd); 
	$ti = mktime(0,0,0,$mm,$dd+1,$yy);
	if ($ti < time()) {  
	  $err= sprintf(_("the date %s is in the past: today is %s"),
			date ("d/m/Y", mktime(0,0,0,$mm,$dd,$yy)),
			date ("d/m/Y",time()));
	  $sug[]=date ("d/m/Y",time());
		      
	
	} 
      }
    }
    return array("err"=>$err,
		 "sug"=>$sug);
  }
/** 
   * return true if user can execute the specified action
   * @param string $appname application name
   * @param string $actname action name
   * @return bool
   *
   */
  static public function canExecute($appname,$actname) {
    global $action;
    
    return $action->canExecute($appname,$actname);
  }

}

?>