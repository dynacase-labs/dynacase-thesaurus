<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Method.DocUser.php,v 1.25 2004/07/08 08:48:02 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: Method.DocUser.php,v 1.25 2004/07/08 08:48:02 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Usercard/Method.DocUser.php,v $
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



  
  
  // LDAP parameters
  var $serveur;
  var $port ;
  var $racine;
  var $rootdn;
  var $rootpw;
  var $dbaccess;
  var $action;
  
  var $defaultabstract= "USERCARD:VIEWABSTRACTCARD";
  
   
  var $cviews=array("USERCARD:VIEWABSTRACTCARD");
// -----------------------------------
   function viewabstractcard($target="finfo",$ulink=true,$abstract="Y") {
     // -----------------------------------
     //     doc::viewabstractcard($target,$ulink,$abstract);
     $this->viewprop($target,$ulink,$abstract);
     $this->viewattr($target,$ulink,$abstract);
   }


   

 

  // no in postUpdate method :: call this only if real change (values)
function PostModify() {
  $priv=$this->GetValue("US_PRIVCARD");
  $err="";

  $this->SetLdapParam();
  if ($this->useldap) {
    // update LDAP only no private card
    if (($priv == 'R') || ($priv == 'W')) {

      $err=$this->UpdateLdapCard();
    } else if ($priv == 'P') {
      $this->SetLdapParam();
      $err=$this->DeleteLdapCard();
    }
  }
  $this->SetPrivacity(); // set doc properties in concordance with its privacity

  return ($err);
}

function SpecRefresh() {

  
  // " gettitle(D,US_IDSOCIETY):US_SOCIETY,US_IDSOCIETY";
  $this->refreshDocTitle("US_IDSOCIETY","US_SOCIETY");


  
  $this->AddParamRefresh("US_IDSOCIETY,US_SOCADDR","US_WORKADDR,US_WORKTOWN,US_WORKPOSTALCODE,US_WORKWEB,US_WORKCEDEX,US_COUNTRY,US_SPHONE,US_SFAX");
  $this->AddParamRefresh("US_IDSOCIETY","US_SCATG,US_JOB");

  $doc=new Doc($this->dbaccess, $this->getValue("US_IDSOCIETY"));
  if ($doc->isAlive()) {
    if ($this->getValue("US_SOCADDR") != "") {
      $this->setValue("US_WORKADDR",$doc->getValue("SI_ADDR"," "));
      $this->setValue("US_WORKTOWN",$doc->getValue("SI_TOWN"," "));
      $this->setValue("US_WORKPOSTALCODE",$doc->getValue("SI_POSTCODE"," "));
      $this->setValue("US_WORKWEB",$doc->getValue("SI_WEB"," "));
      $this->setValue("US_WORKCEDEX",$doc->getValue("SI_CEDEX"," "));
      $this->setValue("US_COUNTRY",$doc->getValue("SI_COUNTRY"," "));
    }
    $this->setValue("US_SCATG",$doc->getValue("SI_CATG"));
    $this->setValue("US_JOB",$doc->getValue("SI_JOB"));


    $this->setValue("US_PHONE",$this->getValue("US_PPHONE",$doc->getValue("SI_PHONE")));
    $this->setValue("US_FAX",$this->getValue("US_PFAX",$doc->getValue("SI_FAX")));

  } else {
    $this->setValue("US_PHONE",$this->getValue("US_PPHONE"," "));
    $this->setValue("US_FAX",$this->getValue("US_PFAX"," "));
    
  }
  
}

  // --------------------------------------------------------------------
  function SetLdapParam() {
  // --------------------------------------------------------------------
    global $action;
    $this->serveur=$action->GetParam("LDAP_SERVEUR");
    $this->port=   $action->GetParam("LDAP_PORT");
    $this->racine= $action->GetParam("LDAP_ROOT");
    $this->rootdn= $action->GetParam("LDAP_ROOTDN");
    $this->rootpw= $action->GetParam("LDAP_ROOTPW");
    $this->useldap= ($action->GetParam("LDAP_ENABLED","no") == "yes");

    $this->action = $action;
  }

/**
 * init society organization of the tree
 * @return bool true if organization has been created or its already created
 */
function OrgInit() {
    if (! $this->useldap) false;
  

	    // ------------------------------
	    // include LDAP organisation first
	    $orgldap["objectclass"]="organization";
	    if (ereg(".*o=(.*),.*", $this->racine, $reg))
	      $orgldap["o"]=$reg[1]; // get organisation from LDAP_ROOT
	    else
		$orgldap["o"]="unknown";

	    $dn=$this->racine;
	    $ds=ldap_connect($this->serveur,$this->port);
	    
	    if ($ds) {
	      ldap_set_option($ds,LDAP_OPT_PROTOCOL_VERSION,3);
	      if (ldap_bind($ds, $this->rootdn, $this->rootpw)) {
		
		if ((@ldap_search($ds, $dn, "", array()))  || 
		    (ldap_add($ds, $dn, $orgldap))) {
		  
		  return true;
		}
	      }
	    }
	    return false;
}


  // --------------------------------------------------------------------
  function ModifyLdapCard( $infoldap, $objectclass="inetOrgPerson") {
  // --------------------------------------------------------------------

    if (! $this->useldap) return;
    $retour = "";
    if ($this->serveur != "")
      {

	if ($this->OrgInit()) {
	  
	// ------------------------------
	// update LDAP values
	
	$infoldap["objectclass"]=$objectclass;
	if (! isset($ds)) {
	  $ds=ldap_connect($this->serveur,$this->port);
	}

	if ($ds)
	  {
	    ldap_set_option($ds,LDAP_OPT_PROTOCOL_VERSION,3);
	    $dn = "cn=".$this->id.",".$this->racine;

	    if (ldap_bind($ds, $this->rootdn, $this->rootpw))
	      {

		$sr = @ldap_search($ds, $dn, "", array());
		if ( $sr )
		
		  {
		    // to modify need to delete and then add
		    // the ldap_modify function cannot perform
		    // add and replace attribute in all configuration
		    //ldap_modify($ds, $dn, $infoldap);
		  
		    ldap_delete($ds, $dn);


		  }


	      }
	    if (! @ldap_add($ds, $dn, $infoldap))     $retour = _("errldapadd");	    
	    ldap_close($ds);
	  }
	else
	  {	    
	      $retour = _("errldapconnect");
	  }
	} else {
	      $retour = _("errldaporginit");
	  
	}
      }
    
    return $retour;
  }
  // --------------------------------------------------------------------
  function PostDelete()    
  // --------------------------------------------------------------------
    {
      $this->SetLdapParam();
      $this->DeleteLdapCard();
    }
  // --------------------------------------------------------------------
  function DeleteLdapCard()
  // --------------------------------------------------------------------
    {

      if (! $this->useldap) return;
     
 
      if (($this->serveur != "") && ($this->id > 0)) {
	$ds=ldap_connect($this->serveur,$this->port);

	if ($ds)  {
	    
	  ldap_set_option($ds,LDAP_OPT_PROTOCOL_VERSION,3);
	  if (ldap_bind($ds, $this->rootdn, $this->rootpw)) {
	     $r=@ldap_delete($ds,"cn=".$this->id.",".$this->racine);
	  }
	  
	  
	  ldap_close($ds);
	}
      }
      
    } 

/**
 * test if the document can be set in LDAP
 */
function canUpdateLdapCard() {
  $priv=$this->GetValue("US_PRIVCARD");
  if ($priv == "P") return false;
  return true;
}

/**
 * update LDAP card from user document
 */
  function UpdateLdapCard()    {
      include_once("FDL/Class.UsercardLdif.php");
      if (! $this->useldap) return false;
      if (! $this->canUpdateLdapCard()) return false;

      $oldif=new UsercardLdif();
      $infoldap=array();
	      
      $infoldap["cn"]=utf8_encode($this->title);
      $values=$this->GetValues();

      foreach($values as $k=>$v) {


	$lvalue=$v;
	  //print $i.":".$lvalue."<BR>";
	  if ($lvalue != "")
	    {

	      // create attributes to LDAP update
	      $oattr=$this->GetAttribute($k);
	    
	      $ldapattr = array_search(strtoupper($k),$oldif->import);

	      // particularity for URI need http://
	      if ($k == "us_workweb") $lvalue="http://".$lvalue;
	      if ($k == "us_passwd") $lvalue="{CRYPT}".$lvalue;
	    
	      if ($ldapattr ) { 

		switch ($oattr->type)
		  {
		  case "image":
		    if (ereg ("(.*)\|(.*)", $lvalue, $reg)) {

		      $vf = new VaultFile($this->dbaccess, "FREEDOM");
		      if ($vf->Retrieve ($reg[2], $info) == "") { 
		    $fd=fopen($info->path, "r");
      
		    if ($fd)
		      {
			$contents = @fread($fd, filesize ($info->path));
		  
			$infoldap[$ldapattr]=  ($contents);

			fclose ($fd);
		      }
		      }
		    }
		    break;
		  default:
		    if ($k == "us_passwd")$infoldap[$ldapattr]= ($lvalue);
		    else $infoldap[$ldapattr]=utf8_encode ($lvalue);
		  }
	      }
	    }
    
      
      }
      

      return ($this->ModifyLdapCard( $infoldap));
	
      

    } 
  
  // --------------------------------------------------------------------
  function SetPrivacity() { // priv  {P, R, W}
  // --------------------------------------------------------------------
    
    $priv=$this->GetValue("US_PRIVCARD");
    $err="";

    switch ($priv) {
    case "P":	
      if ($this->profid == "0") {
	$err=$this->setControl();	
	$this->profid=$this->id;
	$err=$this->modify();	
      }
      $err=$this->lock();

    break;
    case "R":	
      if ($this->profid != "0") {	
	$err=$this->unsetControl();	
	$this->profid=0;
	$err=$this->modify();;
      }
      $this->lock();
    break;
    case "W":	
      if ($this->profid != "0") {	
	$err=$this->unsetControl();	
	$this->profid=0;
	$err=$this->modify();;
      }
      $this->unlock();
    break;

    }
    if ($err != "") AddLogMsg($this->title.":".$err);
  }




  


?>