<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_import.php,v 1.1 2007/01/04 15:57:13 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */


include_once("FDL/import_file.php");
include_once("WGCAL/Lib.WGCal.php");
include_once('WHAT/Lib.Common.php');

function wgcal_import(&$action) { 

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

  $res = false;
  $count = 0;
  $tr = array();
  if (count($cr)>0) {
    foreach ($cr as $k => $v) {
      if ($v["familyid"]>0) {
	$count++;
	$tr[] = array("num"    => $count,
		      "title"  => $v["title"]." (".$v["familyname"].")",
		      "status" => _($v["action"]),
		      "err"    => ($v["err"]!=""?true:false),
		      "errmsg" => $v["err"], 
		      );
      }
    }
  }
  $res = ($count > 0); 
  $action->lay->set("res", $res);
  $action->lay->setBlockData("rv", $tr);
}