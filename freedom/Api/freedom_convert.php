<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_convert.php,v 1.3 2005/06/28 08:37:46 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



// refreah for a classname
// use this only if you have changed title attributes

include_once("FDL/Class.Doc.php");

$famId = GetHttpVars("tofamid",""); // familly filter
$docid = GetHttpVars("docid",""); // document


if (($docid == "") && ($famId == 0)) {
  print "arg class needed :usage --class=<class name> --famid=<familly id>";
  return;
}


$appl = new Application();
$appl->Set("FDL",	   $core);


$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}


$doc= new_Doc($dbaccess, $docid);
$doc->convert($famId);
print $doc->title. " converted";
    

?>