<?php
// ---------------------------------------------------------------
// $Id: Class.DocUser.php,v 1.3 2002/02/18 10:53:59 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Usercard/Attic/Class.DocUser.php,v $
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

$CLASS_USERCARD_PHP = '$Id: Class.DocUser.php,v 1.3 2002/02/18 10:53:59 eric Exp $';


include_once("FDL/Class.Doc.php");
include_once("FDL/Class.UsercardLdif.php");


define('TOP_USERDIR', 121); // user top folder
define('QA_PRIVACITY', 216); // privacity attribute
define('QA_URI', 215); // URI attribute
define('QA_MAIL', 205); // MAIL attribute
define('QA_FNAME', 202); // First name attribute
define('QA_LNAME', 201); // Last name attribute
define('FAM_DOCUSER', 120); // URI attribute

Class DocUser extends Doc
{
  // --------------------------------------------------------------------
    //---------------------- OBJECT CONTROL PERMISSION --------------------
      
      var $obj_acl = array (
			    array(
				  "name"		   =>"view",
				  "description"	   =>"view usercard", # N_("view usercard")
				  "group_default"      =>"Y"),
			    array(
				  "name"               =>"edit",
				  "description"        =>"edit usercard"),# N_("edit usercard")
			    array(
				  "name"               =>"delete",
				  "description"        =>"delete usercard",# N_("delete usercard")
				  "group_default"      =>"N"),
			    array(
				  "name"               =>"edit",
				  "description"        =>"edit usercard") # N_("edit usercard")
			    
			    
			    
			    );
  
  // ------------
    var $defDoctype='F';
  var $defClassname='DocUser';
  
  
  // LDAP parameters
  var $serveur;
  var $port ;
  var $racine;
  var $rootdn;
  var $rootpw;
  var $dbaccess;
  var $orginit = false;
  var $action;
  
  function DocUser($dbaccess='', $id='',$res='',$dbid=0) {
    // don't use Doc constructor because it could call this constructor => infinitive loop
    DbObjCtrl::DbObjCtrl($dbaccess, $id, $res, $dbid);
  }
  
  
  // no in postUpdate method :: call this only if real change (values)
  function PostModify() {
    $priv=$this->GetValue(QA_PRIVACITY);
    $err="";

    // update LDAP only no private card
    if (($priv == 'R') || ($priv == 'W')) {
      $this->SetLdapParam();
      $err=$this->UpdateLdapCard();
    }

    $this->SetPrivacity(); // set doc properties in concordance with its privacity
    $this->DeleteTemporary(); // delete temporary search
    return ($err);
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

    if ($action->GetParam("LDAP_ORGINIT") == "OK")
      $this->orginit = true;
    $this->action = $action;
  }

  // --------------------------------------------------------------------
  function ModifyLdapCard( $infoldap, $objectclass="inetOrgPerson") {
  // --------------------------------------------------------------------

    if (! $this->useldap) return;
    $retour = "";
    if ($this->serveur != "")
      {

	if (! $this->orginit)
	  {

	    // ------------------------------
	    // include LDAP organisation first
	    $orgldap["objectclass"]="organization";
	    if (ereg(".*o=(.*),.*", $this->racine, $reg))
	      $orgldap["o"]=$reg[1]; // get organisation from LDAP_ROOT
	    else
		$orgldap["o"]="unknown";

	    $dn=$this->racine;
	    $ds=ldap_connect($this->serveur,$this->port);
	    if ($ds)
	      if (ldap_bind($ds, $this->rootdn, $this->rootpw)) {
		
		if ((@ldap_search($ds, $dn, "", array()))  || 
		    (ldap_add($ds, $dn, $orgldap))) {
		  
		  global $action;
		  $action->parent->SetParam("LDAP_ORGINIT","OK");
		}
	      }
	  }
	// ------------------------------
	// update LDAP values
	
	$infoldap["objectclass"]=$objectclass;
	if (! isset($ds))
	  $ds=ldap_connect($this->serveur,$this->port);


	if ($ds)
	  {
	    $dn = "id=".$this->id.",".$this->racine;

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
	    if (! @ldap_add($ds, $dn, $infoldap))
	      $retour = _("errldapadd");

	    ldap_close($ds);
	  }
	else
	  {	    
	      $retour = _("errldapconnect");
	  }
      }
    
    return $retour;
  }
  // --------------------------------------------------------------------
  function PostDelete()    
  // --------------------------------------------------------------------
    {
      Doc::PostDelete();
      $this->SetLdapParam();
      $this->DeleteLdapCard();
    }
  // --------------------------------------------------------------------
  function DeleteLdapCard()
  // --------------------------------------------------------------------
    {

      if (! $this->useldap) return;
 
      if ($this->serveur != "") {
	$ds=ldap_connect($this->serveur,$this->port);
	
	if ($ds)
	  {
	    
	  if (ldap_bind($ds, $this->rootdn, $this->rootpw))
	    
	    $r=ldap_delete($ds,"id=".$this->id.",".$this->racine);
	  
	  
	  
	  ldap_close($ds);
	  }
      }
      
    } 
  // --------------------------------------------------------------------
  function UpdateLdapCard()
  // --------------------------------------------------------------------
    {
      if (! $this->useldap) return;

      $oldif=new UsercardLdif();
      $infoldap=array();
	      
      $infoldap["cn"]=utf8_encode($this->title);
      $this->GetValues();
      
      reset($this->values);
      while(list($k,$v) = each($this->values)) {


	$lvalue=$v["value"];
	  //print $i.":".$lvalue."<BR>";
	  if ($lvalue != "")
	    {

	      // create attributes to LDAP update
	      $oattr=$this-> GetAttribute($v["attrid"]);
	    
	      $ldapattr = array_search($v["attrid"],$oldif->import);

	      // particularity for URI need http://
	      if ($oattr->id == QA_URI) $lvalue="http://".$lvalue;

	      if ($ldapattr ) { 
		switch ($oattr->type)
		  {
		  case "image":
		    
		    break;
		    ereg ("(.*)\|(.*)\|(.*)", $lvalue, $reg); 
		    $fd=fopen ($reg[2], "r");
      
		    if ($fd)
		      {
			$contents = fread ($fd, filesize ($reg[2]));
		  
			$infoldap[$ldapattr]=  ($contents);

			fclose ($fd);
		      }
		
		    break;
		  default:
		  
		    $infoldap[$ldapattr]=utf8_encode ($lvalue);
		  }
	      }
	    }
    
      
      }
      

      return ($this->ModifyLdapCard( $infoldap));
	
      

    } 
  
  // --------------------------------------------------------------------
  function SetPrivacity() { // priv  {P, R, W}
  // --------------------------------------------------------------------
    
    $priv=$this->GetValue(QA_PRIVACITY);
    
    switch ($priv) {
    case "P":	
      if ($this->profid != "1") {
	$this->profid = "1";
	$this->modify();
      }

      $this->lock();
    break;
    case "R":	
      if ($this->profid != "0") {
	$this->profid = "0";
	$this->modify();
      }
      $this->lock();
    break;
    case "W":	
      if ($this->profid != "0") {
	$this->profid = "0";
	$this->modify();
      }
      $this->unlock();
    break;

    }
  }


// -----------------------------------
  function _GetCatgId($docid, $title) {
  // -----------------------------------

    $ldir = $this->oqdv->getChildDir($docid, true);
  
    if (count($ldir) > 0 ) {
     
      while (list($k,$v) = each($ldir)) {
	if ($v->title == $title) return $v->id;
      }

      reset($ldir);
      while (list($k,$v) = each($ldir)) {
	$catgid= $this->_GetCatgId($v->id, $title);
	if ($catgid > 0) return $catgid ;
      }

    }
    
  
  return 0;
}

  // --------------------------------------------------------------------
  function GetCatgId($title) { // return the id for catg named $title
  // --------------------------------------------------------------------
    $this->oqdv=new  QueryDirV($this->dbaccess);
    return $this->_GetCatgId(TOP_USERDIR, $title);
  }

  // --------------------------------------------------------------------
    function Control ($aclname) { // redefine Doc::Control
    // -------------------------------------------------------------------- 
    if ($this->userid == 1) return ""; // admin can do anything
    if ($this->IsAffected())
      if ($this->profid > 0 ) 
	{
	  if ($this->owner == $this->userid) return "";
	  else return sprintf(_("private access of the user card %s. You're not the owner"),
			      $this->title);
	}
      else return "";

    return "object not initialized : $aclname";
  }
}

?>