<?php

include_once("WHAT/Lib.Common.php");
include_once("FDL/Class.Doc.php");
include_once("FDL/mailcard.php");
include_once("WGCAL/Lib.Agenda.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_saveevent(&$action) {

  $dbaccess = getParam("FREEDOM_DB");

  $idp   = GetHttpVars("id", -1);                    // Prod. Id (if exists)
  $oid   = GetHttpVars("oi", $action->user->fid);    // Owner Freedom Id
  $title = GetHttpVars("ti", "");                    // Title
  $nh    = GetHttpVars("nh", 0);                     // 0 : ts & te 1:no time 2:all day
  $ts    = GetHttpVars("ts", time());                     // Time start
  $te    = GetHttpVars("te", time()+3600);                     // Time end
  $lo    = GetHttpVars("lo", "");                    // Location
  $no    = GetHttpVars("no", "");                    // Note
  $cat   = GetHttpVars("ca", "0");                   // Categorie
  $conf  = GetHttpVars("co", "0");                   // Categorie

  

  if ($title=="" || $ts==0 || $te==0) {
    $action->lay->set("status", -1);
    $action->lay->set("statustext", _("internal server error ").basename(__FILE__)."::".__LINE__);
    return;
  }

  if ($idp==-1) {
    $event = createDoc($dbaccess, "CALEVENT");
    $err = $event->Add();
    $new = true;
  } else {
    $event = new_Doc($dbaccess, $idp);
    $new = false;
  }

  $down = new_Doc($dbaccess, $oid);
  $dcre = new_Doc($dbaccess, $action->user->fid);

  if ($new) {
    $event->setValue("calev_ownerid", $oid);
    $event->setValue("calev_owner", $down->getValue("title"));
  }
  $event->setValue("calev_creatorid", $action->user->fid);
  $event->setValue("calev_creator",$dcre->getValue("title"));

  $event->setValue("calev_evtitle", $title);
  $event->setValue("calev_evnote", $no);
  $event->setValue("calev_category", $cat);
  $event->setValue("calev_location", $lo);

  if ($nh==1) {
    $event->setValue("calev_start", date("d/m/Y 00:00:00",$ts));
    $event->setValue("calev_end", date("d/m/Y 00:00:00",$te));
    $event->setValue("calev_timetype", 1);
  } else if ($nh==2) {
    $event->setValue("calev_start", date("d/m/Y 00:00:00",$ts));
    $event->setValue("calev_end", date("d/m/Y 23:59:00",$te));
    $event->setValue("calev_timetype", 2);
  } else {
    $event->setValue("calev_start", date("d/m/Y H:i:00",$ts));
    $event->setValue("calev_end", date("d/m/Y H:i:00",$te));
    $event->setValue("calev_timetype", 0);
  }
    
  if ($new) {
    $event->setValue("calev_frequency", 1);
    $cal = getUserPublicAgenda();
    $event->setValue("calev_evcalendarid", -1); //$cal["id"] );
    $event->setValue("calev_evcalendar", $cal["title"] );
  }

  $event->confidential = ($conf>0 ? 1 : 0);
  $event->setValue("calev_visibility", $conf);
  $event->setValue("calev_confgroups", 0);

  if ($new) {
    $event->setValue("calev_evalarm", 0);
    $event->setValue("calev_evalarmday", 0);
    $event->setValue("calev_evalarmhour", 0);
    $event->setValue("calev_evalarmmin", 0);
    $event->setValue("calev_repeatmode", 0);
    $event->setValue("calev_convocation", 0);
    $event->setValue("calev_attid", array($oid));
    $event->setValue("calev_attwid", array($down->getValue("us_whatid")));
    $event->setValue("calev_attstate", array(2));
    $event->setValue("calev_attgroup", array(-1));
  }

  $err = $event->Modify();
  if ($err!="") {
    $action->lay->set("status", -1);
    $action->lay->set("Freedom internal error doc->modify(): $err");
    return;
  } 

  $err = $event->PostModify();
  if ($err!="") {
    $action->lay->set("status", -1);
    $action->lay->set("Freedom internal error doc->PostModify(): $err");
    return;
  } 

  $event->setAccessibility();
  $event->unlock(true);

  // Get produced event
  $ev = GetChildDoc($dbaccess, 0, 0, "ALL", array("evt_idinitiator = ".$event->id ), $action->user->id, "LIST", "EVENT_FROM_CAL");
  if (count($ev)!=1) {
    $action->lay->set("status", -1);
    $action->lay->set("statustext", "#".$event->id." produced event error (count=".count($ev).")");
  }
  $action->lay->set("id", $ev[0]->id);
  $action->lay->set("fromid", $ev[0]->fromid);
  $action->lay->set("evt_idinitiator", $ev[0]->getValue("evt_idinitiator"));
  $action->lay->set("evt_frominitiatorid", $ev[0]->getValue("evt_frominitiatorid"));
  $action->lay->set("start", localFrenchDateToUnixTs($ev[0]->getValue("evt_begdate"),true));
  $end = ($ev[0]->getValue("evfc_realenddate") == "" ? $ev[0]->getValue("evt_enddate") : $ev[0]->getValue("evfc_realenddate"));
  $action->lay->set("end", localFrenchDateToUnixTs($end, true));
  if ($ev[0]->Control("confidential")=="" || ($ev[0]->confidential==0 && $ev[0]->Control("view")=="")) {
    $action->lay->set("evt_title", addslashes($ev[0]->getValue("evt_title")));
    $action->lay->set("displayable", "true");
  } else {
    $action->lay->set("displayable", "false");
    $action->lay->set("evt_title",_("confidential event"));
  }
  $dattr = array( "icons" => array("iconsrc" => "'$noimgsrc'"),
		     "bgColor" => "lightblue",
		     "fgColor" => "black",
		     "topColor" => "lightblue",
		     "rightColor" => "lightblue",
		     "bottomColor" => "lightblue",
		     "leftColor" => "lightblue"  );
  if (method_exists($ev[0], "getDisplayAttr")) $dattr = $ev[0]->getDisplayAttr();
  $action->lay->set("icons", $dattr["icons"]);
  $action->lay->set("bgColor", $dattr["bgColor"]);
  $action->lay->set("fgColor", $dattr["fgColor"]);
  $action->lay->set("topColor", $dattr["topColor"]);
  $action->lay->set("rightColor", $dattr["rightColor"]);
  $action->lay->set("bottomColor", $dattr["bottomColor"]);
  $action->lay->set("leftColor", $dattr["leftColor"]);
  $action->lay->set("editable", ($ev[0]->Control("edit")=="" ? "true" : "false"));

  $action->lay->set("status", 0);
  $action->lay->set("statustext", "#".$event->id." ".($new?"created":"updated"));
  return ;
}
?>
  
 