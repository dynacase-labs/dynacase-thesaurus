
// ---------------------------------------------------------------
// $Id: Method.DocUser.php,v 1.14 2003/05/28 14:35:16 eric Exp $
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
  var $orginit = false;
  var $action;
  
  var $defaultabstract= "USERCARD:VIEWABSTRACTCARD";
  var $defaultedit = "USERCARD:EDITUSERCARD";
  
// -----------------------------------
   function viewabstractcard($target="finfo",$ulink=true,$abstract="Y") {
     // -----------------------------------
     //     doc::viewabstractcard($target,$ulink,$abstract);
     $this->viewprop($target,$ulink,$abstract);
     $this->viewattr($target,$ulink,$abstract);
   }


   function editusercard($target="finfo",$ulink=true,$abstract="Y") {
     global $action;
     // -----------------------------------
     
     $this->lay->Set("selectp", "");
     $this->lay->Set("selectw", "");
     $this->lay->Set("selectr", "");
     $priv=$this->GetValue("US_PRIVCARD",getParam("USER_CONFIDENTIAL"));
     switch ($priv) {
      case "P":	
	$this->lay->Set("selectp", "selected");
      break;
      case "W":	
	$this->lay->Set("selectw", "selected");
      break;
      case "R":	
	$this->lay->Set("selectr", "selected");
      break;
     }
     if (($action->user->id == $this->owner) || ($this->id == 0)) 
       $this->lay->SetBlockData("PRIVATE",array(array("zou")));
   }
  // no in postUpdate method :: call this only if real change (values)
  function PostModify() {
    $priv=$this->GetValue("US_PRIVCARD");
    $err="";

    // update LDAP only no private card
    if (($priv == 'R') || ($priv == 'W')) {
      $this->SetLdapParam();
      $err=$this->UpdateLdapCard();
    }

    $this->SetPrivacity(); // set doc properties in concordance with its privacity

    return ($err);
  }

function SpecRefresh() {

  
  // " gettitle(D,US_IDSOCIETY):US_SOCIETY,US_IDSOCIETY";
  $this->refreshDocTitle("US_IDSOCIETY","US_SOCIETY");


  
  $this->AddParamRefresh("US_IDSOCIETY,US_SOCADDR","US_WORKADDR,US_WORKTOWN,US_WORKPOSTALCODE,US_WORKWEB,US_WORKCEDEX,US_COUNTRY");
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
	    
	    if ($ds) {
	      ldap_set_option($ds,LDAP_OPT_PROTOCOL_VERSION,3);
	      if (ldap_bind($ds, $this->rootdn, $this->rootpw)) {
		
		if ((@ldap_search($ds, $dn, "", array()))  || 
		    (ldap_add($ds, $dn, $orgldap))) {
		  
		  global $action;
		  $action->parent->SetParam("LDAP_ORGINIT","OK");
		}
	      }
	    }
	  }
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
	    
	    $r=ldap_delete($ds,"cn=".$this->id.",".$this->racine);
	  
	  
	  
	  ldap_close($ds);
	  }
      }
      
    } 
  // --------------------------------------------------------------------
  function UpdateLdapCard()
  // --------------------------------------------------------------------
    {
      include_once("FDL/Class.UsercardLdif.php");
      if (! $this->useldap) return;

      $oldif=new UsercardLdif();
      $infoldap=array();
	      
      $infoldap["cn"]=utf8_encode($this->title);
      $values=$this->GetValues();
      
      reset($values);
      while(list($k,$v) = each($values)) {


	$lvalue=$v;
	  //print $i.":".$lvalue."<BR>";
	  if ($lvalue != "")
	    {

	      // create attributes to LDAP update
	      $oattr=$this-> GetAttribute($k);
	    
	      $ldapattr = array_search(strtoupper($k),$oldif->import);

	      // particularity for URI need http://
	      if ($oattr->id == "US_WORKWEB") $lvalue="http://".$lvalue;

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




  

