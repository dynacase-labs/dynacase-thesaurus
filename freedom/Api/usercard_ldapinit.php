<?php


// remove all tempory doc and orphelines values
include_once("FDL/Class.Doc.php");


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

$query = new QueryDb($dbaccess,"Doc");
$query->AddQuery("classname='DocUser'");

$ldoc = $query->Query();


if ($query->nb > 0) {
  
  while(list($k,$doc) = each($ldoc)) {
    $priv=$doc->GetValue(QA_PRIVACITY);
    $err="";

    // update LDAP only no private card
    if (($priv == 'R') || ($priv == 'W')) {
      $doc->SetLdapParam();
      $err=$doc->UpdateLdapCard();
      if ($err == "") print $doc->title.": updated\n";
      else print $doc->title.": skipped : $err\n";
    }
  }
	
  
}
    

?>