<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: usercard_ldapinit.php,v 1.7 2004/07/06 08:38:44 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



// remove all tempory doc and orphelines values
include_once("FDL/Class.Doc.php");
include_once("FDL/Lib.Dir.php");

define("SKIPCOLOR",'[1;31;40m');
define("UPDTCOLOR",'[1;32;40m');
define("STOPCOLOR",'[0m');
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

$ldaphost=$action->GetParam("LDAP_SERVEUR","localhost");
$ldappw=$action->GetParam("LDAP_ROOTPW");
$ldapdn=$action->GetParam("LDAP_ROOTDN");
$ldapr=$action->GetParam("LDAP_ROOT");
print sprintf(_("delete %s on server %s...\n"),$ldapr,$ldaphost);
system("ldapdelete -r -h $ldaphost -D '$ldapdn' -x -w '$ldappw' '$ldapr'");
$famid=getFamIdFromName($dbaccess,"USER");
$ldoc = getChildDoc($dbaccess, 0,0,"ALL", array(),$action->user->id,"TABLE",$famid);

$udoc= createDoc($dbaccess,"USER");
$uidoc= createDoc($dbaccess,"IUSER");
  
$reste=count($ldoc);
foreach($ldoc as $k=>$tdoc) {
  if ($tdoc["fromid"]==$famid) $doc=$udoc;
  else $doc=$uidoc;

    $doc->ResetMoreValues();
    $doc->Affect($tdoc);
    $doc->GetMoreValues();
    $priv=$doc->GetValue("US_PRIVCARD");
    $err="";

    // update LDAP only no private card
      $doc->SetLdapParam();
      $err=$doc->UpdateLdapCard();
      if (($err == "") && ($err !== false)) print UPDTCOLOR.$reste.")".$doc->title.": updated".STOPCOLOR."\n";
      else print SKIPCOLOR.$reste.")".$doc->title.": skipped : $err".STOPCOLOR."\n";
      $reste--;
  }
	
  

    

?>