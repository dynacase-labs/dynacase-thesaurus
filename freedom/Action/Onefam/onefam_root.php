<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: onefam_root.php,v 1.7 2006/10/05 09:22:38 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
include_once("GENERIC/generic_util.php");
function onefam_root(&$action) {
  // -----------------------------------

  $nbcol=intval($action->getParam("ONEFAM_LWIDTH",1));

  $delta=0;
  if ($action->read("navigator") == "EXPLORER") $delta=10;
  
  $iz=$action->getParam("CORE_ICONSIZE");
 

  $izpx=intval($action->getParam("SIZE_IMG-SMALL"))+2;
  $action->lay->set("wcols",$izpx*$nbcol+$delta);
  $action->lay->set("Title",_($action->parent->short_name));
 
  
  $openfam=$action->getParam("ONEFAM_FAMOPEN");
  if ($openfam > 0) {

    $action->lay->set("OPENFAM",true);
    $action->lay->set("openfam",$openfam);
  } else {
    $action->lay->set("OPENFAM",false);
  }

  
 
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
 
  

  $action->lay->SetBlockData("SELECTMASTER",getTableFamilyList($action->GetParam("ONEFAM_MIDS")) );

  if (($action->GetParam("ONEFAM_IDS") != "")&&($action->GetParam("ONEFAM_MIDS") != "")) {
    $action->lay->SetBlockData("SEPARATOR", array(array("zou")));  
    
  }

  if ($action->HasPermission("ONEFAM"))  {
    $action->lay->SetBlockData("CHOOSEUSERFAMILIES", array(array("zou")));    
    $action->lay->SetBlockData("SELECTUSER",  getTableFamilyList($action->GetParam("ONEFAM_IDS")) );
  }
  if ($action->HasPermission("ONEFAM_MASTER"))  {
    $action->lay->SetBlockData("CHOOSEMASTERFAMILIES", array(array("zou")));    
  }
  $iz=$action->getParam("CORE_ICONSIZE");
  $izpx=intval($action->getParam("SIZE_IMG-SMALL"));
  
  $action->lay->set("izpx",$izpx);

}
function getTableFamilyList($idsfam) {
  $selectclass=array();
  if ($idsfam != "") {
    $tidsfam = explode(",",$idsfam);

    $dbaccess = GetParam("FREEDOM_DB");

    foreach ($tidsfam as $k=>$cid) {
      $cdoc= new_Doc($dbaccess, $cid);
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