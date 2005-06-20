<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: search.php,v 1.1 2005/06/20 16:37:30 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once("WGCAL/Lib.wTools.php");


function search(&$action) {

  
  $viewme=false;
  $ress = wGetRessDisplayed($action);
  $tr=array(); 
  $ire=0;
  foreach ($ress as $kr=>$vr) {
    if ($vr->id>0) $tr[$ire++] = $vr->id;
    if ($vr->id==$action->user->fid) $viewme=true;
  }
  if ($viewme) {
    $grp = WGCalGetRGroups($action, $action->user->id);
    foreach ($grp as $kr=>$vr) $tr[$ire++] = $vr;
  }
  $ress = implode("|", $tr);
  $action->lay->set("rlist",$ress);
  return;
}

?>
