<?php
/**
 * Speed Search
 *
 * @author Anakeen 2000 
 * @version $Id: speedsearch.php,v 1.4 2005/02/17 07:52:21 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */




include_once("FDL/Lib.Dir.php");



// -----------------------------------
function speedsearch(&$action) {
  // -----------------------------------

  $dbaccess = $action->GetParam("FREEDOM_DB");

  // Get all the params      
  $dir=GetHttpVars("dirid"); // insert search in this folder
  
  $action->lay->Set("dirid", $dir);

  $idsfam = $action->GetParam("FREEDOM_PREFFAMIDS");


  if ($idsfam != "") {
    $tidsfam = explode(",",$idsfam);

    $selectclass=array();
    while (list($k,$cid)= each ($tidsfam)) {
      $cdoc= new Doc($dbaccess, $cid);
     
	$selectclass[$k]["idcdoc"]=$cdoc->initid;
	$selectclass[$k]["classname"]=$cdoc->title;
      
      
    }
    $action->lay->SetBlockData("SELECTPREFCLASS", $selectclass);
  }

  $tclassdoc=GetClassesDoc($dbaccess, $action->user->id,array(1,2),"TABLE");

  while (list($k,$cdoc)= each ($tclassdoc)) {
    $selectclass[$k]["idcdoc"]=$cdoc["initid"];
    $selectclass[$k]["classname"]=$cdoc["title"];
  }
  
  $action->lay->SetBlockData("SELECTCLASS", $selectclass);
  
}


?>