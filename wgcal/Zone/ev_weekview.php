<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: ev_weekview.php,v 1.2 2005/01/27 12:06:20 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage
 */
 /**
 */

function ev_weekview(&$action) {

  $ev = GetHttpVars("ev", -1);
  if ($ev==-1) return;

  $vi = GetHttpVars("vi", "");
  switch ($vi) {
  case "R": $layf = "ev_weekview_resume.xml"; break;
  default: $layf = "ev_weekview_full.xml"; 
  }

  $action->layout = $action->GetLayoutFile($layf);
  $action->lay = new Layout($action->layout, $action);
  switch ($vi) {
  case "R":  $r = ev_weekview_resume($action, $ev); break;
  default: $r = ev_weekview_full($action, $ev); 
  }
  return $r;
}

function ev_weekview_resume(&$action, $ev) {
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $evg = new Doc($dbaccess, $ev);
  $action->lay->set("START", $evg->getValue("CALEV_START"));
  $action->lay->set("END", $evg->getValue("CALEV_END"));
  $action->lay->set("TITLE", $evg->getValue("CALEV_EVTITLE"));
}

function ev_weekview_full(&$action, $ev) {
  ev_weekview_resume($action, $ev);
}

?>