<?php
/**
 * Initiate LDAP database
 *
 * @author Anakeen 2000 
 * @version $Id: usercard_ldapinit.php,v 1.12 2006/01/02 13:18:59 eric Exp $
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

$clean = (GetHttpVars("clean","no")=="yes"); // clean databases option
$appl = new Application();
$appl->Set("USERCARD",	   $core);

if ($action->GetParam("LDAP_ENABLED","no") != "yes") {
  $err= "LDAP disabled : do nothing ; modify LDAP_ENABLED parameter if you want update LDAP usercard";
  print $err;
  wbar(0,0,$err); 
  return true;
}
$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  $err = "Freedom Database not found : param FREEDOM_DB";
  print $err;
  wbar(0,0,$err); 
  return true;
}

$ldaphost=$action->GetParam("LDAP_SERVEUR","localhost");
$ldappw=$action->GetParam("LDAP_ROOTPW");
$ldapdn=$action->GetParam("LDAP_ROOTDN");
$ldapr=$action->GetParam("LDAP_ROOT");
if ($clean) {
  $msg= sprintf(_("delete %s on server %s...\n"),$ldapr,$ldaphost);
  print $msg;
  wbar(1,-1,$msg); 
  system("ldapdelete -r -h $ldaphost -D '$ldapdn' -x -w '$ldappw' '$ldapr'");
  wbar(1,-1,_("LDAP cleaned")); 
 }
$famid=getFamIdFromName($dbaccess,"USER");
$ldoc = getChildDoc($dbaccess, 0,0,"ALL", array(),$action->user->id,"TABLE",$famid);

$udoc= createDoc($dbaccess,"USER");
$uidoc= createDoc($dbaccess,"IUSER");
$total=count($ldoc);
$reste=$total;
foreach($ldoc as $k=>$tdoc) {
  if (getv($tdoc,"us_whatid")=="") $doc=$udoc;  
  else $doc=$uidoc;
  $doc->Affect($tdoc,true);
  $priv=$doc->GetValue("US_PRIVCARD");
  $err="";

    // update LDAP only no private card
      $doc->SetLdapParam();
      $err=$doc->ConvertToLdap();
      if (($err == "") && ($err !== false)) print UPDTCOLOR.$reste.")".$doc->title.": updated".STOPCOLOR."\n";
      else print SKIPCOLOR.$reste.")".$doc->title.": skipped : $err".STOPCOLOR."\n";
      $reste--;

    wbar($reste,$total);  
  }
	
  
  if ($fbar) { unlink($fbar);}

    

?>