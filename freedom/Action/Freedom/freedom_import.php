<?php
/**
 * Import document descriptions
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_import.php,v 1.12 2006/08/15 13:56:10 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */


include_once("FDL/import_file.php");





// -----------------------------------
function freedom_import(&$action) {
  // -----------------------------------
  global $_FILES;
  if (ini_get("max_execution_time") < 180) ini_set("max_execution_time",180); // 3 minutes
  
  if (isset($_FILES["file"])) {
    $filename=$_FILES["file"]['name'];
    $csvfile=$_FILES["file"]['tmp_name'];
  } else {
    $filename=GetHttpVars("file");
    $csvfile=$filename;
  }
  $cr=add_import_file($action,$csvfile); 

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");

  foreach ($cr as $k=>$v) {
    $cr[$k]["taction"]=_($v["action"]); // translate action
    $cr[$k]["order"]=$k; // translate action
    $cr[$k]["svalues"]="";
    $cr[$k]["msg"]=nl2br($v["msg"]);
    foreach ($v["values"] as $ka=>$va) {
      $cr[$k]["svalues"].= "<LI>[$ka:$va]</LI>"; // 
    }
  }
  $nbdoc=count(array_filter($cr,"isdoc"));
  $action->lay->SetBlockData("ADDEDDOC",$cr);
  $action->lay->Set("nbdoc","$nbdoc");
}

function isdoc($var) {
  return (($var["action"]=="added") ||  ($var["action"]=="updated"));
}


?>
