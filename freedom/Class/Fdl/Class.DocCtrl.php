<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Class.DocCtrl.php,v 1.10 2003/12/16 15:05:39 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: Class.DocCtrl.php,v 1.10 2003/12/16 15:05:39 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Class.DocCtrl.php,v $
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

$CLASS_DOCFILE_PHP = '$Id: Class.DocCtrl.php,v 1.10 2003/12/16 15:05:39 eric Exp $';



include_once("Class.DbObj.php");
include_once('Class.Application.php');
include_once("FDL/Class.DocPerm.php");

define ("POS_INIT", 0);
define ("POS_VIEW", 1);
define ("POS_EDIT", 2);
define ("POS_DEL",  3);
define ("POS_SEND", 4);
// common part are 0-4 and 7-8
define ("POS_OPEN", 5);
define ("POS_EXEC", 5); // idem OPEN : alias
define ("POS_CONT", 6);
define ("POS_VACL", 7);
define ("POS_MACL", 8);
define ("POS_CREATE", 5);
// 7 up 11 undefined for the moment

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
					 "description"        =>"create doc") #  N_("create doc")
			
			);

  // --------------------------------------------------------------------
  function DocCtrl ($dbaccess='', $id='',$res='',$dbid=0) {
    // --------------------------------------------------------------------

    global $action; // necessary to see information about user privilege

    if (isset($action)) {
      $this->userid=$action->parent->user->id;
    }
    DbObj::DbObj($dbaccess, $id,$res,$dbid);      
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
  function SetControl() {
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
      // reactivation of doc with its profil
      $this->exec_query("update doc set profid=-profid where profid=-".$this->id." and locked != -1;");
  }

  /**
   * set profil for document
   *
   * @param int profid identificator for profil document
   */
  function setProfil($profid) {

    $this->profid = $profid;
    if (($profid > 0) && ($profid != $this->id)) {
      // make sure that the profil is activated
      $pdoc=new Doc($this->dbaccess, $profid);
      if ($pdoc->profid == 0) $this->profid = -$profid; // inhibition
    }
  }

  /**
   * set control view for document
   *
   * @param int cvid identificator for control view document
   */
  function setCvid($cvid) {

    $this->cvid = $cvid;
  }
  // --------------------------------------------------------------------
  function ControlId ($docid,$aclname) {
    // --------------------------------------------------------------------     
    
    if (! isset($this->uperm)) {
      
      $perm = new DocPerm($this->dbaccess, array($docid,$this->userid));

      if ($perm -> IsAffected()) $this->uperm = $perm->uperm;
      else $this->uperm = $perm->getUperm($docid,$this->userid);
            
    }
    if (isset($this->dacls[$aclname])) {
      return (($this->uperm & (1 << ($this->dacls[$aclname]["pos"] ))) != 0 )?"":sprintf(_("no privilege %s"),$aclname);
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


  function parseMail($Email) {


    $sug=array(); // suggestions
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

    return array("err"=>$err,
		 "sug"=>$sug);

  }
  /** 
   * return true if the date is in the future
   * @param string date date JJ/MM/AAAA
   */
  function isFutureDate($date) {

    $err="";
    $sug=array(); // suggestions
    if (! ereg("^[0-9]{2}/[0-9]{2}/[0-9]{4}$", $date)) {       
      $err= _("the date syntax must be like : DD/MM/AAAA");

    } else {

      list($dd,$mm,$yy) = explode("/",$date);
      $yy = intval($yy);
      $mm = intval($mm); 
      $dd = intval($dd); 
      $ti = mktime(0,0,0,$mm,$dd+1,$yy);
      if ($ti < time()) {  
	$err= sprintf(_("the date %s is in the past: today is %s"),
		      date ("d/m/Y",$ti),
		      date ("d/m/Y",time()));
		      
	
      } 
    }

    return array("err"=>$err,
		 "sug"=>$sug);

  }

}

?>