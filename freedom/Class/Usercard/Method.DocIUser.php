<?php
/**
 * User manipulation
 *
 * @author Anakeen 2004
 * @version $Id: Method.DocIUser.php,v 1.16 2004/08/09 08:07:06 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
 /**
 */
var $eviews=array("USERCARD:CHOOSEGROUP");

function SpecRefresh() {
  //  $err=_USER::SpecRefresh();
    $this->AddParamRefresh("US_WHATID","US_MAIL,US_LOGIN,US_GROUP");
    if ($this->getValue("US_IDDOMAIN",1) > 1) $this->AddParamRefresh("US_WHATID","US_DOMAIN");
    $this->AddParamRefresh("US_IDDOMAIN","US_DOMAIN");
    $err="";
    if ($this->getValue("US_STATUS")=='D') $err .= ($err==""?"":"\n")._("user is desactivated");
    // refresh MEID itself
    $this->SetValue("US_MEID",$this->id);
    $iduser = $this->getValue("US_WHATID");
    if ($iduser > 0) {
      $user = $this->getWUser();
      if (! $user->isAffected()) return sprintf(_("user #%d does not exist"), $iduser);
    } else {
      return _("user has not identificator");
    }
    return $err;
}

/**
 * test if the document can be set in LDAP
 */
function canUpdateLdapCard() {
  return  ($this->getValue("US_STATUS")!='D');

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

/**
 * recompute intranet values from USER database
 */
function RefreshDocUser() {

  $err="";
  $wid=$this->getValue("us_whatid");
  if ($wid > 0) { 
    $wuser=$this->getWuser(true);

    if ($wuser->isAffected()) {
      $this->SetValue("US_WHATID",$wuser->id);
      $this->SetValue("US_LNAME",$wuser->lastname);
      $this->SetValue("US_FNAME",$wuser->firstname);
      $this->SetValue("US_PASSWD",$wuser->password);
      $this->SetValue("US_PASSWD1"," ");
      $this->SetValue("US_PASSWD2"," ");
      $this->SetValue("US_LOGIN",$wuser->login);
      $this->SetValue("US_STATUS",$wuser->status);
      $this->SetValue("US_PASSDELAY",$wuser->passdelay);
      $this->SetValue("US_EXPIRES",$wuser->expires);
      $this->SetValue("US_DAYDELAY",$wuser->passdelay/3600/24);
      $this->SetValue("US_IDDOMAIN",$wuser->iddomain);
      include_once("Class.Domain.php");
      $dom = new Domain("",$wuser->iddomain);
      $this->SetValue("US_DOMAIN",$dom->name);
      $this->SetValue("US_MAIL",getMailAddr($wid) );
   
      if ($wuser->passdelay<>0) { 
	$this->SetValue("US_EXPIRESD",strftime("%d/%m/%Y",$wuser->expires));
	$this->SetValue("US_EXPIREST",strftime("%H:%M",$wuser->expires));
      } else  {
	$this->SetValue("US_EXPIRESD"," ");
	$this->SetValue("US_EXPIREST"," ");
      }


      $this->SetValue("US_MEID",$this->id);

      // search group of the user
      $g = new Group("",$wid);

      if (count($g->groups) > 0) {
	foreach ($g->groups as $gid) {
	  $gt=new User("",$gid);
	  $tgid[$gid]=$gt->fid;
	  $tglogin[$gid]=$this->getTitle($gt->fid);
	}
	$this->SetValue("US_GROUP", $tglogin);
	$this->SetValue("US_IDGROUP", $tgid);
      } else {
	$this->SetValue("US_GROUP"," ");
	$this->SetValue("US_IDGROUP"," ");
      }
      $err=$this->modify();
      $err.=$this->RefreshLdapCard();

    } else     {
      $err= sprintf(_("user %d does not exist",$wid));
    }
  }
  
  
  return $err;
}



/**
 * Modify IUSER via Freedom    
 */
function PostModify() {
                
                                                                    
  $uid=$this->GetValue("US_WHATID");
  $lname=$this->GetValue("US_LNAME");
  $fname=$this->GetValue("US_FNAME");
  $pwd1=$this->GetValue("US_PASSWD1");
  $pwd2=$this->GetValue("US_PASSWD2");
  $pwd=$this->GetValue("US_PASSWD");
  if (($pwd1 == "") && ($pwd1==$pwd2) && ($pwd!="")) {$pwd1=$pwd;$pwd2=$pwd;};
  $expires=$this->GetValue("US_EXPIRES");
  $daydelay=$this->GetValue("US_DAYDELAY");
  $passdelay=intval($daydelay)*3600*24;
  $status=$this->GetValue("US_STATUS");
  $login=$this->GetValue("US_LOGIN");

  // compute expire for epoch
  
  $expiresd=$this->GetValue("US_EXPIRESD");
  $expirest=$this->GetValue("US_EXPIREST");
   //convert date 
  $expdate=$expiresd." ".$expirest.":00";
  $expires=0;
  if ($expdate != "") {
	if (ereg("([0-9][0-9])/([0-9][0-9])/(2[0-9][0-9][0-9]) ([0-2][0-9]):([0-5][0-9]):([0-5][0-9])", 
		 $expdate, $reg)) {   
	  $expires=mktime($reg[4],$reg[5],$reg[6],$reg[2],$reg[1],$reg[3]);
	}
      
  }


  $iddomain=$this->GetValue("US_IDDOMAIN");
  $domain=$this->GetValue("US_DOMAIN");

  $fid=$this->id;        
  $user=$this->getWUser();
  if ($user)  $err=$this->setGroups();
  else $user=new User(""); // create new user
  $err.=$user->SetUsers($fid,$lname,$fname,$expires,$passdelay,
		       $login,$status,$pwd1,$pwd2,
		       $iddomain);   
 
  if ($err=="") {
    $this->setValue("US_WHATID",$user->id);
    $this->RefreshDocUser();
    $this->modify(true,array("us_whatid"));
  } 

  


  return $err;

}


function PostDelete() {
  _USER::PostDelete();

  $user=$this->getWUser();
  if ($user) $user->Delete();
                                                                                     
}                                                                                    
                                                                                    
                                                                                      

                                                                                      
function ConstraintPassword($pwd1,$pwd2) {
  $sug=array();     
  if (($pwd1 == "")&&($this->id =="")) {
    $err= _("passwords must not be empty");
  }  else  if ($pwd1<>$pwd2) {
    $err= _("the 2 passwords are not the same");
  }      
                                                                                      
  return array("err"=>$err,
	       "sug"=>$sug);                                                                              
                                                                                  
}

function ConstraintExpires($expiresd,$expirest,$daydelay) {
  $sug=array();
  if (($expiresd<>"") && ($daydelay==0)) {
    $err= _("Expiration delay must not be 0 to keep expiration date");
  }
                                       
  return array("err"=>$err,
	       "sug"=>$sug);
}






?>
