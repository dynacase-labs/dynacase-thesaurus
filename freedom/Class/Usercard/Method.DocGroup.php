
// ---------------------------------------------------------------
// $Id: Method.DocGroup.php,v 1.2 2003/07/16 08:09:06 eric Exp $
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

function SpecRefresh() {
  $gmail=$this->GetGroupMail();
  $this->SetValue("GRP_MAIL", $gmail);
}
 
function GetGroupMail() {
  
  
  $gmail=" ";
  $tmail=array();
  $tiduser = $this->getTValue("GRP_IDUSER");
  if (count($tiduser) > 0) {
    $user = new User("",$iduser);
    while (list($k,$v) = each($tiduser)) {

      $udoc = new doc($this->dbaccess,$v);
      if ($udoc) {
	$mail = $udoc->getValue("US_MAIL");
	if ($mail != "") $tmail[]=$mail;
      }
    }

    $gmail=implode(", ",$tmail);
  }
  
  return $gmail;
  // add mail groups
  $tiduser = $this->getTValue("GRP_IDGROUP");
  if (count($tiduser) > 0) {
    $user = new User("",$iduser);
    while (list($k,$v) = each($tiduser)) {

      $udoc = new doc($this->dbaccess,$v);
      if ($udoc) {
	$mail = $udoc->getValue("GRP_MAIL");
	if ($mail != "") $tmail[]=$mail;
      }
    }

    $gmail=implode(", ",$tmail);
  }
  
  return $gmail;
}
  