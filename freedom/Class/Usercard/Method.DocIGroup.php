<?php
/**
 * Set WHAT user & mail parameters
 *
 * @author Anakeen 2003
 * @version $Id: Method.DocIGroup.php,v 1.10 2004/02/25 15:50:02 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
 /**
 */


function specRefresh() {
  //  $err=$this->ComputeGroup();
  $this->AddParamRefresh("US_WHATID","GRP_MAIL,US_LOGIN");
  if ($this->getValue("US_IDDOMAIN",1) > 1) $this->AddParamRefresh("US_WHATID","US_DOMAIN");
  return $err;
}

/**
 * recompute only parent group 
 * call {@see ComputeGroup()}
 * 
 * @return string error message, if no error empty string
 */
function RefreshGroup() {
  
  //  $err=_GROUP::RefreshGroup(); 
  $err.=$this->RefreshDocUser();
   $err.=$this->refreshMembers();
   $err.=$this->insertGroups();
   $err.=$this->SetGroupMail();
   $err.=$this->Modify();
   
   //  AddWarningMsg(sprintf("RefreshGroup %d %s",$this->id, $this->title));
  return $err;
}

function PostModify() {
  $uid=$this->GetValue("US_WHATID");
  $gname=$this->GetValue("GRP_NAME");
  $login=$this->GetValue("US_LOGIN");
  $iddomain=$this->GetValue("US_IDDOMAIN");

  $fid=$this->id;        
  $user=new User("",$uid); 
  $err=$user->SetGroups($fid,$gname,
		       $login,
		       $iddomain);   
 
  if ($err=="") {
    $this->setValue("US_WHATID",$user->id);
    $this->modify(true,array("us_whatid"));
    
    // get members 
    $this->insertGroups();
  } 



  if ($err=="") $err="-";
  return $err;
} 

/**
 * update groups table in USER database
 * @return string error message 
 */
function postInsertDoc($docid,$multiple) {

  $err="";
  if ($multiple == false) {
    $gid = $this->getValue("US_WHATID");
    if ($gid > 0) {
      $du = getTDoc($this->dbaccess,$docid);
      $uid = getv($du,"us_whatid");
      if ($uid > 0) {
	$g = new Group("",$uid);
	$g->iduser=$uid;
	$g->idgroup=$gid;
	$err=$g->Add();
	
      }
      
    }    
  }
  return $err;
}
/**
 * update groups table in USER database
 * @return string error message 
 */
function postMInsertDoc($tdocid) {

  $err="";

  $gid = $this->getValue("US_WHATID");
  if ($gid > 0) {

    $g = new Group("",$uid);
    foreach ($tdocid as $k=>$docid) {
      $du = getTDoc($this->dbaccess,$docid);
	
      $uid = getv($du,"us_whatid");
      if ($uid > 0) {
	$g->iduser=$uid;
	$g->idgroup=$gid;
	$err=$g->Add(true);
      }	      
    }     
    $g->PostInsert();
  }
  return $err;
}
/**
 * update groups table in USER database before suppress
 * @return string error message 
 */
function postUnlinkDoc($docid) {
  AddLogMsg("postUnlinkDoc $docid");

  $err="";
    $gid = $this->getValue("US_WHATID");
    if ($gid > 0) {
      $du = getTDoc($this->dbaccess,$docid);
      $uid = getv($du,"us_whatid");
      if ($uid > 0) {
	$g = new Group("",$gid);

	$err=$g->SuppressUser($uid);
	
      }
      
    }    
    return $err;
}
/**
 * (re)insert members of the group in folder from USER databasee
 * 
 * @return string error message, if no error empty string
 */
function insertGroups() { 
  $uid=$this->GetValue("US_WHATID");
  $user=new User("",$uid); 
  $err="";

    
    // get members 
    $tu  = $user->GetUsersGroupList($user->id);
    
    if (is_array($tu)) {
      $this->Clear();
      $tfid=array();
      foreach($tu as $k=>$v) {
	//	if ($v["fid"]>0)  $err.=$this->AddFile($v["fid"]);
	if ($v["fid"]>0) $tfid[]=$v["fid"];	
      }
      $err=$this->InsertMSDocId($tfid,true);// without postInsert
    
    } 



  return $err;
}
/**
 * (re)insert members of the group in folder from USER databasee
 * 
 * @param int $docid user doc parameter
 * @return string error message, if no error empty string
 */
function insertMember($docid) { 
  $err = $this->AddFile($docid,"latest",true); // without postInsert

  return $err;
}

 /**
 * recompute intranet values from USER database
 */
function RefreshDocUser() {  
  $err="";
  $wid=$this->getValue("us_whatid");
  if ($wid > 0) { 
    $wuser=new User("",$wid);
    if ($wuser->isAffected()) {
      $this->SetValue("US_WHATID",$wuser->id);
      $this->SetValue("GRP_NAME",$wuser->lastname);
      //   $this->SetValue("US_FNAME",$wuser->firstname);
      $this->SetValue("US_LOGIN",$wuser->login);
      $this->SetValue("US_IDDOMAIN",$wuser->iddomain);
      include_once("Class.Domain.php");
      $dom = new Domain("",$wuser->iddomain);
      $this->SetValue("US_DOMAIN",$dom->name);
           if ($wuser->iddomain>1) $this->SetValue("GRP_MAIL",getMailAddr($wid) );
   
    
      $this->SetValue("US_MEID",$this->id);

      // search group of the group
      $g = new Group("",$wid);

      if (count($g->groups) > 0) {
	foreach ($g->groups as $gid) {
	  $gt=new User("",$gid);
	  $tgid[$gid]=$gt->fid;
	  $tglogin[$gid]=$this->getTitle($gt->fid);
	}
	$this->SetValue("GRP_PGROUP", $tglogin);
	$this->SetValue("GRP_IDPGROUP", $tgid);
      } else {
	$this->SetValue("GRP_PGROUP"," ");
	$this->SetValue("GRP_IDPGROUP"," ");
      }
      $err=$this->modify();
  
    } else     {
      $err= sprintf(_("group %d does not exist",$wid));
    }
  }
  return $err;
}

/**
 * refresh members of the group from USER database
 */
function refreshMembers() { 

  $wid=$this->getValue("us_whatid");
  if ($wid > 0) { 
    $u = new User("",$wid);

    $tu=$u->GetUsersGroupList($wid);
    if (count($tu) > 0) {

      foreach ($tu as $uid=>$tvu) {
	if ($tvu["isgroup"]=="Y") {
	  $tgid[$uid]=$tvu["fid"];
	  $tglogin[$uid]=$this->getTitle($tvu["fid"]);
	} else {
	  $tuid[$uid]=$tvu["fid"];
	  $tulogin[$uid]=$this->getTitle($tvu["fid"]);
	}
      }
      $this->SetValue("GRP_USER", $tulogin);
      $this->SetValue("GRP_IDUSER", $tuid);
      $this->SetValue("GRP_GROUP", $tglogin);
      $this->SetValue("GRP_IDGROUP", $tgid);
    } else {
      $this->DeleteValue("GRP_USER");
      $this->DeleteValue("GRP_IDUSER");
      $this->DeleteValue("GRP_GROUP");
      $this->DeleteValue("GRP_IDGROUP");
    }
    $err=$this->modify();
  }
}

function ComputeGroup() {
  $err="";
  $this->AddParamRefresh("US_WHATID",
			 "GRP_NAME,GRP_MAIL,US_LOGIN,GRP_USER,GRP_GROUP,GRP_IDUSER,GRP_IDGROUP");

  
  $iduser = $this->getValue("US_WHATID");
  if ($iduser > 0) {
    $user = new User("",$iduser);
    if (! $user->isAffected()) {
      return sprintf(_("Group %s not exist"),$iduser);
    }
   

    // get members 
    $tu  = $user->GetUsersGroupList($user->id);
    $tuid=array();
    $tulogin=array();
    $tgid=array();
    $tglogin=array();
    if (is_array($tu)) {
      while (list($k,$v) = each($tu)) {
	$udoc = getDocFromUserId($this->dbaccess,$k);
	if ($udoc) {
	  if ($v["isgroup"]=="Y") {
	    $tgid[]=$udoc->id;
	    $tglogin[]=$udoc->title;
	  } else {
	    $tuid[]=$udoc->id;
	    $tulogin[]=$udoc->title;
	  }
	}
      }
    }

    if (count($tulogin)==0) {
      $this->SetValue("GRP_USER", " ");
      $this->SetValue("GRP_IDUSER"," ");
    } else {
      $this->SetValue("GRP_USER", implode("\n",$tulogin));
      $this->SetValue("GRP_IDUSER", implode("\n",$tuid));
    }
    if (count($tglogin)==0) {
      $this->SetValue("GRP_GROUP", " ");
      $this->SetValue("GRP_IDGROUP", " ");
    } else {
      $this->SetValue("GRP_GROUP", implode("\n",$tglogin));
      $this->SetValue("GRP_IDGROUP", implode("\n",$tgid));
    }
    // get parent members group
//     $tu  = $user->GetGroupsId();
//     $tgid=array();
//     $tglogin=array();
//     if (is_array($tu)) {
//       while (list($k,$v) = each($tu)) {
// 	$udoc = getDocFromUserId($this->dbaccess,$v);
// 	if ($udoc) {	 
// 	  $tgid[]=$udoc->id;
// 	  $tglogin[]=$udoc->title;	  
// 	}
//       }
//       $this->SetValue("GRP_PGROUP", implode("\n",$tglogin));
//       $this->SetValue("GRP_IDPGROUP", implode("\n",$tgid));
//     }
  
  } 

  return $err;
  
}
  
?>
