<?php
/**
 * User manipulation
 *
 * @author Anakeen 2004
 * @version $Id: Method.DocIUser.php,v 1.12 2004/04/29 08:41:24 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
 /**
 */


function SpecRefresh() {
  //  $err=_USER::SpecRefresh();
    $this->AddParamRefresh("US_WHATID","US_MAIL,US_LOGIN,US_GROUP");
    if ($this->getValue("US_IDDOMAIN",1) > 1) $this->AddParamRefresh("US_WHATID","US_DOMAIN");
    $this->AddParamRefresh("US_IDDOMAIN","US_DOMAIN");
    
    // refresh MEID itself
    $this->SetValue("US_MEID",$this->id);
    $iduser = $this->getValue("US_WHATID");
    if ($iduser > 0) {
      $user = new User("",$iduser);
      if (! $user->isAffected()) return sprintf(_("user #%d does not exist"), $iduser);
    } else {
      return _("user has not identificator");
    }
}


// --------------------------------------------------------------------------
// Set WHAT user & mail parameters
// I               
// O               
// I/O             
// Return          
// Date            jun, 04 2003 - 09:39:09
// Author          Eric Brison	(Anakeen)
// --------------------------------------------------------------------------
function SpecRefresh2() {
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
  
      // refresh groups
 //      $tgid = $wuser->GetGroupsId();
//       foreach ($tgid as $k=>$v) {
// 	$g = new User("",$v);
// 	if ($g->isAffected() && ($g->fid > 0)) {
// 	  $gdoc = new Doc($this->dbaccess, $g->fid);
// 	  $gdoc->RefreshGroup();
// 	}
//      }

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
  $user=new User("",$uid); 
  
  $err=$user->SetUsers($fid,$lname,$fname,$expires,$passdelay,
		       $login,$status,$pwd1,$pwd2,
		       $iddomain);   
 
  if ($err=="") {
    $this->setValue("US_WHATID",$user->id);
    $this->modify(true,array("us_whatid"));
  } 
  $this->SetLdapParam();
  $err=$this->UpdateLdapCard();

  if ($err=="") $err="-";
  return $err;

}


function PostDelete() {
  $uid=$this->GetValue("US_WHATID");
                                                                                     
  if ($uid<>"")
    {
      $user=new User("",$uid);
      $user->Delete();
    }
                                                                                     
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
