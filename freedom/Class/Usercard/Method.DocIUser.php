<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Method.DocIUser.php,v 1.8 2004/02/02 10:34:01 caroline Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: Method.DocIUser.php,v 1.8 2004/02/02 10:34:01 caroline Exp $
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
//  $err=_USER::SpecRefresh();
//  $this->AddParamRefresh("US_WHATID","US_FNAME,US_LNAME,US_MAIL,US_PASSWD,US_LOGIN,US_GROUP");

//Domain >1 can't be updated
if ($this->GetValue("US_IDDOMAIN")>1) {$this->AddParamRefresh("US_WHATID","US_DOMAIN");}

  $tgid=array();
  $tglogin=array();
  $iduser = $this->getValue("US_WHATID");
  if ($iduser > 0) {
    $user = new User("",$iduser);
    if (! $user->isAffected()) return sprintf(_("user #%d does not exist"), $iduser);
    
//    $this->SetValue("US_FNAME", $user->firstname);
//    $this->SetValue("US_LNAME", $user->lastname);
//    $this->SetValue("US_PASSWD", $user->password);
//    $this->SetValue("US_LOGIN", $user->login);
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
  if ($this->id == 0) return array();
  
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

//Modify IUSER via Freedom                                                                                
                                                                                      
function PostModify() {
                                                                                      
$id=$this->GetValue("US_WHATID");
$lname=$this->GetValue("US_LNAME");
$fname=$this->GetValue("US_FNAME");
$pwd1=$this->GetValue("US_PASSWD1");
$pwd2=$this->GetValue("US_PASSWD2");
$expires=$this->GetValue("US_EXPIRES");
$passdelay=$this->GetValue("US_PASSDELAY");
$daydelay=$this->GetValue("US_DAYDELAY");
$status=$this->GetValue("US_STATUS");
$login=$this->GetValue("US_LOGIN");                                                                          
$expiresd=$this->GetValue("US_EXPIRESD");
$expirest=$this->GetValue("US_EXPIREST");

$iddomain=$this->GetValue("US_IDDOMAIN");
$domain=$this->GetValue("US_DOMAIN");

$fid=$this->id;        
$user=new User("",$id);
$user->SetUsers($lname,$fname,$expires,$passdelay,$login,$status,$pwd1,$pwd2,$fid,$expiresd,$expirest,$daydelay,$iddomain,$domain);   
                                                                        
if ($id=="") {$user->Add();}
else {$user->Modify();}

return "Modification effectuée";

}


function PostDelete() {
$id=$this->GetValue("US_WHATID");
                                                                                     
 if ($id<>"")
 {
 $user=new User("",$id);
 $user->Delete();
 }
                                                                                     
}                                                                                    
                                                                                    
                                                                                      
function ConstraintLogin($login)
{
$sug=array();
$id=$this->GetValue("US_WHATID");
$user=new User("",$id);
                                                                                      
if (!ereg("^([a-z]+\.)+[a-z]{1,10}$", $login)) {$err= _("the login syntax is like : john.doe");}

if ($user->iddomain<>"")
 {
  if (!$user->CheckLogin($login,$user->iddomain,$id))
  {$err= _("login deja utilisé");
 }
 }

return array("err"=>$err,"sug"=>$sug);
}
                                                                                      
function ConstraintPassword($pwd1,$pwd2)
{
$sug=array();                                                                                                                                                
if ($pwd1<>$pwd2 or $pwd1=="")
{$err= _("Password erroné");}                                                                     
                                                                                      
return array("err"=>$err,
        "sug"=>$sug);                                                                              
                                                                                  
}

function ConstraintExpires($expiresd,$expirest,$daydelay)
{
$sug=array();
if ($expiresd<>"" and $daydelay==0)
{$err= _("Délai d'expiration différent de 0 pour conserver la date d'expiration");}
                                                                                                                                                        
return array("err"=>$err,
             "sug"=>$sug);
}
?>
