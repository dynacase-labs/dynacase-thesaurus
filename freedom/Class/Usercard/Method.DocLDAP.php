<?php
/**
 *  LDAP methods
 *
 * @author Anakeen 2000 
 * @version $Id: Method.DocLDAP.php,v 1.1 2005/02/01 16:23:24 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
 /**
 */


  
  // LDAP parameters
  var $serveur;
  var $port ;
  var $racine;
  var $rootdn;
  var $rootpw;

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
	      if (@ldap_bind($ds, $this->rootdn, $this->rootpw)) {
		
		if ((@ldap_search($ds, $dn, "", array()))  || 
		    (ldap_add($ds, $dn, $orgldap))) {
		  
		  return true;
		}
	      }
	    }
	    return false;
}

  // --------------------------------------------------------------------
  function SetLdapParam() {
    global $action;
    $this->serveur=$action->GetParam("LDAP_SERVEUR");
    $this->port=   $action->GetParam("LDAP_PORT");
    $this->racine= $action->GetParam("LDAP_ROOT");
    $this->rootdn= $action->GetParam("LDAP_ROOTDN");
    $this->rootpw= $action->GetParam("LDAP_ROOTPW");
    $this->useldap= ($action->GetParam("LDAP_ENABLED","no") == "yes");

    $this->action = $action;

    $this->exchangeLDAP=$this->getExchangeLDAP();
  }

/**
 * update or delete LDAP card
 */
function RefreshLdapCard() {
  $this->SetLdapParam();
  if (! $this->useldap) return false;

  if ($this->canUpdateLdapCard()) {
    $err=$this->UpdateLdapCard();
  } else {
    $err=$this->DeleteLdapCard();
  }
  return $err;
}

  function DeleteLdapCard()
  // --------------------------------------------------------------------
    {

      if (! $this->useldap) return;
     
 
      if (($this->serveur != "") && ($this->id > 0)) {
	$ds=ldap_connect($this->serveur,$this->port);

	if ($ds)  {
	    
	  ldap_set_option($ds,LDAP_OPT_PROTOCOL_VERSION,3);
	  if (@ldap_bind($ds, $this->rootdn, $this->rootpw)) {
	     $r=@ldap_delete($ds,"cn=".$this->id.",".$this->racine);
	  }
	  
	  
	  ldap_close($ds);
	}
      }
      
    } 

/**
 * update LDAP card from user document
 */
  function UpdateLdapCard()    {
      if (! $this->useldap) return false;
      if (! $this->canUpdateLdapCard()) return false;

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
	    
	      $ldapattr = array_search(strtoupper($k),$this->exchangeLDAP);

	      // particularity for URI need http://
	      if ($k == "us_workweb") $lvalue="http://".$lvalue;
	      if ($k == "us_passwd") $lvalue="{CRYPT}".$lvalue;
	    
	      if ($ldapattr ) { 

		switch ($oattr->type)
		  {
		  case "image":
		    if (ereg ("(.*)\|(.*)", $lvalue, $reg)) {

		      $vf = newFreeVaultFile($this->dbaccess);
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
      $this->specLDAPexport($infoldap);

      return ($this->ModifyLdapCard($infoldap));
	
      

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
	  if (is_array($this->ldapobjectclass)) {
	    foreach ($this->ldapobjectclass as $k=>$v) $infoldap["objectclass"][$k]=$v;
	  } else  $infoldap["objectclass"]=$this->ldapobjectclass;
	if (! isset($ds)) {
	  $ds=ldap_connect($this->serveur,$this->port);
	}

	if ($ds)
	  {
	    ldap_set_option($ds,LDAP_OPT_PROTOCOL_VERSION,3);
	    $dn = "cn=".$this->id.",".$this->racine;

	    if (@ldap_bind($ds, $this->rootdn, $this->rootpw))
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