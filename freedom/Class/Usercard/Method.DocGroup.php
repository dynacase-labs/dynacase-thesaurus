
// ---------------------------------------------------------------
// $Id: Method.DocGroup.php,v 1.3 2003/08/01 14:53:58 eric Exp $
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
  return $err;
}
 
function RefreshGroup() {
  $err=$this->PostModify();
  $err.=$this->modify();
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
	$err .= sprintf("%s does not exist",$tuser[$k]);
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
  while (list($k,$v) = each($tgmemberid)) {
    $tgmembers[$v]=$tgmember[$k];
  }

  $this->SetValue("GRP_IDRUSER", implode("\n",array_keys($tgmembers)));
  $this->SetValue("GRP_RUSER", implode("\n",$tgmembers));
  $this->SetValue("GRP_MAIL", $gmail);
  return $err;
}
  