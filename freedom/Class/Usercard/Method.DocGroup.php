<?php
/**
 * Specials methods for GROUP family
 *
 * @author Anakeen 2003
 * @version $Id: Method.DocGroup.php,v 1.9 2004/03/01 09:32:43 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */


/**
 * reconstruct mail group & recompute parent group
 *
 * @return string error message, if no error empty string
 * @see Doc::PostModify()
 */
function PostModify() {

  $err=$this->SetGroupMail(); 
  $this->refreshParentGroup();
  return $err;
}

/**
 * recompute only parent group 
 *
 * 
 * @return string error message, if no error empty string
 */
function RefreshGroup() {
  global $refreshedGrpId; // to avoid inifinitive loop recursion
  
  $err="";
  if (!isset($refreshedGrpId[$this->id])) {
    $err=$this->SetGroupMail();
    $err.=$this->modify();
    $refreshedGrpId[$this->id]=true;
    
  }
  return $err;
}

/**
 * compute the mail of the group 
 * concatenation of each user mail and group member mail
 *
 * 
 * @return string error message, if no error empty string
 */
function SetGroupMail($nomail=false) {
  
  $err="";
  $gmail=" ";
  $tmail=array();


  //------------------------------------------------------
  // first compute mail from users members
  $tiduser = $this->getTValue("GRP_IDUSER");
  $tuser = $this->getTValue("GRP_USER");

  if (count($tiduser) > 0) {
   
    while (list($k,$v) = each($tiduser)) {

      $udoc = new doc($this->dbaccess,$v);
      if ($udoc && $udoc->isAlive()) {
	if (!$nomail) {
	  $mail = $udoc->getValue("US_MAIL");
	  
	  if ($mail != "") $tmail[]=$mail;
	}
      } else {
	if ($tuser[$k]!="") $err .= sprintf("%s does not exist",$tuser[$k]);
      }
    }

    $gmail=implode(", ",array_unique($tmail));
  }

  // add mail groups
  //------------------------------------------------------
  // second compute mail from users members
  $tgmemberid=$tiduser; // affiliated members ids
  $tgmember=$tuser; // affiliated members
  $tiduser = $this->getTValue("GRP_IDGROUP");
  if (count($tiduser) > 0) {
   
    while (list($k,$v) = each($tiduser)) {

      $udoc = new doc($this->dbaccess,$v);
      if ($udoc && $udoc->isAlive()) {
	if (!$nomail) {
	  $mail = $udoc->getValue("GRP_MAIL");
	  if ($mail != "") {
	    $tmail1 = explode(",",str_replace(" ", "", $mail));
	    $tmail=array_merge($tmail,$tmail1);
	  }
	}
	$tgmemberid=array_merge($tgmemberid,$udoc->getTValue("GRP_IDUSER"));
	$tgmember=array_merge($tgmember,$udoc->getTValue("GRP_USER"));
      }
    }

    $gmail=implode(", ",array_unique($tmail));
  }

  $tgmembers=array();
  reset($tgmemberid);
  while (list($k,$v) = each($tgmemberid)) {
    $tgmembers[$v]=$tgmember[$k];
  }
 

  $this->SetValue("GRP_IDRUSER", implode("\n",array_keys($tgmembers)));
  $this->SetValue("GRP_RUSER", implode("\n",$tgmembers));
  if (!$nomail) $this->SetValue("GRP_MAIL", $gmail);
  return $err;
}
  
/**
 * recompute parent group and its ascendant
 *
 * @return array/array parents group list refreshed
 * @see RefreshGroup()
 */
function refreshParentGroup() {
  include_once("FDL/freedom_util.php");  
  include_once("FDL/Lib.Dir.php");  

  $sqlfilters[]="in_textlist(grp_idgroup,{$this->id})";
  // $sqlfilters[]="fromid !=".getFamIdFromName($this->dbaccess,"IGROUP");
  $tgroup=getChildDoc($this->dbaccess, 
		      0, 
		      "0", "ALL", $sqlfilters, 
		      1, 
		      "LIST", getFamIdFromName($this->dbaccess,"GROUP"));

  $tpgroup=array();
  $tidpgroup=array();
  while (list($k,$v) = each($tgroup)) {
    $v->RefreshGroup();
    $tpgroup[]=$v->title; 
    $tidpgroup[]=$v->id;
    
  }
  
  $this->SetValue("GRP_PGROUP", implode("\n",$tpgroup));
  $this->SetValue("GRP_IDPGROUP", implode("\n",$tidpgroup));
  return $tgroup;
  
}
?>
?>