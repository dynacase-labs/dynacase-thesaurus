<?php

/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_navigator.php,v 1.2 2004/12/01 17:07:08 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

include_once("Lib.WGCal.php");

function wgcal_navigator(&$action) {

  $ico = "down";
  $vis = "none";
  $state = WGCalToolIsVisible($action, CAL_T_NAVIGATOR);
  if ($state) {
    $ico = "up";
    $vis = "";
  }
  $action->lay->set("VISICO",$ico);
  $action->lay->set("VISTOOL",$vis);

  $ctime = $action->Read("WGCAL_SU_CURDATE", time());
  $cmtime = $ctime * 1000;
  $action->lay->set("CTIME", $ctime);
  $action->lay->set("CmTIME", $cmtime);

  $cy = strftime("%Y",$ctime);
  $cys = $cy - 5;
  $cye = $cy + 5;
  $action->lay->set("YSTART", $cys);
  $action->lay->set("YSTOP",$cye );
}
?>
