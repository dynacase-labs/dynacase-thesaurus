<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: usercard_ldapinit.php,v 1.5 2004/02/17 10:51:57 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



// remove all tempory doc and orphelines values
include_once("FDL/Class.Doc.php");
include_once("FDL/Lib.Dir.php");


$appl = new Application();
$appl->Set("USERCARD",	   $core);

if ($action->GetParam("LDAP_ENABLED","no") != "yes") {
  print "LDAP disabled : do nothing ; modify LDAP_ENABLED parameter if you want update LDAP usercard";
  exit;
}
$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}


$famid=getFamIdFromName($dbaccess,"USER");
$ldoc = getChildDoc($dbaccess, 0,0,"ALL", array(),$action->user->id,"LIST",$famid);


  
  while(list($k,$doc) = each($ldoc)) {
    $priv=$doc->GetValue("US_PRIVCARD");
    $err="";

    // update LDAP only no private card
    if (($priv != "P")) {
      $doc->SetLdapParam();
      $err=$doc->UpdateLdapCard();
      if ($err == "") print $doc->title.": updated\n";
      else print $doc->title.": skipped : $err\n";
    }
  }
	
  

    

?>