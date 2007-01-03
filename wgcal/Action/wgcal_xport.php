<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: wgcal_xport.php,v 1.1 2007/01/03 18:18:02 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage
 */
 /**
 */
include_once('Lib.wTools.php');
include_once('Lib.WGCal.php');
include_once('FDL/Lib.Dir.php');
include_once('FDL/popup_util.php');

function wgcal_xport(&$action) {

  $action->parent->AddJsRef("jscalendar/Layout/calendar.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-fr.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-setup.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");
  $action->parent->AddCssRef("FDL:POPUP.CSS",true);

  $edate = time() - (30*24*3600);

  $action->lay->set("exportTsDate", $edate);
  $action->lay->set("exportDate", strftime("%a %d %b %Y", $edate));

  return;
}
