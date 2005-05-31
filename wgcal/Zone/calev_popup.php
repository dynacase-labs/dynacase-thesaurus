<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: calev_popup.php,v 1.2 2005/05/31 10:27:06 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage
 */
 /**
 */
include_once("WGCAL/Lib.WGCal.php");
include_once("EXTERNALS/WGCAL_external.php");

function calev_popup(&$action) {
  include_once("FDL/popup_util.php");
  $kdiv = 1;
  $action->lay->set("id", GetHttpVars("ev", -1));
}


