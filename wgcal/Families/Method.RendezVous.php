<?php

var $eventAttBeginDate = "CALEV_START";
var $eventAttEndDate   = "CALEV_END";
var $eventAttDesc      = "CALEV_TITLE";
var $eventAttCode      = "RV";
var $eventFamily       = "EVENT_FROM_CAL";

var $defaultview      = "WGCAL:RENDEZVOUSVIEW:T";
var $defaultabstract  = "WGCAL:RENDEZVOUSRESUME:T";
var $defaultshorttext = "WGCAL:RENDEZVOUSRESUME:T";
var $defaultlongtext  = "WGCAL:RENDEZVOUSRESUME:T";

var $defaultedit = "WGCAL:RENDEZVOUSEDIT:U";

function postModify() {
  $err = $this->setEvent(); 
  if ($err!="") print_r2($err);
}

function getEventOwner() {
  $uo = new Doc($this->dbaccess, $this->getValue("CALEV_OWNERID"));
  return $uo->getValue("us_whatid");
}

function getEventRessources() {
  return $this->getTValue("CALEV_ATTID", array());
}

function  setEventSpec(&$e) {
  include_once('EXTERNALS/WGCAL_external.php');
  $e->setValue("EVT_IDCREATOR", $this->getValue("CALEV_OWNERID"));
  $e->setValue("EVT_CREATOR", $this->getValue("CALEV_OWNER"));
  $e->setValue("EVT_DESC", $this->getValue("CALEV_EVNOTE"));
  $e->setValue("EVT_CODE", $this->getValue("CALEV_CATEGORY"));
  $e->setValue("EVFC_VISIBILITY", $this->getValue("CALEV_VISIBILITY"));
  $e->setValue("EVFC_REALENDDATE", $this->getValue("CALEV_END"));
  $e->setValue("EVFC_REPEATMODE", $this->getValue("CALEV_REPEATMODE"));
  $e->setValue("EVFC_REPEATWEEKDAY", $this->getValue("CALEV_REPEATWEEKDAY"));
  $e->setValue("EVFC_REPEATMONTH", $this->getValue("CALEV_REPEATMONTH"));
  $e->setValue("EVFC_REPEATUNTIL", $this->getValue("CALEV_REPEATUNTIL"));
  $e->setValue("EVFC_REPEATUNTILDATE", $this->getValue("CALEV_REPEATUNTILDATE"));
  $e->setValue("EVFC_ALARMTIME", $this->getValue("CALEV_ALARMTIME"));
  $tv = $this->getTValue("CALEV_EXCLUDEDATE");
  $e->deleteValue("EVFC_EXCLUDEDATE");
  if (count($tv)>0) {
    foreach ($tv as $kv => $vv) {
      $texc[] = $vv;
    }
    $e->setValue("EVFC_EXCLUDEDATE", $texc);
  }
  $e->setValue("EVFC_REPEATFREQ", $this->getValue("CALEV_FREQUENCY"));
  if ($this->getValue("CALEV_REPEATMODE") > 0) {
    $renddate = ($this->getValue("CALEV_REPEATUNTIL")==1 ? $this->getValue("CALEV_REPEATUNTILDATE") : jd2cal((5000001), 'FrenchLong') );
    $e->setValue("evt_enddate", $renddate);
  }


  $tattid = $this->getTValue("CALEV_ATTID");
  $tattst = $this->getTValue("CALEV_ATTSTATE");
  $tattgp = $this->getTValue("CALEV_ATTGROUP");
  $nattid = array(); $nattst = array(); $iatt = 0;
  $rejattid = array();  $iratt = 0;
  foreach ($tattid as $ka => $va) {
    $nattid[$iatt] = $va;
    $nattst[$iatt] =  $tattst[$ka];
    $iatt++;
    if ($tattst[$ka] == EVST_REJECT) {
      $rejattid[$iratt] = $va;
      $iratt++;
    }
  }
  if (count($nattid)==0) $e->deleteValue("EVFC_LISTATTID");
  else {
    $e->setValue("EVFC_LISTATTID", $nattid);
    $e->setValue("EVFC_LISTATTST", $nattst);  
  }
 if (count($rejattid)==0)  $e->deleteValue("EVFC_REJECTATTID");  
 else $e->setValue("EVFC_REJECTATTID", $rejattid);  

  $e->setValue("EVFC_CALENDARID", $this->getValue("CALEV_EVCALENDARID"));

  // Propagate RV profil to events
  //$e->setProfil($this->dprofid );
}


function mailrv() {
  $this->lay->set("rvid", $this->id);

  $uo = new Doc($dbaccess, $this->getValue("CALEV_OWNERID"));
  $this->lay->set("rvowner", $uo->title);

  $this->lay->set("rvtitle", GetHttpVars("msg", ""));
  $this->lay->set("dstart", substr($this->getValue("CALEV_START"),0,16));
  $this->lay->set("dend", substr($this->getValue("CALEV_END"),0,16));

}

function RendezVousView() {

  include_once('WGCAL/Lib.WGCal.php');

  global $action;

  $dbaccess = $this->dbaccess;

  // matrice de ressource affichée / présentes dans le RV
  $ressd = wgcalGetRessourcesMatrix($this->id);

  $this->lay->set("ID",    $this->id);
  $myid = $action->user->fid;
  $ownerid = $this->getValue("CALEV_OWNERID");
  $conf    = $this->getValue("CALEV_VISIBILITY");
  if ($ownerid == $myid) $private = false;
  else if (isset($ressd[$myid])) $private = false;
  else if ($conf==0) $private = false;
  else $private = true;

  $visgroup = false;
  $glist = "";
  if ($conf==2) {
    $visgroup = true;
    $ogrp = $this->getValue("CALEV_CONFGROUPS");
    $t = explode("|", $ogrp);
    foreach ($t as $k => $v ) {
      if ($v!="") {
	$g  = new Doc($dbaccess, $v);
	$glist .= ($glist=="" ? "" : ", " ) . ucwords(strtolower($g->title));
      }
    }
  }
  $this->lay->set("ShowGroups", $visgroup);
  $this->lay->set("groups", $glist);
  
  $this->lay->set("D_HL","");
  $this->lay->set("D_HR","");
  $this->lay->set("D_LL","");
  $this->lay->set("D_LR","");

  $ldstart = substr($this->getValue("CALEV_START"),0,10);
  $lstart = substr($this->getValue("CALEV_START"),11,5);
  $ldend = substr($this->getValue("CALEV_END"),0,10);
  $lend = substr($this->getValue("CALEV_END"),11,5);

  switch($this->getValue("CALEV_TIMETYPE",0)) {

  case 1: 
    $this->lay->set("D_HR",w_strftime(w_dbdate2ts($ldend),WD_FMT_DAYLTEXT));
    $this->lay->set("D_LR",_("no hour")); 
    break;

  case 2: 
    $this->lay->set("D_HR",w_strftime(w_dbdate2ts($ldend),WD_FMT_DAYLTEXT));
    $this->lay->set("D_LR",_("all the day")); 
    break;

  default:
    
    if ($ldend!=$ldstart) {
      $this->lay->set("D_HL",w_strftime(w_dbdate2ts($ldstart),WD_FMT_DAYLTEXT).", ");
      $this->lay->set("D_LL",w_strftime(w_dbdate2ts($ldend),WD_FMT_DAYLTEXT).", ");
      $this->lay->set("D_HR",$lstart);
      $this->lay->set("D_LR",$lend);
    } else {
      $this->lay->set("D_HR",w_strftime(w_dbdate2ts($ldend),WD_FMT_DAYLTEXT));
      $this->lay->set("D_LR",$lstart." - ".$lend);
    }
  }
  $this->lay->set("iconevent", $this->getIcon($this->icon));

  $this->lay->set("owner", ucwords(strtolower($this->getValue("CALEV_OWNER"))));
  $this->lay->set("ShowCategories", false);
  $this->lay->set("ShowDate", false);
  $this->lay->set("modifdate", "");
  $this->lay->set("ShowCalendar", false);
  $this->lay->set("incalendar", "");
  if (!$private) {
    $this->lay->set("ShowDate", true);
    $this->lay->set("modifdate", strftime("%d %B %y %H:%M",$this->revdate));
    $this->lay->set("ShowCalendar", true);
    $this->lay->set("incalendar", $this->getValue("CALEV_EVCALENDAR"));
    $show = ($action->getParam("WGCAL_G_SHOWCATEGORIES",0)==1 ? true : false);
    if ($show) {
      $this->lay->set("ShowCategories", $show);
      $catg = wGetCategories();
      $cat = $this->getValue("CALEV_CATEGORY");
      if (isset($catg[$cat])) $tc = $catg[$cat];
      else $tc = "";
      $this->lay->set("category", $tc);
    }
    $title = $this->getValue("CALEV_EVTITLE");
  } else {
    $title = _("confidential event");
  }
  $this->lay->set("TITLE", $title);
  
  $tress  = $this->getTValue("CALEV_ATTID");
  $tresse = $this->getTValue("CALEV_ATTSTATE");
  $tressg = $this->getTValue("CALEV_ATTGROUP");
  
  // Si je suis convié / j'ai refusé / affichable => Ma couleur
  // Si le propriétaire est dans les affichables / pas refusé => Couleur du propriétaire
  // Si le propriétaire n'est pas affichable => Couleur du premier convié ET AFFICHE et pas refusé.... 
  $showrefused = $action->getParam("WGCAL_U_DISPLAYREFUSED", 0);
  $event_color = "";
  if (isset($ressd[$myid]) 
      && (($ressd[$myid]["state"]==EVST_REJECT && $showrefused==1) || $ressd[$myid]["state"]!=EVST_REJECT )
      && $ressd[$myid]["displayed"]) {
    $event_color = $ressd[$myid]["color"];
  } else {
    if (isset($ressd[$ownerid]) && $ressd[$ownerid]["state"]!=EVST_REJECT &&  $ressd[$ownerid]["displayed"]) {
      $event_color = $ressd[$ownerid]["color"];
    } else {
      while ((list($k,$v) = each($ressd)) && $event_color=="") {
	if ($v["state"]!=EVST_REJECT && $v["displayed"]) $event_color = $v["color"];
      }
    }
  }
  
  $me_attendee = (isset($ressd[$myid]) && $ressd[$myid]["state"]!=EVST_REJECT &&  $ressd[$myid]["displayed"]);

  $bgnew = WGCalGetColorState(EVST_ACCEPT);
  $bgresumecolor = $bgcolor = $event_color;
  if (isset($ressd[$myid]) && $ressd[$myid]["displayed"]) $bgnew = WGCalGetColorState($ressd[$myid]["state"]);

  $this->lay->set("bgstate", $bgnew);
  $this->lay->set("bgcolor", $bgcolor);
  $this->lay->set("bgresumecolor", $bgresumecolor);

  $textcolor = "black";
  $this->lay->set("textcolor", $textcolor);

  // repeat informations
  $this->lay->set("repeatdisplay", "none");
  $tday = array( _("monday"), _("tuesday"),_("wenesday"),_("thursday"),_("friday"),_("saturday"), _("sunday"));
  if (!$private) {
    $rmode = $this->getValue("CALEV_REPEATMODE", 0);
    $rday = $this->getTValue("CALEV_REPEATWEEKDAY", -1);
    $rmonth = $this->getValue("CALEV_REPEATMONTH", -1);
    $runtil = $this->getValue("CALEV_REPEATUNTIL", 0);
    $runtild = $this->getValue("CALEV_REPEATUNTILDATE", "");
    $rexclude = $this->getValue("CALEV_EXCLUDEDATE", array());
    if ($rmode>0 && $rmode<5) {
      $this->lay->set("repeatdisplay", "");
      switch ($rmode) {
      case 1:
        $tr = _("dayly");
        break;
      case 2:
        $tr = _("weekly");
        $tr .= ": ";
        foreach ($rday as $kd => $vd) $tr .= $tday[$vd]." ";
        break;
      case 3:
        $tr = _("monthly");
        if ($rmonth==0) $tr .= " ("._("by date").")";
        if ($rmonth==1) $tr .= " ("._("by day").")";
        break;
      case 4: $tr = _("yearly"); break;
      }
      $tru = "";
      if ($runtil==1 && $runtild>0) $tru = " "._("until")." ".substr($runtild,0,10);
      if (count($rexclude)>0) $tru .= " "._("there is excluded days");
      $this->lay->set("repeatinfos", $tr);
      $this->lay->set("repeatuntil", $tru);
    }
  }
      
  $this->showIcons($private, $me_attendee);

  $this->ev_showattendees($ressd, $private, "lightgrey");

  $nota = str_replace("\n", "<br>", $this->getValue("CALEV_EVNOTE"));
  if ($nota!="" && !$private) {
    $this->lay->set("displaynote", "");
    $this->lay->set("NOTE", $nota);
  } else {
    $this->lay->set("displaynote", "none");
  }    
}

function showIcons($private, $withme) {
  global $action;
  $icons = array();
  $sico = $action->GetParam("WGCAL_U_RESUMEICON", 0);
  if ($sico == 1) {
  if ($private) {
    $this->addIcons($icons, "CONFID");
  } else {
    if ($this->getValue("CALEV_EVCALENDARID") > -1)  $this->addIcons($icons, "CAL_PRIVATE");
    if ($this->getValue("CALEV_VISIBILITY") == 1)  $this->addIcons($icons, "VIS_PRIV");
    if ($this->getValue("CALEV_VISIBILITY") == 2)  $this->addIcons($icons, "VIS_GRP");
    if ($this->getValue("CALEV_REPEATMODE") != 0)  $this->addIcons($icons, "REPEAT");
    if ((count($this->getTValue("CALEV_ATTID"))>1))  $this->addIcons($icons, "GROUP");
    if ($withme && ($this->getValue("CALEV_OWNERID") != $action->user->fid)) $this->addIcons($icons, "INVIT");
    if ($this->getValue("CALEV_EVALARMTIME") > 0) $this->addIcons($icons, "ALARM");
  }
  }
  $this->lay->SetBlockData("icons", $icons);
}

function addIcons(&$ia, $icol)
{
  global $action;

  $ricons = array(
     "CONFID" => array( "iconsrc" => $action->getImageUrl("wm-confidential.gif"), 
			"icontitle" => "[TEXT:confidential event]" ),
     "INVIT" => array( "iconsrc" => $action->getImageUrl("wm-invitation.gif"), 
		       "icontitle" => "[TEXT:invitation]" ),
     "VIS_PRIV" => array( "iconsrc" => $action->getImageUrl("wm-private.gif"), 
			  "icontitle" => "[TEXT:visibility private]" ),
     "VIS_GRP" => array( "iconsrc" => $action->getImageUrl("wm-privgroup.gif"), 
			 "icontitle" => "[TEXT:visibility group]" ),
     "REPEAT" => array( "iconsrc" => $action->getImageUrl("wm-repeat.gif"), 
			"icontitle" => "[TEXT:repeat event]" ),
     "CAL_PRIVATE" => array( "iconsrc" => $action->getImageUrl("wm-privatecalendar.gif"), 
			     "icontitle" => "[TEXT:private calendar]" ),
     "ALARM" => array( "iconsrc" => $action->getImageUrl("wm-alarm.gif"), 
		       "icontitle" => "[TEXT:alarm]" ),
     "GROUP" => array( "iconsrc" => $action->getImageUrl("wm-attendees.gif"), 
		       "icontitle" => "[TEXT:with attendees]" )
  );
  $ia[count($ia)] = $ricons[$icol];
}

function ev_showattendees($ressd, $private, $dcolor) {
  include_once('WGCAL/Lib.WGCal.php');
  global $action;

  $globalstate = $dcolor;
  $d = new Doc($this->dbaccess);
  $headSet = false;

  $show = true;
  if ($private) $show = false;
  else {
    if (count($ressd)==1) {
      $ownerid = $this->getValue("CALEV_OWNERID");
      if (isset($ressd[$ownerid])) $show = false;
    }
  }

  if ($show) {
    $states = WGCalGetState("");
    $this->lay->set("attdisplay","inline");

    $attoncol = 5;
    $cola = (count($ressd)> $attoncol ? 2 : 1);
    $curcol = 1;

    $t = array();
    $a = 0;
    foreach ($ressd as $k => $v) {
      if ($v["group"] == -1) {
	if ($k == $action->user->fid) {
	  $headSet = true;
	  $globalstate = WGCalGetColorState($v["state"]);
	}
	$attru = GetTDoc($action->GetParam("FREEDOM_DB"), $k);
	$t[$a]["atticon$curcol"] = $d->GetIcon($attru["icon"]);
	$t[$a]["atttitle$curcol"] = ucwords(strtolower($attru["title"]));
	$t[$a]["attnamestyle$curcol"] = ($v["state"] != EVST_REJECT ? "none" : "line-through");
	$t[$a]["attstate$curcol"] = $states[$v["state"]];
	$t[$a]["TWOCOL"] = ($cola==2 && (count($ressd)>($cola*($a+1)))? true : false );
	if ($curcol == $cola) $a++;
	$curcol = ($curcol==$cola ? 1 : $cola );
      }
    }
    $this->lay->setBlockData("attlist",$t);
  } else {
    $this->lay->set("attdisplay","none");
  }
  $this->lay->set("evglobalstate", $globalstate);
  $this->lay->set("headSet", $headSet);
  $this->lay->set("borderColor", "grey");
}


function RendezVousResume() {
  $this->RendezVousView();
}


function RendezVousEdit() {
  global $action;
  include_once('WGCAL/Lib.wTools.php');

  $fq = getIdFromName($db, "WG_AGENDA");
  $rvf = getIdFromName($db, "CALEVENT");
  $fref = $action->getParam("WGCAL_G_VFAM", $rvf);
  $this->lay->set("planid", $fq);
  $this->lay->set("idfamref", $fref);

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/DHTMLapi.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/PopupWindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/ColorPicker2.js");

  $action->parent->AddJsRef("FDL/Layout/jdate.js");

  $action->parent->AddJsRef("jscalendar/Layout/calendar.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-setup.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-fr.js");

  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_editevent.js");
  
  $cssfile = $action->GetLayoutFile("calendar-default.css");
  $csslay = new Layout($cssfile,$action);
  $action->parent->AddCssCode($csslay->gen());
  

  $nh = GetHttpVars("nh", 0);
  $times = GetHttpVars("ts", time());
  $timee = GetHttpVars("te", $times + ($action->getParam("WGCAL_U_RVDEFDUR", 60) * 60));

  if ($this->isAffected()) {
    $eventid = $this->id;
    $ownerid = $this->getValue("CALEV_OWNERID", "");
    $ownertitle = $this->getValue("CALEV_OWNER", "");
    $evtitle  = $this->getValue("CALEV_EVTITLE", "");
    $evnote   = $this->getValue("CALEV_EVNOTE", "");
    $evstart  = w_dbdate2ts($this->getValue("CALEV_START", ""));
    $evend    = w_dbdate2ts($this->getValue("CALEV_END", ""));
    $evtype   = $this->getValue("CALEV_TIMETYPE", "");
    $evfreq   = $this->getValue("CALEV_FREQUENCY", 1);
    $evcal    = $this->getValue("CALEV_EVCALENDARID", -1);
    $evvis    = $this->getValue("CALEV_VISIBILITY", 0);
    $ogrp = $this->getValue("CALEV_CONFGROUPS");
    $evalarm  = $this->getValue("CALEV_EVALARM", 0);
    $evalarmt = $this->getValue("CALEV_EVALARMTIME", 0);
    $evrepeat = $this->getValue("CALEV_REPEATMODE", 0);
    $evrweekd = $this->getTValue("CALEV_REPEATWEEKDAY", 0);
    $evrmonth = $this->getValue("CALEV_REPEATMONTH", 0);
    $evruntil = $this->getValue("CALEV_REPEATUNTIL", 0);
    $evruntild = w_dbdate2ts($this->getValue("CALEV_REPEATUNTILDATE"));
    $evrexcld  = $this->getTValue("CALEV_EXCLUDEDATE", array());
    $attendees = $this->getTValue("CALEV_ATTID", array());
    $attendeesState = $this->getTValue("CALEV_ATTSTATE", array());
    $attendeesGroup = $this->getTValue("CALEV_ATTGROUP", array());
    $evcategory = $this->getValue("CALEV_CATEGORY");
    $evstatus = EVST_READ;
    $mailadd = "";
    $withme = false;
    $onlyme = true;
    foreach ($attendees as $k => $v) {
      if ($v == $action->user->fid) {
	$evstatus = ($evstatus == EVST_NEW ? EVST_READ : $attendeesState[$k]);
	$withme = true;
      } else {
        $onlyme = false;
	$u = new Doc($action->GetParam("FREEDOM_DB"), $v);
	$m = $u->getValue("US_MAIL");
	if ($m) $mailadd .= ($mailadd==""?"":", ").$u->getValue("US_FNAME")." ".$u->getValue("US_LNAME")." <".$m.">";
      }
    }
    $rwstatus = false;
    $ro = true;
    // Compute ro mode & rostatus mode
    if ($action->user->fid == $ownerid) {
      $rostatus = false;
      $ro = false;
    } else {
      $rostatus = true;
      foreach ($attendees as $k => $v) {
        if ($action->user->fid == $v) $rostatus = false;
      }
    }
  } else {
    $eventid = -1;
    $mailadd = "";
    $evtitle  = "";
    $evnote   = "";
    $evstart  = $times;
    $evend    = $timee;
    $evtype   = $nh;
    $evfreq   = 1;
    $evcal    = -1;
    $evvis    = 0;
    $ogrp    = "-";
    $evalarm  = 0;
    $evalarmt = -1;
    $evrepeat = 0;
    $evrweekd = array();
    $evrmonth = 0;
    $evruntil = -1;
    $evruntild = $timee + (7*24*3600);
    $evrexcld  = array();
    $evstatus = EVST_ACCEPT;
    $evcategory = 0;
    $withme = true;
    $attendees = array( );
    $attendeesState = array( );
    $attendeesGroup = array( );
    $ownerid = $action->user->fid;
    $attru = GetTDoc($action->GetParam("FREEDOM_DB"), $ownerid);
    $ownertitle = $attru["title"];
    $rostatus = true;
    $ro = false;
  }

  $this->lay->set("EVENTID", $eventid);
  if ($evid==-1 || $ro) {
    $this->lay->setBlockData("EMPTY", null);
  } else {
    $this->lay->setBlockData("EMPTY", array( array("nop" => "", "eventid" => $evid) ));
    $this->lay->set("mailadd", $mailadd);
  }    
  $this->lay->set("DFMT", "%A %d %b %Y");

  $this->EventSetTitle($evtitle, $ro);
  $this->EventSetDescr($evnote, $ro);  
  $this->EventSetDate($evstart, $evend, $evtype, $ro);
  $this->EventSetVisibility($evvis, $ogrp, $ro);
  $this->EventSetCalendar($evcal, $ro);
  $this->EventSetStatus($evstatus, $withme, $onlyme, $rostatus);
  $this->EventSetAlarm($evalarm, $evalarmt, $ro);
  $this->EventSetRepeat($evrepeat, $evrweekd, $evrmonth, $evruntil, $evruntild, $evfreq, $evrexcld, $ro);
  $this->EventSetCategory($evcategory);
  $this->EventAddAttendees($ownerid, $attendees, $attendeesState, $attendeesGroup, $withme, $ro, $onlyme);
  $this->EventSetOwner($ownerid, $ownertitle);

  return;  
}    

function EventSetCategory(&$action, $evcategory) {
  global $action;
  $show = ($action->getParam("WGCAL_G_SHOWCATEGORIES",0)==1 ? true : false);
  $this->lay->set("evcategory", $evcategory);
  $this->lay->set("ShowCategories", $show);
  if ($show) {
    $catg = wGetCategories();
    $tcat = array(); $ntc = 0;
    foreach ($catg as $k => $v) {
      $tcat[$ntc]["value"] = $k;
      $tcat[$ntc]["descr"] = $v;
      $tcat[$ntc]["selected"] = ($k == $evcategory ? "selected" : "");
      $ntc++;
    }
    $this->lay->setBlockData("RVCATEGORY", $tcat);
  }
}

function EventSetTitle($title, $ro) {
  $this->lay->set("TITLE", $title);
  $this->lay->set("TITLERO", ($ro?"readonly":""));
}
function EventSetDescr($text, $ro) {
  $this->lay->set("DESCR", $text);
  $this->lay->set("DESCRRO", ($ro?"readonly":""));
}


function EventSetDate($dstart, $dend, $type, $ro) 
{
  global $action;
  $this->lay->set("NOHOURINIT", ($type==1?"checked":""));
  $this->lay->set("NOHOURRO", ($ro?"disabled":""));
  $this->lay->set("NOHOURDISP", ($type==2?"hidden":"visible"));
  $this->lay->set("ALLDAYINIT", ($type==2?"checked":""));
  $this->lay->set("ALLDAYRO", ($ro?"disabled":""));
  $this->lay->set("ALLDAYDISP", ($type==1?"hidden":"visible"));
  if ($type==1 || $type==2) $this->lay->set("HVISIBLE", "hidden");
  else $this->lay->set("HVISIBLE", "visible");
  
  
  $start_y = gmdate("Y", $dstart);
  $start_m = gmdate("m", $dstart);
  $start_d = gmdate("d", $dstart);
  $lstart = gmmktime(0,0,0,$start_m,$start_d,$start_y);
  $this->lay->set("START", $lstart);
  $this->lay->set("mSTART", $lstart*1000);
  $this->lay->set("STARTREAD", w_strftime($lstart, WD_FMT_DAYFTEXT));
  $this->lay->set("H_START", gmdate("H", $dstart));
  $th = array();
//   for ($h=$action->getParam("WGCAL_U_HSUSED",7); $h<$action->getParam("WGCAL_U_HEUSED",19); $h++) {
   for ($h=0; $h<24; $h++) {
    $th[$h]["optvalue"] = $h;
    $th[$h]["optdescr"] = (strlen($h)==1?"0".$h:$h)."h";
    $th[$h]["optselect"] = ($h==gmdate("H", $dstart)?"selected":"");
  }
  $this->lay->setBlockData("SHSEL", $th);
  $th = array();
  $incm = $action->getParam("WGCAL_U_MINCUSED",15);
  for ($h=0; $h<60; $h+=$incm) {
    $th[$h]["optvalue"] = $h;
    $th[$h]["optdescr"] = (strlen($h)==1?"0".$h:$h);
    $minu = gmdate("i", $dstart);
    $th[$h]["optselect"] = ( $h >= $minu && $minu < $h+$incm ? "" :  "selected");
  }
  $this->lay->setBlockData("SHMSEL", $th);
  $this->lay->set("M_START", gmdate("i", $dstart));
  $this->lay->set("FSTART", $dstart);
  
  $end_y = gmdate("Y", $dend);
  $end_m = gmdate("m", $dend);
  $end_d = gmdate("d", $dend);
  $lend = gmmktime(0,0,0,$end_m,$end_d,$end_y);
  $this->lay->set("END", $lend);
  $this->lay->set("mEND", $lend*1000);
  $this->lay->set("ENDREAD", w_strftime($lend, WD_FMT_DAYFTEXT));
  $this->lay->set("H_END", gmdate("H", $dend));
  $this->lay->set("M_END", gmdate("i", $dend));
  $this->lay->set("FEND", $dend);
  $th = array();
//   for ($h=$action->getParam("WGCAL_U_HSUSED",7); $h<$action->getParam("WGCAL_U_HEUSED",19); $h++) {
   for ($h=0; $h<24; $h++) {
    $th[$h]["optvalue"] = $h;
    $th[$h]["optdescr"] = (strlen($h)==1?"0".$h:$h)."h";
    $th[$h]["optselect"] = ($h==gmdate("H", $dend)?"selected":"");
  }
  $this->lay->setBlockData("EHSEL", $th);
  $th = array();
  for ($h=0; $h<60; $h+=$action->getParam("WGCAL_U_MINCUSED",15)) {
    $th[$h]["optvalue"] = $h;
    $th[$h]["optdescr"] = (strlen($h)==1?"0".$h:$h);
    $th[$h]["optselect"] = ($h>=gmdate("i", $dend-60) && $h<=gmdate("i", $dend+240)?"selected":"");
  }
  $this->lay->setBlockData("EHMSEL", $th);
 
  if ($ro) {
    $this->lay->set("DATEBUTVIS", "none");
    $this->lay->set("DATERO", "disabled");
  } else {
    $this->lay->set("DATEBUTVIS", "");
    $this->lay->set("DATERO", "");
  }
  $this->lay->set("DATEVIS", (($allday || $nohour)?"none":""));
}

function EventSetVisibility($vis, $ogrp, $ro) {
  include_once('WGCAL/Lib.WGCal.php');
  global $action;
  $avis = CAL_getEventVisibilities($this->dbaccess, "");
  $ic = 0;
  $this->lay->set("evconfidentiality", $vis);
  foreach ($avis as $k => $v) {
    $tconf[$ic]["value"] = $k;
    $tconf[$ic]["descr"] = $v;
    $tconf[$ic]["selected"] = ($vis==$k?"selected":"");
    $ic++;
  }
  $this->lay->SetBlockData("RVCONFID", $tconf);
  if ($vis==2) $this->lay->set("vis_groups", "");
  else $this->lay->set("vis_groups", "none");

  $og = false;
  if ($ogrp!="-") {
    $og = true;
    $tg = explode("|", $ogrp);
    $ugrp = array();
    foreach ($tg as $k => $v ) {
      if ($v!="") $ugrp[$v] = $v;
    }
  }
  
  // Display groups
  $glist = "";
  $u_groups = wGetUserGroups();
  $gjs = array(); $igjs=0;
  $igroups = array(); $ig=0;
  foreach ($u_groups as $k => $v) {
    $gr = new Doc($this->dbaccess, $k);
    $igroups[$ig]["gfid"] = $gr->id;
    $igroups[$ig]["gid"] = $gr->getValue("us_whatid");
    $igroups[$ig]["gtitle"] = ucwords(strtolower($gr->title));
    $igroups[$ig]["gicon"] = $gr->GetIcon();
    $igroups[$ig]["gjstitle"] = addslashes(ucwords(strtolower($gr->title)));
    $igroups[$ig]["gisused"] = ($og ? isset($ugrp[$gr->id]) : $v["sel"]);
    if ($igroups[$ig]["gisused"]) {
      $glist .= (strlen($glist)>0 ? "|" : "") . $gr->id;
      $gjs[$igjs]["igroup"] = $igjs;
      $gjs[$igjs]["gfid"] = $gr->id;
      $igjs++;
    }    
    $ig++;
  }  
  $this->lay->set("evconfgroups", $glist);
  $this->lay->setBlockData("groups", $igroups);
  $this->lay->setBlockData("GLIST", $gjs);
}
  
function EventSetCalendar($cal, $ro) {
  include_once('WGCAL/Lib.WGCal.php');
  global $action;
  $acal = WGCalGetMyCalendars($action->GetParam("FREEDOM_DB"));
  $this->lay->set("evcalendar", $cal);
  $ic = 0;
  foreach ($acal as $k => $v) {
    $tconf[$ic]["value"] = $v[0];
    $tconf[$ic]["descr"] = $v[1];
    $tconf[$ic]["selected"] = ($cal==$v[0]?"selected":"");
    $ic++;
  }
  $this->lay->SetBlockData("CALS", $tconf);
  $this->lay->set("rvcalro", ($ro?"disabled":""));
  $this->lay->set("fullattendees", ($cal==-1?"":"none"));
}

function EventSetStatus($status, $withme, $onlyme, $ro) {
  include_once('WGCAL/Lib.WGCal.php');
  global $action;
  $acal = WGCalGetState("");
  $this->lay->set("evstatus", $status);
  $ic = 0;
  if ($ro) {
    $tconf[$ic]["iState"] = $ic;
    $tconf[$ic]["vState"] = $status;
    $tconf[$ic]["rState"] = "disabled";
    $tconf[$ic]["tState"] = WGCalGetLabelState($status);
    $tconf[$ic]["cState"] = WGCalGetColorState($status);
    $tconf[$ic]["sState"] = "checked";
  } else {
    foreach ($acal as $k => $v) {
      if ($k==EVST_NEW || $k==EVST_READ) continue;
      $tconf[$ic]["iState"] = $k;
      $tconf[$ic]["vState"] = $k;
      $tconf[$ic]["rState"] = "";
      $tconf[$ic]["tState"] = WGCalGetLabelState($k);
      $tconf[$ic]["cState"] = WGCalGetColorState($k);
      $tconf[$ic]["sState"] = ($k==$status ? "checked" : "");
      $ic++;
    }
  }
  $this->lay->set("vStatus", ($withme&&!$onlyme ? "visible" : "hidden")); 
  $this->lay->SetBlockData("STATUSZ", $tconf);
  $this->lay->set("cState", WGCalGetColorState($status));
}
  
function EventSetAlarm($alarm, $alarmt, $ro) {

  $this->lay->set("ALARMCHK", ($alarm?"checked":""));
  $this->lay->set("ALARMRO", ($ro?"disabled":""));
  $this->lay->set("ALRMVIS", ($alarm?"visible":"hidden"));

  if ($alarmt>0) {
    $H = floor($alarmt / 60);
    $M = $alarmt - ($H * 60);
  } else {
    $H = 1;
    $M = 0;
  }

  $inc = 15;
  for ($min=0; $min<60; $min+=$inc) {
    $r = ($min==0?0:($min/$inc));
    $m[$r]["ALRMPERIOD_V"] = $min;
    $m[$r]["ALRMPERIOD_S"] = ($M>=$min && $min<($min+$inc)?"selected":"");
  } 
  $this->lay->SetBlockData("ALARM_MIN", $m);

  for ($hour=0; $hour<24; $hour++) {
    $h[$hour]["ALRMPERIOD_V"] = $hour;
    $h[$hour]["ALRMPERIOD_S"] = ($H==$hour?"selected":"");
  } 
  $this->lay->SetBlockData("ALARM_HR", $h);
}

function EventSetRepeat($rmode, $rday, $rmonthdate, $runtil,
			$runtildate, $freq, $recxlude = array(), $ro = false )
{

  $this->lay->set("REPEAT_SELECTED", "");
  $this->lay->set("FREQVALUE", $freq);
  
  for ($i=0; $i<=4; $i++) $this->lay->set("REPEATTYPE_".$i, ($rmode==$i?"checked":""));

  $tday = array( _("monday"), _("tuesday"),_("wenesday"),_("thursday"),_("friday"),_("saturday"), _("sunday"));
  for ($i=0; $i<=6; $i++) {
    $td[$i]["dayn"] = $i;
    $td[$i]["repeatdis"] = ($ro?"disabled":"");
    $td[$i]["tDay"] = $tday[$i];
    $td[$i]["rdstate"] = "";
    if ($i==4) $td[$i]["weekend"] = true;
    else $td[$i]["weekend"] = false;
  }
  foreach ($rday as $kd => $vd) $td[$vd]["rdstate"] = "checked";
  $this->lay->SetBlockData("D_RWEEKDISPLAY", $td);

  $this->lay->set("RWEEKDISPLAY", ($rmode==2?"":"none"));

  $this->lay->set("D_RMONTH", ($rmode==3?"":"none"));
  $this->lay->set("D_RMONTH_DATE_CHECKED", ($rmonthdate==0?"checked":""));
  $this->lay->set("D_RMONTH_DAY_CHECKED", ($rmonthdate==1?"checked":""));
  
  $this->lay->set("D_RUNTIL_INFI", ($runtil==0?"checked":""));
  $this->lay->set("D_RUNTIL_DATE", ($runtil==1?"checked":""));
  $this->lay->set("RUNUNTIL_DATE_DISPLAY", ($runtil==1?"":"none"));
  
  $this->lay->set("uDate", w_strftime($runtildate, WD_FMT_DAYLTEXT));
  $this->lay->set("umDate", $runtildate*1000);
  

  // Excluded dates
  if (is_array($recxlude) && count($recxlude)>0) {
    $ide = 0;
    foreach ($recxlude as $kd => $vd) {
      if ($vd!="" && $vd>0) {
        $ld = dbdate2ts($vd);
        $rx[$ide]["rDate"] = w_strftime($ld, WD_FMT_DAYFTEXT);
        $rx[$ide]["mDate"] = $ld;
        $rx[$ide]["iDate"] = $i;
	$ide++;
      }
    }
    if ($ide>0) $this->lay->setBlockData("EXCLDATE", $rx);
  }
  $this->lay->set("repeatvie", ($ro?"none":""));
  $this->lay->set("repeatdis", ($ro?"disabled":""));
  
}

function EventSetOwner($ownerid, $ownertitle) {
  $this->lay->set("ownerid", $ownerid);
  $this->lay->set("ownertitle", $ownertitle);
}

function EventAddAttendees($ownerid, $attendees = array(), $attendeesState = array(), $attendeesGroup = array(), $withme=true, $ro=false, $onlyme) {
//echo "ownerid = $ownerid cuser = ".$action->user->fid." withme = ".($withme?"T":"F")."<br>";
  global  $action;
  $udbaccess = $action->GetParam("COREUSER_DB");
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $ugrp = new User($udbaccess);
  $groupfid = getIdFromName($dbaccess, "GROUP");
  $igroupfid = getIdFromName($dbaccess, "IGROUP");
  $att = array();
  $a = 0;
  $tallgrp = array(); $grp = 0;
  foreach ($attendees as $k => $v) {
    if ($v == "" || $v==0 || ($ownerid==$action->user->fid&&$action->user->fid==$v) ) continue;
    if ($attendeesGroup[$k] != -1) continue;
    $res = new Doc($dbaccess, $v);
    $att[$a]["attId"]    = $v;
    $att[$a]["attSelect"]    = "true";
    $att[$a]["attState"] = $attendeesState[$k];
    $att[$a]["attTitle"] = ucwords(strtolower(($res->getTitle())));
    $att[$a]["attIcon"]  = $res->GetIcon();
    if ($res->fromid==$groupfid || $res->fromid==$igroupfid) {
      $ulist = $ugrp->GetUsersGroupList($res->getValue("US_WHATID"));
      $tugrp = array(); $rgrp = 0;
      foreach ($ulist as $ku=>$vu) {
	$rg = new Doc($dbaccess, $vu["fid"]);
        if ($rg->fromid==$groupfid || $rg->fromid==$igroupfid) continue;
	$tugrp[$rgrp]["atticon"] = $rg->GetIcon();;
	$tugrp[$rgrp]["atttitle"] = ucwords(strtolower(($rg->getTitle())));
	$cstate = "?";
	foreach ($attendees as $katt => $vatt) {
	  if ($vatt==$rg->id) $cstate = WGCalGetLabelState($attendeesState[$katt]);
	}
	$tugrp[$rgrp]["attstate"] = $cstate;
	$rgrp++;
      }
      $tallgrp[$grp]["GROUPCONTENT"] = "GROUPCONTENT$v";
      $this->lay->SetBlockData($tallgrp[$grp]["GROUPCONTENT"], $tugrp);
      $tallgrp[$grp]["RID"] = $v;
      $tallgrp[$grp]["groupicon"] = $res->getIcon();
      $tallgrp[$grp]["grouptitle"] = ucwords(strtolower(($res->getTitle())));
      $grp++;
      $att[$a]["attLabel"] = "";
      $att[$a]["attColor"] = "transparent";
    } else {
      $att[$a]["attLabel"] = WGCalGetLabelState($attendeesState[$k]);
      $att[$a]["attColor"] = WGCalGetColorState($attendeesState[$k]);
    }
    $a++;
  }
  $this->lay->setBlockData("GROUPS", $tallgrp);
  if ($a==0) {
    $this->lay->set("voneatt", "none");
    $this->lay->set("vnatt", "none");
  } else {
    $this->lay->set("voneatt", "");
    $this->lay->set("vnatt", "");
  }
  if ($ro) $this->lay->set("voneatt", "none");

  $this->lay->set("vnatt", "none");
  $this->lay->set("WITHME", "");
  $this->lay->set("WITHMERO", ($ro?"disabled":""));
  if ($ownerid==$action->user->fid) {
    if (!$onlyme) $this->lay->set("vnatt", "");
    $this->lay->set("WITHME", ($withme?"checked":""));
  }
  $this->lay->setBlockData("ADD_RESS", $att);
  $this->lay->set("attendeesro", ($ro?"none":""));

  $dress = $action->GetParam("WGCAL_U_RESSDISPLAYED", "");
  $tdress = explode("|", $dress);
  $to = array(); $ito = 0;
  $ts = array(); $its = 0;
  foreach ($tdress as $k => $v) {
    if ($v=="") continue;
    $tx = explode("%", $v);
    if ($tx[0]=="" || $tx[0]==$action->user->fid) continue;
    $res = new Doc($dbaccess, $tx[0]);
    $to[$ito]["idress"] = $ito;
    $to[$ito]["resstitle"] = addslashes(ucwords(strtolower(($res->getTitle()))));
    $to[$ito]["ressid"] = $tx[0];
    $to[$ito]["ressico"] = $res->getIcon();
    if ($tx[1] == 1) {
      $ts[$its] = $to[$ito];
      $ts[$its]["idress"] = $its;
      $its++;
    }
    $ito++;
  }
  wUSort($to, "resstitle");
  $this->lay->setBlockData("DRESS", $to);
  wUSort($ts, "resstitle");
  $this->lay->setBlockData("SRESS", $ts);

    
    
  $dress = $action->GetParam("WGCAL_U_PREFRESSOURCES", "");
  $tdress = explode("|", $dress);
  $to = array(); $ito = 0;
  foreach ($tdress as $k => $v) {
    if ($v=="" || $v==$action->user->fid) continue;
    $res = new Doc($dbaccess, $v);
    $to[$ito]["idress"] = $ito;
    $to[$ito]["resstitle"] = addslashes(ucwords(strtolower(($res->getTitle()))));
    $to[$ito]["ressid"] = $v;
    $to[$ito]["ressico"] = $res->getIcon();
    $ito++;
  }
  wUSort($to, "resstitle");
  $this->lay->setBlockData("PRESS", $to);
}