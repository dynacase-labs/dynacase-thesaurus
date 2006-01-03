<?php
/**
 *  LDAP methods
 *
 * @author Anakeen 2000 
 * @version $Id: Method.DocLDAP.php,v 1.5 2006/01/03 17:31:57 eric Exp $
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
    $tinfoldap=$this->ConvertToLdap();       
    $err=$this->ModifyLdapCard($tinfoldap);
  } else {
    $err=$this->DeleteLdapCard();
  }
  return $err;
}


function DeleteLdapCard()    {
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
function getLDAPDN($rdn,$path="") {
  if ($path=="") $dn = "$rdn=".$this->infoldap[$this->cindex][$rdn].",".$this->racine;
  else  $dn = "$rdn=".$this->infoldap[$this->cindex][$rdn].",$path,".$this->racine;
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
  $q->order_by="famid,ldapclass";
  $l=$q->Query(0,0,"TABLE");
  $this->ldapmap=array();
  foreach ($l as $v) {
    $this->ldapmap[$v["ldapname"].$v["index"]]=$v;
  }
  return $this->ldapmap;
}
/**
 * return array of ldap values LDAP card from user document
 */
function ConvertToLdap()    {

  $this->infoldap=array();
	      
  $tmap=$this->getMapAttributes();
 
  foreach ($tmap as $ki=>$v) {
    $k=$v["ldapname"];
    $map=$v["ldapmap"];
    $index=$v["index"];
    if ($map) {
      if (substr($map,0,2)=="::") {
	// call method 
	$this->cindex=$index; // current index
	$value=$this->ApplyMethod($map);
	if ($value){
	  if (is_array($value)) $this->infoldap[$index][$k]=array_map("utf8_encode",$value);
	  else $this->infoldap[$index][$k]=utf8_encode ($value);

	  if ((!isset($this->infoldap[$index]["objectclass"])) || ( !in_array($v["ldapclass"],$this->infoldap[$index]["objectclass"]))) $this->infoldap[$index]["objectclass"][]=$v["ldapclass"];

	}
      } else {
	switch ($map) {
	case "I":
	  $this->infoldap[$index][$k]=$this->initid;
	  break;
	case "T":
	  $this->infoldap[$index][$k]=utf8_encode($this->title);
	  break;
	default:
	  $oa=$this->getAttribute($map);
	  $value=$this->getValue($map);

	  if ($value) {
	    if ((!isset($this->infoldap[$index]["objectclass"])) || ( !in_array($v["ldapclass"],$this->infoldap[$index]["objectclass"]))) $this->infoldap[$index]["objectclass"][]=$v["ldapclass"];
	    
	    switch ($oa->type) {
	    case "image":
	      if (ereg ("(.*)\|(.*)", $value, $reg)) {
		$vf = newFreeVaultFile($this->dbaccess);
		if ($vf->Retrieve ($reg[2], $info) == "") { 
		  $fd=fopen($info->path, "r");      
		  if ($fd) {
		    $contents = @fread($fd, filesize ($info->path));		  
		    $this->infoldap[$index][$k]=  ($contents);
		    fclose ($fd);
		  }
		}
	      }
	      break;
	    case "password":
	      $this->infoldap[$index][$k]= "{CRYPT}".($value);
	      break;	      
	    default:
	      $this->infoldap[$index][$k]=utf8_encode ($value);
	    }
	  }
	}
      }
    }
  }
  
  
  return $this->infoldap;
} 

/**
 * get ldap value
 * @param string $idattr ldap attribute name
 * @return string the value
 */
function getLDAPValue($idattr,$index="") {
  if (! isset($this->infoldap)) $this->ConvertToLdap();
  if ($index == "") $tldap=current($this->infoldap);
  else $tldap=$this->infoldap[$index];
  
  return $tldap[$idattr];
}



/**
 * modify in LDAP database information
 */
function ModifyLdapCard( $tinfoldap) {

  if (! $this->useldap) return;
  $retour = "";
  if ($this->serveur != "")   {
    if ($this->OrgInit()) {
	  
      // ------------------------------
      // update LDAP values

      if (! isset($ds)) {
	$ds=ldap_connect($this->serveur,$this->port);
      }

      if ($ds)	{
	ldap_set_option($ds,LDAP_OPT_PROTOCOL_VERSION,3);

	if (@ldap_bind($ds, $this->rootdn, $this->rootpw))  {
	  foreach ($tinfoldap as $k=>$infoldap) {
	    $dn = $infoldap["dn"];
	    unset($infoldap["dn"]);
	    $sr = @ldap_search($ds, $dn, "", array());
	    if ( $sr )		
	      {
		// to modify need to delete and then add
		// the ldap_modify function cannot perform
		// add and replace attribute in all configuration
		//ldap_modify($ds, $dn, $infoldap);
		  
		ldap_delete($ds, $dn);
	      }
	  

	    if (! @ldap_add($ds, $dn, $infoldap)) $retour .= sprintf(_("errldapadd:%s\n%s\n%d:%s\n"),$dn,ldap_error($ds),ldap_errno($ds),ldap_err2str(ldap_errno($ds)));
	  }
	}
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