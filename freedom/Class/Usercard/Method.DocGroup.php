
// ---------------------------------------------------------------
// $Id: Method.DocGroup.php,v 1.4 2003/08/05 09:12:33 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Usercard/Method.DocGroup.php,v $
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
// construct mail group
// I               
// O               
// I/O             
// Return          
// Date            jun, 04 2003 - 09:39:09
// Author          Eric Brison	(Anakeen)
// --------------------------------------------------------------------------

  


function PostModify() {

  $err=$this->SetGroupMail(); 
   $this->refreshParentGroup();
  return $err;
}
 
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

function SetGroupMail() {
  
  $err="";
  $gmail=" ";
  $tmail=array();
  $tiduser = $this->getTValue("GRP_IDUSER");
  $tuser = $this->getTValue("GRP_USER");
  if (count($tiduser) > 0) {
    $user = new User("",$iduser);
    while (list($k,$v) = each($tiduser)) {

      $udoc = new doc($this->dbaccess,$v);
      if ($udoc && $udoc->isAlive()) {
	$mail = $udoc->getValue("US_MAIL");
	if ($mail != "") $tmail[]=$mail;
      } else {
	if ($tuser[$k]!="") $err .= sprintf("%s does not exist",$tuser[$k]);
      }
    }

    $gmail=implode(", ",array_unique($tmail));
  }

  // add mail groups
  $tgmemberid=$tiduser; // affiliated members
  $tgmember=$tuser; // affiliated members
  $tiduser = $this->getTValue("GRP_IDGROUP");
  if (count($tiduser) > 0) {
    $user = new User("",$iduser);
    while (list($k,$v) = each($tiduser)) {

      $udoc = new doc($this->dbaccess,$v);
      if ($udoc && $udoc->isAlive()) {
	$mail = $udoc->getValue("GRP_MAIL");
	if ($mail != "") {
	  $tmail1 = explode(",",str_replace(" ", "", $mail));
	  $tmail=array_merge($tmail,$tmail1);
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
  $this->SetValue("GRP_MAIL", $gmail);
  return $err;
}
  

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