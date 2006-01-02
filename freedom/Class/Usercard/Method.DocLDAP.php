<?php
/**
 *  LDAP methods
 *
 * @author Anakeen 2000 
 * @version $Id: Method.DocLDAP.php,v 1.4 2006/01/02 13:17:11 eric Exp $
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

/**
 * initialialize LDAP coordonates
 */
function SetLdapParam() {
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
 * update or delete LDAP card
 */
function RefreshLdapCard() {
  $this->SetLdapParam();
  if (! $this->useldap) return false;

  if ($this->canUpdateLdapCard()) {
    $err=$this->ConvertToLdap();
  } else {
    $err=$this->DeleteLdapCard();
  }
  return $err;
}

  function DeleteLdapCard()
    {

      if (! $this->useldap) return;
     
 
      if (($this->serveur != "") && ($this->id > 0)) {
	$ds=ldap_connect($this->serveur,$this->port);

	if ($ds)  {
	    
	  ldap_set_option($ds,LDAP_OPT_PROTOCOL_VERSION,3);
	  if (@ldap_bind($ds, $this->rootdn, $this->rootpw)) {
	     $r=@ldap_delete($ds,$this->getLDAPDN());
	  }
	  
	  
	  ldap_close($ds);
	}
      }
      
    } 


/**
 * get DN of document
 */
function getLDAPDN($path="") {
  if ($path=="") $dn = "uid=".$this->initid.",".$this->racine;
  else  $dn = "uid=".$this->initid.",$path,".$this->racine;
  return $dn;
}

/**
 * get Attribute mapping FREEDOM -> LDAP
 * @return array
 */  
function getMapAttributes() {
  include_once("FDL/Class.DocAttrLDAP.php");
  $fids=$this->GetFromDoc();

  include_once("Class.QueryDb.php");
  $q=new QueryDb($this->dbaccess,"DocAttrLDAP");
  $q->AddQuery(getSqlCond($fids,"famid"));
  $q->order_by="famid";
  $l=$q->Query(0,0,"TABLE");
  $this->ldapmap=array();
  foreach ($l as $v) {
    $this->ldapmap[$v["ldapname"]]=$v;
  }
  return $this->ldapmap;
}
/**
 * update LDAP card from user document
 */
function ConvertToLdap()    {
  if (! $this->useldap) return false;
  if (! $this->canUpdateLdapCard()) return false;

  $infoldap=array();
	      
  $tmap=$this->getMapAttributes();
 
 
  foreach ($tmap as $k=>$v) {
    $map=$v["ldapmap"];
    if ($map) {
      if (substr($map,0,2)=="::") {
	// call method 
	$value=$this->ApplyMethod($map);
	if ($value){
	  print_r2($value);
	  if (is_array($value)) $infoldap[$k]=array_map("utf8_encode",$value);
	  else $infoldap[$k]=utf8_encode ($value);
	  $infoldap["objectclass"][$v["ldapclass"]]=$v["ldapclass"];
	}
      } else {
	switch ($map) {
	case "I":
	  $infoldap[$k]=$this->initid;
	  break;
	case "T":
	  $infoldap[$k]=utf8_encode($this->title);
	  break;
	default:
	  $oa=$this->getAttribute($map);
	  $value=$this->getValue($map);

	  if ($value) {
	    $infoldap["objectclass"][$v["ldapclass"]]=$v["ldapclass"];
	    switch ($oa->type) {
	    case "image":
	      if (ereg ("(.*)\|(.*)", $value, $reg)) {
		$vf = newFreeVaultFile($this->dbaccess);
		if ($vf->Retrieve ($reg[2], $info) == "") { 
		  $fd=fopen($info->path, "r");      
		  if ($fd) {
		    $contents = @fread($fd, filesize ($info->path));		  
		    $infoldap[$k]=  ($contents);
		    fclose ($fd);
		  }
		}
	      }
	      break;
	    case "password":
	      $infoldap[$k]= "{CRYPT}".($value);
	      break;	      
	    default:
	      $infoldap[$k]=utf8_encode ($value);
	    }
	  }
	}
      }
    }
  }
  
  $n=0;
  foreach ($infoldap["objectclass"] as $k =>$v) {
    $infoldap["objectclass"][$n++]=$v;
    unset($infoldap["objectclass"][$k]);
  }
  return ($this->ModifyLdapCard($infoldap));
	
      

} 

/**
 * modify in LDAP database information
 */
function ModifyLdapCard( $infoldap, $objectclass="inetOrgPerson") {

  if (! $this->useldap) return;
  $retour = "";
  if ($this->serveur != "")   {
      if ($this->OrgInit()) {
	  
	// ------------------------------
	// update LDAP values

	if (! isset($ds)) {
	  $ds=ldap_connect($this->serveur,$this->port);
	}

	if ($ds)
	  {
	    ldap_set_option($ds,LDAP_OPT_PROTOCOL_VERSION,3);	   
	    $dn = $infoldap["dn"];
	    unset($infoldap["dn"]);
	    if (@ldap_bind($ds, $this->rootdn, $this->rootpw))  {
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

	    if (! @ldap_add($ds, $dn, $infoldap)) $retour = sprintf(_("errldapadd:%s\n%s\n%d:%s"),$dn,ldap_error($ds),ldap_errno($ds),ldap_err2str(ldap_errno($ds)));
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

function createLDAPDc($n) {	
  if (! isset($ds)) {
    $ds=ldap_connect($this->serveur,$this->port);
  }	
  if ($ds) {
    if (! @ldap_add($ds, "dc=$n,".$this->racine, 
		    array("objectclass"=>array("dcObject",
					       "organizationalUnit"),
			  "dc"=>"$n",
			  "ou"=>"$n")))
      return ldap_error($ds);
  }
}

?>