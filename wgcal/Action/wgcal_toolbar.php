<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_toolbar.php,v 1.5 2004/12/15 21:26:49 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

include_once("FDL/Class.Doc.php");

function wgcal_toolbar(&$action) {

  $action->parent->AddJsRef("jscalendar/Layout/calendar.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-fr.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-setup.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/DHTMLapi.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/PopupWindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/ColorPicker2.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_calendar.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_toolbar.js");

  $cssfile = $action->GetLayoutFile("calendar-default.css");
  $csslay = new Layout($cssfile,$action);
  $action->parent->AddCssCode($csslay->gen());

   _navigator($action);
   _listress($action);

   // Set initial visibility
   $all =  explode("|", $action->GetParam("WGCAL_U_TOOLSSTATE", ""));
   $vis = array ( "up", "down");
   $visstyle = array ( "none", "");
   if (count($all)>0) {
     while (list($k, $v) = each($all)) {
       $t = explode("%",$v);
       $action->lay->set($t[0], $t[1]);
       $action->lay->set($t[0]."ico", $vis[$t[1]]);
       $action->lay->set($t[0]."init", $visstyle[$t[1]]);
     }
   }

}

function _navigator(&$action) {

  $ctime = $action->Read("WGCAL_SU_CURDATE", time());
  $cmtime = $ctime * 1000;
  $action->lay->set("CTIME", $ctime);
  $action->lay->set("CmTIME", $cmtime);

  $cy = strftime("%Y",$ctime);
  $cys = $cy - 5;
  $cye = $cy + 5;
  $action->lay->set("YSTART", $cys);
  $action->lay->set("YSTOP",$cye );
}



function _listress(&$action)
{

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $i = 0;
  $j = 0;

  $curress = $action->GetParam("WGCAL_U_RESSDISPLAYED", "");

  $lress = explode("|", $curress);
  if (count($lress)>0) {
    foreach ($lress as $k => $v) {
      $tt = explode("%", $v);
      $rid = $tt[0];
      $sid = ($tt[1]!="" ? $tt[1] : 0);
      $cid = ($tt[2]!="" ? $tt[2] : "blue");
      $rd = new Doc($dbaccess, $rid);
      if ($rd->IsAffected()) {
	$t[$i]["RID"] = $rd->id;
	$t[$i]["RDESCR"] = $rd->title;
	$t[$i]["RICON"] =  $rd->getIcon();
	$t[$i]["RCOLOR"] = $cid;
        $t[$i]["RSTATE"] = $sid;
	if ($sid==1) $t[$i]["RSTYLE"] = "WGCRessSelected";
	else $t[$i]["RSTYLE"] = "WGCRessDefault";
	$i++;
      }
    }
  }
  $action->lay->SetBlockData("L_RESS", $t);
}
?>
