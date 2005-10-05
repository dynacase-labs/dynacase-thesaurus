<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_resspicker_main.php,v 1.3 2005/10/05 10:29:01 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');

function wgcal_resspicker_main(&$action) {
  $target = GetHttpVars("updt", "");
  $wre = GetHttpVars("wre", 0);
  setHttpVar("wre", $wre);
  $action->lay->set("updt", $target);
  $action->lay->set("wre", $wre);
}
?>