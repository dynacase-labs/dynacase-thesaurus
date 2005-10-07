<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_toolbar.php,v 1.52 2005/10/07 10:17:06 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

include_once("FDL/Class.Doc.php");
include_once('FDL/Lib.Dir.php');
include_once("WGCAL/Lib.WGCal.php");
include_once("osync/Lib.WgcalSync.php");
include_once("EXTERNALS/WGCAL_external.php");
include_once('WGCAL/Lib.wTools.php');
include_once('WGCAL/Lib.Agenda.php');

function wgcal_toolbar(&$action) {

  $action->lay->set("refresh",$action->getParam("WGCAL_U_RELOADTOOLBAR", 0));
  
  if ($action->getParam("WGCAL_U_TBCONTACTS",0)) $action->lay->set("SHOWCONTACTS", true);
  else $action->lay->set("SHOWCONTACTS", false);

  if ($action->getParam("WGCAL_U_TBSEARCH",0)) $action->lay->set("SHOWSEARCH", true);
  else $action->lay->set("SHOWSEARCH", false);

  $action->lay->set("VContactSTop", ($action->getParam("WGCAL_U_CONTACSEARCH",0)==1 ? true : false ));
  $action->lay->set("VContactSBottom", ($action->getParam("WGCAL_U_CONTACSEARCH",0)==2 ? true : false ));


  if ($action->getParam("WGCAL_U_TBTODOS",1)) $action->lay->set("SHOWTODOS", true);
  else $action->lay->set("SHOWTODOS", false);

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/DHTMLapi.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/PopupWindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/ColorPicker2.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_calendar.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_toolbar.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-fr.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-setup.js");


  $action->lay->set("MyFreedomId", $action->user->fid);
  $action->lay->set("owner", $action->user->lastname." ".$action->user->firstname);
  $action->lay->set("today", strftime("%a %d %b, %H:%M", time()));

  // Set last outlook syncro date
  $action->lay->set("LSYNC", false);
  if ($action->getParam("WGCAL_U_OSYNCVDATE",1)) {
    $action->lay->set("LSYNC", true);
    $db = WSyncGetAdminDb();
    $lsync = GetLastSyncDate($db);
    if ($lsync!="") {
      $action->lay->set("lastsync", substr(WSyncTs2Outlook($lsync),0,16));
      $action->lay->set("lsyncstyle", ((time()-$lsync)>(24*3600*7)?"color:red":""));
    } else {
      $action->lay->set("lastsync", _("no sync made"));
      $action->lay->set("lsyncstyle", "");
    }
  }
  
  _navigator($action);

  _listress($action);


  // how many days for todos ?
  $action->lay->set("tododays", $action->getParam("WGCAL_U_TODODAYS", 7));
}



function _navigator(&$action) {

  $ctime = $action->GetParam("WGCAL_U_CALCURDATE", time());
  $cmtime = $ctime * 1000;
  $action->lay->set("CTIME", $ctime);
  $action->lay->set("CmTIME", $cmtime);
  $action->lay->set("Cm2TIME", $cmtime + (30*24*3600 * 1000) );

  $cy = strftime("%Y",$ctime);
  $cys = $cy - 5;
  $cye = $cy + 5;
  $action->lay->set("YSTART", $cys);
  $action->lay->set("YSTOP",$cye );

  setToolsLayout($action, 'nav');
}



function _listress(&$action)
{

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $i = 0;
  $j = 0;


  $curress = $action->GetParam("WGCAL_U_RESSDISPLAYED", "");

  $lress = explode("|", $curress);

  $cuser = false;
  foreach ($lress as $k => $v) {
    $tt = explode("%", $v);
    if ($tt[0] == $action->user->fid) $cuser = true;
  }

  if (!$cuser) $lress[count($lress)] = $action->user->fid."%1%yellow";

  $contacts = $action->GetParam("WGCAL_U_PREFRESSOURCES", "");
  $tcontacts = explode("|", $contacts);
  $rplist = "";
  if (count($tcontacts)>0) {
    foreach ($tcontacts as $kc => $vc) {
      if ($vc=="") continue;
      $rplist .= (strlen($rplist)>0?"|":"").$vc;
    }
  }
  $action->lay->set("rplist", $rplist);

  // Init popup
  $action->lay->set("POPUPICONS", $action->getParam("WGCAL_U_ICONPOPUP", true));
  include_once("FDL/popup_util.php");
  popupInit('resspopup',  array('displayress', 'rcalendar', 'changeresscolor', 'removeress', 'onlyme', 'rvprefered', 'invertress', 'displayallr', 'hideallr', 'cancelress'));
  foreach ($lress as $k => $v) {
    $tt = explode("%", $v);
    $rid = $tt[0];
    $sid = ($tt[1]!="" ? $tt[1] : 0);
    $cid = ($tt[2]!="" ? $tt[2] : "blue");
    $rd = new_Doc($dbaccess, $rid);
    $trd = getTDoc($dbaccess, $rid);
    if (!$rd->IsAffected()) continue;
    $writeaccess = $readaccess = false;
    $cal = getUserPublicAgenda($rid, false);
    if ($cal && $cal->isAffected()) {
      $writeaccess = ($cal->Control("invite")==""?true:false);
      $readaccess = ($cal->Control("execute")==""?true:false);
    }
    if ($writeaccess || $readaccess || $rd->id == $action->user->fid) {
      if ($rd->id == $action->user->fid) PopupInactive('resspopup', $rd->id, 'removeress');
      else PopupActive('resspopup', $rd->id, 'removeress');
      $t[$i]["RG"] = $i;
      $t[$i]["RID"] = $rd->id;
      $t[$i]["RDESCR"] = addslashes(ucwords(strtolower($rd->getTitle())));
      $t[$i]["RICON"] =  $rd->getIcon();
      $t[$i]["RCOLOR"] = $cid;
      $t[$i]["RSTATE"] = $sid;
      if ($rd->id == $action->user->fid) $t[$i]["ROMODE"] = "false";
      else $t[$i]["ROMODE"] =  ($writeaccess ? "false" : "true" );;
      if ($sid==1) $t[$i]["RSTYLE"] = "WGCRessSelected";
      else $t[$i]["RSTYLE"] = "WGCRessDefault";
      PopupActive('resspopup', $rd->id, 'displayress');
      PopupActive('resspopup', $rd->id, 'changeresscolor');
      PopupActive('resspopup', $rd->id, 'rcalendar');
      PopupActive('resspopup', $rd->id, 'removeress');
      PopupActive('resspopup', $rd->id, 'onlyme');
      PopupActive('resspopup', $rd->id, 'rvprefered');
      PopupActive('resspopup', $rd->id, 'invertress');
      PopupActive('resspopup', $rd->id, 'displayallr');
      PopupActive('resspopup', $rd->id, 'hideallr');
      PopupActive('resspopup', $rd->id, 'cancelress');
      
      $i++;
    }
    setToolsLayout($action, 'cals');
  }

  popupGen(count($t));
  wUSort($t, "RDESCR"); 
  $action->lay->SetBlockData("L_RESS", $t);

}

?>
