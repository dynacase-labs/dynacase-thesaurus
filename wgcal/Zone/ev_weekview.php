<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: ev_weekview.php,v 1.1 2005/01/20 11:07:12 marc Exp $
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

  //echo "Rendez vous $ev vue $vi <br>";

  $action->layout = $action->GetLayoutFile($layf);
  $action->lay = new Layout($action->layout, $action);
  switch ($vi) {
  case "R":  ev_weekview_resume(); break;
  default: ev_weekview_full(); 
  }
  $action->lay->set("CONTENT", "[$ev::$vi]");

}

function ev_weekview_resume() {
}

function ev_weekview_full() {
}

?>