<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_viewevent.php,v 1.1 2005/03/06 21:40:21 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once("FDL/Class.Doc.php");

function wgcal_viewevent(&$action) {
  $evt = GetHttpVars("evt", "");
  $ref = GetHttpVars("ref", "");
  setHttpVar("ev", $evt);
  setHttpVar("ref", $ref);
  setHttpVar("mode", "v");
  $action->lay->set("ID", $evt);
  $action->lay->set("REF",$ref );
}

?>
