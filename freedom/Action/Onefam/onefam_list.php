<?php
/**
 * list available families
 *
 * @author Anakeen 2003
 * @version $Id: onefam_list.php,v 1.8 2004/06/03 14:47:28 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("FDL/Lib.Dir.php");


function onefam_list(&$action) {
 
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
 
  

  $action->lay->SetBlockData("SELECTUSER",  getTableFamilyList($action->GetParam("ONEFAM_IDS")) );
  $action->lay->SetBlockData("SELECTMASTER",getTableFamilyList($action->GetParam("ONEFAM_MIDS")) );

  if (($action->GetParam("ONEFAM_IDS") != "")&&($action->GetParam("ONEFAM_MIDS") != "")) {
    $action->lay->SetBlockData("SEPARATOR", array(array("zou")));  
    
  }

  if ($action->HasPermission("ONEFAM"))  {
    $action->lay->SetBlockData("CHOOSEUSERFAMILIES", array(array("zou")));    
  }
  if ($action->HasPermission("ONEFAM_MASTER"))  {
    $action->lay->SetBlockData("CHOOSEMASTERFAMILIES", array(array("zou")));    
  }
}


function getTableFamilyList($idsfam) {
  $selectclass=array();
  if ($idsfam != "") {
    $tidsfam = explode(",",$idsfam);

    $dbaccess = GetParam("FREEDOM_DB");

    foreach ($tidsfam as $k=>$cid) {
      $cdoc= new Doc($dbaccess, $cid);
      if ($cdoc->dfldid > 0) {

	$selectclass[$k]["idcdoc"]=$cdoc->initid;
	$selectclass[$k]["ftitle"]=$cdoc->title;
	$selectclass[$k]["iconsrc"]=$cdoc->getIcon();      
      }
    }
  }
  return $selectclass;
}
?>
