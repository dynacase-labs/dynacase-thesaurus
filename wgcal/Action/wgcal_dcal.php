<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_dcal.php,v 1.1 2005/11/22 17:25:30 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("WGCAL/Lib.Agenda.php");
include_once("WGCAL/Lib.wTools.php");


function wgcal_dcal(&$action) {

  $action->parent->AddJsRef("WHAT/Layout/logmsg.js");
  $action->parent->AddJsRef("WHAT/Layout/geometry.js");
  $action->parent->AddJsRef("WHAT/Layout/DHTMLapi.js");
  $action->parent->AddJsRef("WHAT/Layout/AnchorPosition.js");
  $action->parent->AddJsRef("WHAT/Layout/subwindow.js");

  $action->parent->AddJsRef("jsXMLParser/Layout/xmldom.js");

  $action->parent->AddJsRef("mcal/Layout/mcallib.js");
  $action->parent->AddJsRef("mcal/Layout/mcalCookie.js");
  $action->parent->AddJsRef("mcal/Layout/mcalmenu.js");
  $action->parent->AddJsRef("mcal/Layout/mcalendar.js");

  $theme = $action->getParam("WGCAL_U_THEME", "default");
  $action->lay->set("theme", $theme);

  $dcal = MonAgenda();
  $action->lay->set("id", $dcal->id);

  // Init the ressources
  $res = GetHttpVars("ress", "");
  if ($res!="") {
    $ress = explode("|", $res);
    foreach ($ress as $kr => $vr) {
      if ($vr>0) $tr[$vr] = $vr;
    }
  } else {  
    $ress = wGetRessDisplayed();
    $tr=array(); 
    $ire=0;
    foreach ($ress as $kr=>$vr) {
      if ($vr->id>0) $tr[$vr->id] = $vr->id;
    }
  }
  $idres = implode("|", $tr);
  $dcal->setValue("DCAL_IDRES", $tr);
  $dcal->Modify();

  $stdate = $action->GetParam("WGCAL_U_CALCURDATE", time());
  $action->lay->set("startTime", (w_GetFirstDayOfWeek($stdate)*1000));

  $action->lay->set("hoursPerDay", 
		    ($action->GetParam("WGCAL_U_STOPHOUR", 20) - $action->GetParam("WGCAL_U_STARTHOUR", 8)));
  $action->lay->set("hourDiv", $action->GetParam("WGCAL_U_HOURDIV", 1));
  

}