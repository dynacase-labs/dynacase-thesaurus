<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_viewevent.php,v 1.2 2005/03/09 22:27:44 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once("FDL/Class.Doc.php");

function wgcal_viewevent(&$action) {
  $evt = GetHttpVars("ev", "");
  $action->lay->set("ID", $evt);
  $action->lay->set("M", "v");
}

?>
