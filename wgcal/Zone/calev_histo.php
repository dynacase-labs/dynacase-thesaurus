<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: calev_histo.php,v 1.8 2006/08/03 07:31:14 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage
 */
 /**
 */
include_once("WGCAL/Lib.WGCal.php");
include_once("EXTERNALS/WGCAL_external.php");

function calev_histo(&$action) {
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $evid = GetHttpVars("id", -1);
  $ev = new_Doc($dbaccess, $evid);

  $action->lay->set("title", $ev->getValue("CALEV_EVTITLE"));
  $action->lay->set("owner", $ev->getValue("CALEV_OWNER"));

  $revs = $ev->getHisto();
  $action->lay->setBlockData("HISTO", $revs);

}
?>
