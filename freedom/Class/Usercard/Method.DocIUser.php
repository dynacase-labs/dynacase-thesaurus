
// ---------------------------------------------------------------
// $Id: Method.DocIUser.php,v 1.5 2003/08/12 16:31:54 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Usercard/Method.DocIUser.php,v $
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
function SpecRefresh() {
  $err=_USER::SpecRefresh();
  $this->AddParamRefresh("US_WHATID","US_FNAME,US_LNAME,US_MAIL,US_PASSWD,US_LOGIN,US_GROUP");


  $tgid=array();
  $tglogin=array();
  $iduser = $this->getValue("US_WHATID");
  if ($iduser > 0) {
    $user = new User("",$iduser);
    if (! $user->isAffected()) return sprintf(_("user #%d does not exist"), $iduser);
    
    $this->SetValue("US_FNAME", $user->firstname);
    $this->SetValue("US_LNAME", $user->lastname);
    $this->SetValue("US_PASSWD", $user->password);
    $this->SetValue("US_LOGIN", $user->login);
    $this->SetValue("US_MAIL",getMailAddr($iduser) );
    if ($user->status=='D') $err .= ($err==""?"":"\n")._("user is desactivated");
    // get parent members group
    $tu  = $user->GetGroupsId();

    $tgid=array();
    $tglogin=array();
    if (is_array($tu)) {
      while (list($k,$v) = each($tu)) {
	$udoc = getDocFromUserId($this->dbaccess,$v);
	if ($udoc) {	 
	    $tgid[$udoc->id]=$udoc->id;
	    $tglogin[$udoc->id]=$udoc->title;	  
	}
      }
    }
  }
 
  $tog=$this->GetOtherGroups(); 
  while (list($k,$v) = each($tog)) {
    $tgid[$v["id"]]=$v["id"];
    $tglogin[$v["id"]]=$v["title"];
  }
  $this->SetValue("US_GROUP", implode("\n",$tglogin));
  $this->SetValue("US_IDGROUP", implode("\n",$tgid));

  return $err;
}
  
function GetOtherGroups() {
  include_once("FDL/freedom_util.php");  
  include_once("FDL/Lib.Dir.php");  

  $sqlfilters[]="in_textlist(grp_idruser,{$this->id})";
  // $sqlfilters[]="fromid !=".getFamIdFromName($this->dbaccess,"IGROUP");
  $tgroup=getChildDoc($this->dbaccess, 
		      0, 
		      "0", "ALL", $sqlfilters, 
		      1, 
		      "TABLE", getFamIdFromName($this->dbaccess,"GROUP"));
  
  return $tgroup;
}