<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_toolbar.php,v 1.7 2005/01/26 08:42:49 marc Exp $
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

   _waitrv($action);
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

function _waitrv(&$action) {

  // recherche magique

  $wrv = array( array("id"=>1, "date"=> "lun 12 janvier, 15h00", "title"=>"Revue de spec mairie de cugnaux", "owner"=>"Jean Demars"),
		array("id"=>2, "date"=> "lun 3 fevrier, 9h00", "title"=>"Avancement d&eacute;veloppement", "owner"=>"Eric Brison") );
  $action->lay->SetBlockData("WAITRV", null);
  $action->lay->SetBlockData("befWAITRV", null);
  $action->lay->SetBlockData("aftWAITRV", null);
  if (count($wrv)>0) {
    $t = array();
    $it=0;
    foreach ($wrv as $k => $v) {
      $t[$it]["wrvid"] = $v["id"];
      $t[$it]["wrvtitle"] = $v["title"];
      $t[$it]["wrvfulldescr"] = $v["date"]." : ".$v["title"]." (".$v["owner"].")";
      $it++;
    }
    $action->lay->SetBlockData("WAITRV", $t);
    //   $action->lay->SetBlockData("befWAITRV", array( array( "nop" => "")));
    //    $action->lay->SetBlockData("aftWAITRV", array( array( "nop" => "")));
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

  $rd = new Doc($dbaccess, $action->user->fid);
  $action->lay->set("myrid", $rd->id);
  $action->lay->set("myricon", $rd->getIcon());
  $action->lay->set("myrdesc", $rd->title);
  $action->lay->set("myrcolor", $action->GetParam("WGCAL_U_MYCOLOR", "black"));

  $curress = $action->GetParam("WGCAL_U_RESSDISPLAYED", "");

  $lress = explode("|", $curress);
  if (count($lress)>0) {
    foreach ($lress as $k => $v) {
      $tt = explode("%", $v);
      $rid = $tt[0];
      $sid = ($tt[1]!="" ? $tt[1] : 0);
      $cid = ($tt[2]!="" ? $tt[2] : "blue");
      $rd = new Doc($dbaccess, $rid);
      if ($rd->IsAffected() && $rd->id != $action->user->fid) {
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

  $urc = ($action->GetParam("WGCAL_U_USERESSINEVENT", 1) ? "WGCRessSelected" : "WGCRessDefault");
  $action->lay->set("urclass", $urc);
  $ur = ($action->GetParam("WGCAL_U_USERESSINEVENT", 1) ? "checked" : "");
  $action->lay->set("urchecked", $ur);

}
?>