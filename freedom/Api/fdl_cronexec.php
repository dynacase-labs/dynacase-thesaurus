<?php
/**
 *  Execute Freedom Processes when needed
 *
 * @author Anakeen 2005
 * @version $Id: fdl_cronexec.php,v 1.1 2005/08/02 16:17:20 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



// refreah for a classname
// use this only if you have changed title attributes

include_once("FDL/Lib.Dir.php");
include_once("FDL/Class.DocFam.php");




$appl = new Application();
$appl->Set("FDL",	   $core);


$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}


$filters[]="exec_nextdate < '".Doc::getTimeDate()."'";
$tle=getChildDoc($dbaccess,0,0,"ALL",$filters,1,"TABLE","EXEC");



foreach ($tle as $k=>$v) {
  $de=getDocObject($dbaccess,$v);
  $status=$de->bgExecute();

  $de=new_Doc($dbaccess,$de->latestId(false,true));
  print $de->exec_handnextdate;
  $de->deleteValue("exec_handnextdate");
  $de->refresh();
  $err=$de->modify();
  print $de->id;
  print $de->exec_handnextdate;
  if ($err != "") print $err;
  
}

    

?>