<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Method.DocIGroup.php,v 1.9 2004/02/09 16:43:56 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: Method.DocIGroup.php,v 1.9 2004/02/09 16:43:56 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Usercard/Method.DocIGroup.php,v $
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




// --------------------------------------------------------------------------
// Set WHAT user & mail parameters
// I               
// O               
// I/O             
// Return          
// Date            jun, 04 2003 - 09:39:09
// Author          Eric Brison	(Anakeen)
// --------------------------------------------------------------------------

function specRefresh() {
  $err=$this->ComputeGroup();
  return $err;
}

/**
 * recompute only parent group 
 * call {@see ComputeGroup()}
 * 
 * @return string error message, if no error empty string
 */
function RefreshGroup() {
  
  $err=_GROUP::RefreshGroup(); 
  $err.=$this->ComputeGroup();
  return $err;
}

function PostModify() {

  $err=_GROUP::PostModify(); 
  $err.=$this->ComputeGroup();
  return $err;
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
    $this->SetValue("GRP_NAME", chop($user->firstname." ".$user->lastname));
    $this->SetValue("US_LOGIN", $user->login);
    $this->SetValue("GRP_MAIL",getMailAddr($iduser) );

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
