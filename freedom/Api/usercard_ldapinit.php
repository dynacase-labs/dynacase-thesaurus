<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: usercard_ldapinit.php,v 1.6 2004/03/16 14:14:06 eric Exp $
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
$ldoc = getChildDoc($dbaccess, 0,0,"ALL", array(),$action->user->id,"TABLE",$famid);

$udoc= createDoc($dbaccess,"USER");
  
  while(list($k,$tdoc) = each($ldoc)) {
    $udoc->ResetMoreValues();
    $udoc->Affect($tdoc);
    $udoc->GetMoreValues();
    $priv=$udoc->GetValue("US_PRIVCARD");
    $err="";

    // update LDAP only no private card
    if (($priv != "P")) {
      $udoc->SetLdapParam();
      $err=$udoc->UpdateLdapCard();
      if ($err == "") print $udoc->title.": updated\n";
      else print $udoc->title.": skipped : $err\n";
    }
  }
	
  

    

?>