<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: call_abstract.php,v 1.1 2005/04/01 11:47:20 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage
 */
 /**
 */
include_once("WGCAL/Lib.WGCal.php");
include_once("WGCAL/WGCAL_external.php");
include_once("WGCAL/call_card.php");

function call_abstract(&$action) {
  call_card(&$action);
  if ($action->GetParam("WGCAL_U_RESUMEICON", 0) == 0) 
    $action->lay->SetBlockData("showicons", $null);
  else
    $action->lay->SetBlockData("showicons", array(array("zou" => "")));

}
?>
