<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_resspicker_main.php,v 1.1 2004/12/08 16:44:18 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');

function wgcal_resspicker_main(&$action) {
  $target = GetHttpVars("updt", "");
  $action->lay->set("updt", $target);
}
