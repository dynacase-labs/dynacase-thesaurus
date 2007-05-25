<?php
/**
 * Specific menu for family
 *
 * @author Anakeen 2000 
 * @version $Id: viewbarmenu.php,v 1.1 2007/05/25 15:46:01 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/popupdocdetail.php");


function viewbarmenu(&$action) {
  $docid = GetHttpVars("id");
  if ($docid == "") $action->exitError(_("No identificator"));
  $popup=getpopupdocdetail($action,$docid);
  foreach ($popup as $k=>$v) {
    if ($v["visibility"]!=POPUP_ACTIVE) unset($popup[$k]);
    else {
      if (!isset($v["jsfunction"])) $popup[$k]["jsfunction"]='';
      if ($v["mwidth"]=="")  $popup[$k]["mwidth"]=$action->getParam("FDL_VD2SIZE");
      if ($v["mheight"]=="")  $popup[$k]["mheight"]=$action->getParam("FDL_HD2SIZE");
      if ($v["target"]=="")  $popup[$k]["target"]="$k$docid";
      $popup[$k]["descr"]=ucfirst($v["descr"]);
      $popup[$k]["ISJS"]=($v["jsfunction"]!="");
      $popup[$k]["confirm"]=($v["confirm"]=="true");
      $popup[$k]["tconfirm"]=str_replace("'","&rsquo;",$v["tconfirm"]);
    }
  }
  $action->lay->setBlockData("LINKS",$popup);
  $action->lay->set("id",$docid);

}