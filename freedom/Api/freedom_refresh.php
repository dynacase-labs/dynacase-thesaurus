<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_refresh.php,v 1.13 2005/01/14 17:55:03 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



// refreah for a classname
// use this only if you have changed title attributes

include_once("FDL/Class.Doc.php");

$famId = GetHttpVars("famid",""); // familly filter
$docid = GetHttpVars("docid",""); // doc filter
$method = GetHttpVars("method"); // method to use
$arg = GetHttpVars("arg"); // arg for method


if  ($famId == "") {
  print "arg class needed :usage  --famid=<familly id> [--docid=<doc id>] [--method=<method name>]";
  return;
}


$appl = new Application();
$appl->Set("FDL",	   $core);


$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}


if (! is_numeric($famId)) $famId=getFamIdFromName($dbaccess,$famId);

if ($famId > 0) {
  $GEN=getGen($dbaccess);
  include_once "FDL$GEN/Class.Doc$famId.php";
}
	
  
$query = new QueryDb($dbaccess,"Doc$famId");
$query->AddQuery("locked != -1");
$query->AddQuery("doctype != 'T'");
if ($docid > 0) $query->AddQuery("id = $docid");


    
$table1 = $query->Query(0,0,"TABLE");

     
if ($query->nb > 0)	{

  printf("\n%d documents to refresh\n", count($table1));
  $card=count($table1);
  $fdoc[$famId] = createDoc($dbaccess,$famId,false);
  $doc=&$fdoc[$famId];
  if ($method && (method_exists ($doc,$method))){
    $usemethod=true;
    print "using $method method\n";
    $targ = array();
    if ($arg != "") $targ[]=$arg;
  }
  else  $usemethod=false;
  while(list($k,$v) = each($table1)) 
	    { 
	      if ($v["fromid"] != $doc->fromid) {
		if (! isset($fdoc[$v["fromid"]])) $fdoc[$v["fromid"]] = createDoc($dbaccess,$v["fromid"],false);
		$doc=&$fdoc[$v["fromid"]];$usemethod=false;
		if ($method && (method_exists ($doc,$method))) $usemethod=true;
		
	      }
	      $doc->Affect($v);
	      print $card-$k.")".$doc->title . "\n";
	      if ($usemethod) call_user_method_array ($method, $doc, $targ);
	      $doc->refresh();
	      $doc->refreshTitle();
	      $doc->Modify();

	    }	  
}      
    

?>