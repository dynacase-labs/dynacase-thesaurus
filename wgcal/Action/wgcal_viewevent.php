<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_viewevent.php,v 1.3 2005/03/10 18:06:49 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once("FDL/Class.Doc.php");

function wgcal_viewevent(&$action) {
  $evt = GetHttpVars("ev", -1);
  $calev = GetHttpVars("cev", -1);
  $action->lay->set("ID", $evt);
  $action->lay->set("CID", $calev);
  $action->lay->set("M", "v");
}

?>
