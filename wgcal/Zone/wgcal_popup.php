<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: wgcal_popup.php,v 1.1 2005/03/09 22:28:39 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage
 */
 /**
 */
include_once("WGCAL/Lib.WGCal.php");
include_once("WGCAL/WGCAL_external.php");

function wgcal_popup(&$action) {
  include_once("FDL/popup_util.php");
  $action->lay->set("id", GetHttpVars("ev", -1));
}


