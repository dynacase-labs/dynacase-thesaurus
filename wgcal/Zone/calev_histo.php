<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: calev_histo.php,v 1.4 2005/06/20 16:07:31 marc Exp $
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

  $ev = GetHttpVars("id", -1);
  if ($ev==-1) $evid = -1;
  else {
    $evtmp = new Doc($dbaccess, $ev);
    $evid = $evtmp->getValue("evt_idinitiator");
  }
  if ($evid==-1) return;
  $ev = new Doc($dbaccess, $evid);

  $action->lay->set("title", $ev->getValue("CALEV_EVTITLE"));
  $action->lay->set("owner", $ev->getValue("CALEV_OWNER"));

  $line = array();
  $revs = $ev->getTValue("COMMENT");
  foreach ($revs as $k => $v) $line[]["line"] = $v;
  $action->lay->setBlockData("HISTO", $line);

}