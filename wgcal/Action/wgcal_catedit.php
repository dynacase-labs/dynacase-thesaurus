<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_catedit.php,v 1.1 2005/11/29 15:51:33 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once('FDL/Class.Doc.php');

function wgcal_catedit(&$action) {

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/DHTMLapi.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/PopupWindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/ColorPicker2.js");
  $dbaccess = $action->getParam("FREEDOM_DB");

  $calf = getIdFromName($dbaccess, "CALEVENT");
  $glist = GetChildDoc($dbaccess, 0, 0, "ALL", array("catg_famid = $calf"), $action->user->id, "LIST", "CATEGORIES");
  if (count($glist)==0) {
    $action->lay->set("nocat", true);
    return;
  }
  $action->lay->set("nocat", false);
  $catl = $glist[0];

  $ncatname = GetHttpVars("ncatname", "");
  $ncatorder = GetHttpVars("ncatorder", "");
  $ncatcolor = GetHttpVars("ncatcolor", "");
  $ecat = GetHttpVars("catlist", array());

  $catid    = GetHttpVars("catid", array());
  $catcolor = GetHttpVars("catcolor", array());
  $catorder = GetHttpVars("catorder", array());
  $catname  = GetHttpVars("catname", array());


  if (is_array($catid) && is_array($catcolor) && is_array($catorder) && is_array($catname) 
      && (count($catid)>0) && (count($catid) == count($catcolor)) && (count($catid) == count($catorder)) && (count($catid) == count($catname))) {
    $lastcat = 0;
    foreach ($catid as $k => $v) $lastcat = ($lastcat<$v?$v:$lastcat);
    $lastcat++;
//     echo "OK lascat = $lastcat<br>";
//     print_r2($catid);
//     print_r2($catcolor);
//     print_r2($catorder);
//     print_r2($catname);
    if ($ncatname!="" && $ncatorder!="" && $ncatcolor!="") {
      $catid[] = $lastcat;
      $catorder[] = $ncatorder;
      $catname[] = $ncatname;
      $catcolor[] = $ncatcolor;
    }
    $catl->setValue("catg_id", $catid);
    $catl->setValue("catg_order", $catorder);
    $catl->setValue("catg_name", $catname);
    $catl->setValue("catg_color", $catcolor);
    $catl->Modify();
  }

  $cat_id    = $catl->getTValue("catg_id");
  $cat_order = $catl->getTValue("catg_order");
  $cat_name  = $catl->getTValue("catg_name");
  $cat_color = $catl->getTValue("catg_color");

  $catg = array();
  $maxorder = 0;
  foreach ($cat_order as $kg => $vg) {
    $catg[] = array( "idcat" => $cat_id[$kg],
		     "catorder" => $cat_order[$kg],
		     "catname" => $cat_name[$kg],
		     "catcolor" => $cat_color[$kg],
		     );
    $maxorder = ($vg>$maxorder?$vg:$maxorder);
  }
  usort($catg, catgsort);
  $action->lay->setBlockData("CATG", $catg);
  $action->lay->set("maxorder", ($maxorder+10));
 
  return;
}

function catgsort($a, $b) {
  return  intval($a["catorder"]) - intval($b["catorder"]);
}

?>