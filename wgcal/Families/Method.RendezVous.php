<?php

var $eventAttBeginDate = "CALEV_START";
var $eventAttEndDate   = "CALEV_END";
var $eventAttDesc      = "CALEV_TITLE";
var $eventAttCode      = "RV";
var $eventFamily       = "EVENT_FROM_CAL";

var $defaultview      = "WGCAL:RENDEZVOUSVIEW:T";
var $defaultabstract  = "WGCAL:RENDEZVOUSRESUME:T";
var $defaultshorttext = "WGCAL:RENDEZVOUSSHORTTEXT:T";
var $defaultlongtext  = "WGCAL:RENDEZVOUSRESUME:T";

var $defaultedit = "WGCAL:RENDEZVOUSEDIT:U";

var $vcalendarview = "WGCAL:VCALENDAR:U";

var $popup_name = 'calpopup';
var $popup_item = array('editrv', 
			'deloccur', 
			'viewrv', 
			'deleterv',
			'acceptrv', 
			'rejectrv', 
			'tbcrv', 
			'dacceptrv', 
			'drejectrv', 
			'dtbcrv', 
			'historyrv',
			'cancelrv',
			'showaccess' );
var $popup_zone = "WGCAL:WGCAL_POPUP";


function postModify() {
  $err = $this->setEvent(); 
  if ($err!="") print_r2($err);
}

function getEventOwner() {
  $uo = new_Doc($this->dbaccess, $this->getValue("CALEV_OWNERID"));
  return $uo->getValue("us_whatid");
}

function getEventRessources() {
  return $this->getTValue("CALEV_ATTID", array());
}

function  setEventSpec(&$e) {
  include_once('EXTERNALS/WGCAL_external.php');
  include_once('WGCAL/Lib.wTools.php');
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
  
  if ($this->getValue("calev_evalarm", 0)==1) {
    $htime = w_dbdate2ts($this->getValue("calev_start"));
    $hd = ($this->getValue("calev_evalarmday", 0) * 3600 * 24)
      + ($this->getValue("calev_evalarmhour", 0) * 3600)
      + ($this->getValue("calev_evalarmmin", 0) * 60);
    $e->setValue("evfc_alarmtime", w_datets2db($htime - $hd));
  }
  $e->setValue("evfc_alarm", $this->getValue("calev_evalarm", 0));
  $e->setValue("evfc_alarmd", $this->getValue("calev_evalarmday", 0));
  $e->setValue("evfc_alarmh", $this->getValue("calev_evalarmhour", 0));
  $e->setValue("evfc_alarmm", $this->getValue("calev_evalarmmin", 0));
    
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
  $tattwid = $this->getTValue("CALEV_ATTWID");
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
  
  $e->SetProfil($this->id);
}


/*
 *
 */
function mailrv() {
  $this->lay->set("rvid", $this->id);

  $uo = new_Doc($dbaccess, $this->getValue("CALEV_OWNERID"));
  $this->lay->set("rvowner", $uo->title);

  $this->lay->set("rvtitle", GetHttpVars("msg", ""));
  $this->lay->set("dstart", substr($this->getValue("CALEV_START"),0,16));
  $this->lay->set("dend", substr($this->getValue("CALEV_END"),0,16));
  $this->lay->set("EvPCard", $this->viewdoc($this->defaultview));
}

function vcalendar() {
  $uo = new_Doc($this->dbaccess, $this->getValue("CALEV_OWNERID"));

  $v = new Param($action->dbaccess, array("VERSION", PARAM_APP, $action->parent->id));
  $this->lay->set("version", $v->val);

  $this->lay->set("owner_mail", $uo->getValue("us_mail"));
  $this->lay->set("s_date", $this->_WsetDate($this->getValue("calev_start")));
  $this->lay->set("s_hour", $this->_WsetHour($this->getValue("calev_start")));
  $this->lay->set("e_date", $this->_WsetDate($this->getValue("calev_end")));
  $this->lay->set("e_hour", $this->_WsetHour($this->getValue("calev_end")));
  $this->lay->set("uid", $this->_WsetUid());
  $this->lay->set("c_date", $this->_WsetDate(w_datets2db($this->revdate)));
  $this->lay->set("c_hoursec", $this->_WsetHour(w_datets2db($this->revdate), true));
  $note = $this->getValue("calev_evnote");
  $note = str_replace("\n", "", str_replace("\r", "", $note));
  $this->lay->set("description", $note);
  $tress  = $this->getTValue("calev_attid");
  $attlist = "";
  foreach ($tress as $kr => $vr) {
    $dr = getTDoc($this->dbaccess,$vr);
    $attlist .= (strlen($attlist)>0 ? ", " : "") . ucwords(strtolower($dr["title"]));
  }
  $this->lay->set("attendees", $attlist);
  $this->lay->set("title", $this->getValue("calev_evtitle"));
}

function _WsetDate($d="") {
  $r = "";
  if ($d!="") $r = substr($d,6,4).substr($d,3,2).substr($d,0,2);
  return $r;
}
function _WsetHour($d="", $long=false) {
  $r = "";
  if ($d!="") $r = substr($d,11,2).substr($d,14,2).($long?substr($d,17,2):"");
  return $r;
}
function _WsetUid() {
  return "FREEDOM:WGCAL-".time()."-".$this->id."-".$this->_WsetDate($this->getValue("calev_start")).$this->_WsetHour($this->getValue("calev_start"), true).$this->_WsetDate($this->getValue("calev_end")).$this->_WsetHour($this->getValue("calev_end"));
}
  
/*
 *
 */

function UHaveAccess($r) { return ($this->Control($r)=="" ? true : false ); }

  
function RendezVousView() {

  include_once('WGCAL/Lib.WGCal.php');
  include_once('EXTERNALS/WGCAL_external.php');

  global $action;
  $showid = true;
  $dbaccess = $this->dbaccess;

  // matrice de ressource affichée / présentes dans le RV
  $ressd = wgcalGetRessourcesMatrix($this->id);


  $this->lay->set("ID",    $this->id);
  $myid = $action->user->fid;
  $ownerid = $this->getValue("CALEV_OWNERID");
  $conf    = $this->getValue("CALEV_VISIBILITY");
  $private = $this->isConfidential();

  $visgroup = false;
  $glist = "";
  if ($conf==2) {
    $visgroup = true;
    $ogrp = $this->getValue("CALEV_CONFGROUPS");
    $t = explode("|", $ogrp);
    foreach ($t as $k => $v ) {
      if ($v!="") {
	$du = getDocFromUserId($this->dbaccess, $v);
	$g  = new_Doc($dbaccess, $du->id);
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

  $otitle = ucwords(strtolower($this->getValue("CALEV_OWNER")));
  if ($this->getValue("calev_ownerid")!=$this->getValue("calev_creatorid") && $this->getValue("calev_creatorid")>0) $otitle .= " (".$this->getValue("calev_creator").")";
  $this->lay->set("owner", $otitle);
  $this->lay->set("ShowCategories", false);
  $this->lay->set("ShowDate", false);
  $this->lay->set("modifdate", "");
  $this->lay->set("ShowCalendar", false);
  $this->lay->set("incalendar", "");
  $this->lay->set("Confidential", $private);
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
    $title =_("confidential event");
  }
  if (wDebugMode()) $title = ($showid ? "(".$this->id.") " : "" ) . $title;
  $this->lay->set("TITLE", $title);
  
  $tress  = $this->getTValue("CALEV_ATTID");
  $tress  = $this->getTValue("CALEV_ATTWID");
  $tresse = $this->getTValue("CALEV_ATTSTATE");
  $tressg = $this->getTValue("CALEV_ATTGROUP");
  
  $me_attendee = (isset($ressd[$myid]) && $ressd[$myid]["state"]!=EVST_REJECT &&  $ressd[$myid]["displayed"]);

  
  $bgnew =  $bgresumecolor = $bgcolor = $this->evColorByOwner();
  if (isset($ressd[$myid]) && $ressd[$myid]["displayed"] && $ressd[$myid]["state"]!=-1) $bgnew = WGCalGetColorState($ressd[$myid]["state"], $bgnew);
  $this->lay->set("MeCreator", false);
  if ($this->getValue("calev_ownerid")!=$this->getValue("calev_creatorid") && $this->getValue("calev_creatorid")==$action->user->fid) {
    $mycolor = wgcalGetRColor($action->user->fid);
    $this->lay->set("mycolor", $mycolor);
    $this->lay->set("MeCreator", true);
  }

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

  $this->ev_showattendees($ressd, $private, $bgresumecolor);

  $nota = str_replace("\n", "<br>", $this->getValue("CALEV_EVNOTE"));
  if ($nota!="" && !$private) {
    $this->lay->set("displaynote", "");
    $this->lay->set("NOTE", $nota);
  } else {
    $this->lay->set("displaynote", "none");
  }    
}

/*
 *
 */
function showIcons($private, $withme) {
  global $action;
  $ricons = array( "CONFID" => array( "iconsrc" => $action->getImageUrl("wm-confidential.gif"), 
				      "icontitle" => _("icon text confidential event") ),
		   "INVIT" => array( "iconsrc" => $action->getImageUrl("wm-invitation.gif"), 
				     "icontitle" => _("icon text invitation") ),
		   "VIS_PRIV" => array( "iconsrc" => $action->getImageUrl("wm-private.gif"), 
					"icontitle" => _("icon text visibility private") ),
		   "VIS_GRP" => array( "iconsrc" => $action->getImageUrl("wm-privgroup.gif"), 
				       "icontitle" => _("icon text visibility group") ),
		   "REPEAT" => array( "iconsrc" => $action->getImageUrl("wm-icorepeat.gif"), 
				      "icontitle" => _("icon text repeat event") ),
		   "CAL_PRIVATE" => array( "iconsrc" => $action->getImageUrl("wm-privatecalendar.gif"), 
					   "icontitle" => _("icon text private calendar") ),
		   "ALARM" => array( "iconsrc" => $action->getImageUrl("wm-alarm.gif"), 
				     "icontitle" => _("icon text alarm") ),
		   "GROUP" => array( "iconsrc" => $action->getImageUrl("wm-attendees.gif"), 
				     "icontitle" => _("icon text with attendees") )
		   );
  $icons = array();
  $sico = $this->getWgcalUParam("WGCAL_U_RESUMEICON", 0);
  if ($sico == 1) {
    if ($private)  $icons[] = $ricons["CONFID"];
    else {
      if ($this->getValue("CALEV_EVCALENDARID") > -1)  $icons[] = $ricons["CAL_PRIVATE"];
      if ($this->getValue("CALEV_VISIBILITY") == 1)  $icons[] = $ricons["VIS_PRIV"];
      if ($this->getValue("CALEV_VISIBILITY") == 2)  $icons[] = $ricons["VIS_GRP"];
      if ($this->getValue("CALEV_REPEATMODE") != 0)  $icons[] = $ricons["REPEAT"];
      if ((count($this->getTValue("CALEV_ATTID"))>1))  $icons[] = $ricons["GROUP"];
      if ($withme && ($this->getValue("CALEV_OWNERID") != $action->user->fid)) $icons[] = $ricons["INVIT"];
      if ($this->getValue("CALEV_EVALARM") == 1 && ($this->getValue("CALEV_OWNERID") == $action->user->fid)) $icons[] = $ricons["ALARM"];
    }
  }
  $this->lay->SetBlockData("icons", $icons);
}


/*
 *
 */
function ev_showattendees($ressd, $private, $dcolor="") {
  include_once('EXTERNALS/WGCAL_external.php');
  include_once('WGCAL/Lib.WGCal.php');
  global $action;

  $globalstate = $dcolor;
  $d = new_Doc($this->dbaccess);
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
	  $globalstate = WGCalGetColorState($v["state"], $globalstate);
	}
	$attru = GetTDoc($action->GetParam("FREEDOM_DB"), $k);
 	$t[$a]["atticon$curcol"] = $d->GetIcon($attru["icon"]);
	$t[$a]["attcolor$curcol"] = WGCalGetColorState($v["state"]);
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


/*
 *
 */
function RendezVousResume() {
  $this->RendezVousView();
}

/*
 *
 */
function RendezVousShortText() {

  include_once('WGCAL/Lib.WGCal.php');
  global $action;

  $ressd = wgcalGetRessourcesMatrix($this->id);

  $myid = $action->user->fid;
  $ownerid = $this->getValue("CALEV_OWNERID");
  $conf    = $this->getValue("CALEV_VISIBILITY");
  $private = $this->isConfidential();

  $ownertitle = "";
  $showowner = false;
  if ($this->getValue("calev_ownerid") != $action->user->fid) {
    $ownertitle = $this->getValue("calev_owner");
    $showowner = true;
  }
  $this->lay->set("showowner", $showowner);
  $this->lay->set("ownertitle", $ownertitle);

  if ($private) {
    $title = _("confidential event");
    $headSet = false;
  } else {
    $title = $this->getValue("CALEV_EVTITLE");
    if (strlen($title)>30) $title = substr($title,0,30)."...";
  }
  $this->lay->set("TITLE", $title);
  $this->lay->set("headSet", $headSet);

  $bgresumecolor = $this->evColorByOwner();
  $this->lay->set("bgresumecolor", $bgresumecolor);
  $this->ev_showattendees($ressd, $private,$bgresumecolor);

  $lstart = substr($this->getValue("CALEV_START"),11,5);
  $lend = substr($this->getValue("CALEV_END"),11,5);
  
  switch($this->getValue("CALEV_TIMETYPE",0)) {
  case 1: 
    $this->lay->set("textdate","("._("no hour").")"); 
    break;

  case 2: 
    $this->lay->set("textdate","("._("all the day").")"); 
    break;

  default:
      $this->lay->set("textdate", $lstart." - ".$lend);
  }
}



/*
 *
 */
function RendezVousEdit() {
  global $action;
  include_once('EXTERNALS/WGCAL_external.php');
  include_once('WGCAL/Lib.wTools.php');
  include_once('FDL/freedom_util.php');
  

  $fq = getIdFromName($this->dbaccess, "WG_AGENDA");
  $rvf = getIdFromName($this->dbaccess, "CALEVENT");
 
  $this->lay->set("planid", $fq);
  $fref = $action->getParam("WGCAL_G_VFAM", $rvf);
  $if = array();
  foreach (explode("|",$fref) as $k => $v) {
    $if[] = getIdFromName($this->dbaccess, $v);
  }
  $this->lay->set("idfamref", implode("|", $if));

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
  $timee = GetHttpVars("te", $times + ($this->getWgcalUParam("WGCAL_U_RVDEFDUR", 60) * 60));

  if ($this->isAffected()) 
    {

      setHttpVar("HUL", "cancelEvent(true)");
      setHttpVar("HBUL", "cancelEvent(true)");

      $eventid = $this->id;
      $ownerid = $this->getValue("CALEV_OWNERID", "");
      $ownertitle = $this->getValue("CALEV_OWNER", "");
      $creatorid = $this->getValue("CALEV_CREATORID", $ownerid);
      $evtitle  = $this->getValue("CALEV_EVTITLE", "");
      $evnote   = $this->getValue("CALEV_EVNOTE", "");
      $evstart  = w_dbdate2ts($this->getValue("CALEV_START", ""));
      $evend    = w_dbdate2ts($this->getValue("CALEV_END", ""));
      $evtype   = $this->getValue("CALEV_TIMETYPE", "");
      $evfreq   = $this->getValue("CALEV_FREQUENCY", 1);
      $evcal    = $this->getValue("CALEV_EVCALENDARID", -1);
      $evvis    = $this->getValue("CALEV_VISIBILITY", 0);
      $ogrp = $this->getValue("CALEV_CONFGROUPS");
      $evrepeat = $this->getValue("CALEV_REPEATMODE", 0);
      $evrweekd = $this->getTValue("CALEV_REPEATWEEKDAY", 0);
      $evrmonth = $this->getValue("CALEV_REPEATMONTH", 0);
      $evruntil = $this->getValue("CALEV_REPEATUNTIL", 0);
      $evruntild = w_dbdate2ts($this->getValue("CALEV_REPEATUNTILDATE"));
      $evrexcld  = $this->getTValue("CALEV_EXCLUDEDATE", array());
      $attendees = $this->getTValue("CALEV_ATTID", array());
      $attendeesWid = $this->getTValue("CALEV_ATTWID", array());
      $attendeesState = $this->getTValue("CALEV_ATTSTATE", array());
      $attendeesGroup = $this->getTValue("CALEV_ATTGROUP", array());
      $evcategory = $this->getValue("CALEV_CATEGORY");
      $evstatus = EVST_READ;
      $mailadd = "";
      $withme = false;
      foreach ($attendees as $k => $v) {
	if ($v == $ownerid) {
	  $evstatus = ($evstatus == EVST_NEW ? EVST_READ : $attendeesState[$k]);
	  $withme = true;
	} else {
	  $u = new_Doc($action->GetParam("FREEDOM_DB"), $v);
	  $m = $u->getValue("US_MAIL");
	  if ($m) $mailadd .= ($mailadd==""?"":", ").$u->getValue("US_FNAME")." ".$u->getValue("US_LNAME")." <".$m.">";
	}
      }
      $ro = false;
      
    } 
  else 
    {
      $eventid = -1;
      $mailadd = "";
      $evtitle  = "";
      $evnote   = "";
      $evstart  = $times;
      $evend    = $timee;
      $evtype   = $nh;
      $evfreq   = 1;
      $evcal    = -1;
      $evvis    = $this->getWgcalUParam("WGCAL_U_RVDEFCONF",0);
      $ogrp    = "-";
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
      $creatorid = $action->user->fid;
      $attru = GetTDoc($action->GetParam("FREEDOM_DB"), $ownerid);
      $ownertitle = $attru["title"];
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
  
  $this->lay->set("evstatus", $evstatus);
  $this->lay->set("WITHME", ($withme?"checked":""));
  $this->lay->set("evwithme", ($withme?"1":"0"));

  $this->lay->set("TITLE", $evtitle);
  $this->lay->set("DESCR", $evnote);

  $this->lay->set("ownerid", $ownerid);
  $this->lay->set("ownertitle", $ownertitle);
  $this->lay->set("creatorid", $creatorid);

  // Compute delegation
  $ownerlist = array();
  $this->lay->set("mforuser", false);
  $this->lay->set("mforusermod", false);
  if ($eventid == -1) {
    $filter[]="( us_wgcal_dguid ~ '\\\\\\\\y(".$action->user->fid.")\\\\\\\\y' )";
    $dusers = GetChildDoc($this->dbaccess, 0, 0, "ALL", $filter, 1, "TABLE", "IUSER");
    $tdusers = array();
    if (count($dusers)>0) {
      $this->lay->set("mforuser", true);
      $tdusers[] = array( "forufid" => $action->user->fid, 
			  "foruname" => ucwords(strtolower($action->user->lastname." ".$action->user->firstname)), 
			  "foruselected" => ($ownerid==$action->user->fid ? "selected" : ""));
      $ownerlist[$action->user->fid] = $action->user->fid;
      foreach ($dusers as $k => $v) {
	if ($v!="") {
	  $tdusers[] = array( "forufid" => $v["id"], 
			      "foruname" => ucwords(strtolower($v["title"])),
			      "foruselected" => ($ownerid==$v["id"] ? "selected" : ""));
	  $ownerlist[$v["id"]] = $v["id"];
	}
      }
      $this->lay->setBlockData("foruser", $tdusers);
    } else {
      $this->lay->set("mforuser", false);
    }    
  } else {
    if ($ownerid != $action->user->fid) {
      $this->lay->set("mforusermod", true);
      $this->lay->set("foruname", $ownertitle);
    }
    $ownerlist[$ownerid] = $ownerid;
  }

  $this->EventSetDate($evstart, $evend, $evtype, $ro);
  $this->EventSetVisibility($ownerid, $ownerlist, $evvis, $ogrp, $ro);
  $this->EventSetCalendar($evcal, $ro);
  $this->EventSetAlarm();
  $this->EventSetRepeat($evrepeat, $evrweekd, $evrmonth, $evruntil, $evruntild, $evfreq, $evrexcld, $ro);
  $this->EventSetCategory($evcategory);
  $this->EventAddAttendees($ownerid, $attendees, $attendeesState, $attendeesGroup, $withme, $ro);

  return;  
}    

function EventSetCategory($evcategory) {
  global $action;
  include_once("WGCAL/Lib.wTools.php");
  $show = ($action->GetParam("WGCAL_G_SHOWCATEGORIES", 0) ==1 ? true : false);
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
  $incm = $this->getWgcalUParam("WGCAL_U_MINCUSED",15);
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
  for ($h=0; $h<60; $h+=$this->getWgcalUParam("WGCAL_U_MINCUSED",15)) {
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

function EventSetVisibility($ownerid, $ownerlist, $vis, $ogrp, $ro) {
  include_once('WGCAL/Lib.WGCal.php');
  global $action;

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
  $none = true;
  $allgroups = array(); $iall=0;
  foreach ($ownerlist as $ko => $vo) {
    $glist = "";
    $u_groups = wGetUserGroups($ko);
    if (count($u_groups)>0) {
      $gjs = array(); $igjs=0;
      $igroups = array(); $ig=0;
      foreach ($u_groups as $k => $v) {
        $gr = new_Doc($this->dbaccess, $k);
        $igroups[$ig]["gowner"] = $ko;
        $igroups[$ig]["gfid"] = $gr->id;
        $igroups[$ig]["gid"] = $gr->getValue("us_whatid");
        $igroups[$ig]["gtitle"] = ucwords(strtolower($gr->title));
        $igroups[$ig]["gicon"] = $gr->GetIcon();
        $igroups[$ig]["gjstitle"] = addslashes(ucwords(strtolower($gr->title)));
        $igroups[$ig]["gisused"] = ($og ? isset($ugrp[$gr->getValue("us_whatid")]) : $v["sel"]);
        if ($igroups[$ig]["gisused"]) {
	  $glist .= (strlen($glist)>0 ? "|" : "") . $gr->getValue("us_whatid");
	  $gjs[$igjs]["igroup"] = $igjs;
	  $gjs[$igjs]["gid"] = $gr->getValue("us_whatid");;
	  $igjs++;
        }    
        $ig++;
      }  
      $allgroups[$iall]["grange"] = $iall;
      $allgroups[$iall]["gownerid"] = $ko;
      $allgroups[$iall]["gownerdispl"] = ($ownerid==$ko?"":"none");
      $allgroups[$iall]["groups"] = $igroups;
      $allgroups[$iall]["jsgroups"] = $gjs;
      $iall++;
      $none = false;
    }    
  }
  $this->lay->set("evconfgroups", $glist);
  $this->lay->setBlockData("allgroups", $allgroups);
  $this->lay->setBlockData("jsOwnerList", $allgroups);
  foreach ($allgroups as $kg => $vg) {
    $this->lay->setBlockData($vg["gownerid"], $vg["groups"]);
    $this->lay->setBlockData("js".$vg["gownerid"], $vg["jsgroups"]);
  }
  $this->lay->setBlockData("VGLIST", $gjs);

  $avis = CAL_getEventVisibilities($this->dbaccess, "");
  $ic = 0;
  $this->lay->set("evconfidentiality", $vis);
  foreach ($avis as $k => $v) {
    if ($none && $k==2) continue;
    $tconf[$ic]["value"] = $k;
    $tconf[$ic]["descr"] = $v;
    $tconf[$ic]["selected"] = ($vis==$k?"selected":"");
    $ic++;
  }
  $this->lay->SetBlockData("RVCONFID", $tconf);
  if ($vis==2) $this->lay->set("vis_groups", "");
  else $this->lay->set("vis_groups", "none");

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

  
function EventSetAlarm() {

  $alarm_set = $this->getValue("calev_evalarm", 0);
  $this->lay->set("evalarmst", $alarm_set);
  $alarm_d   = $this->getValue("calev_evalarmday", 0);
  $this->lay->set("evalarmd",$alarm_d);
  $alarm_h   = $this->getValue("calev_evalarmhour", 1);
  $this->lay->set("evalarmh",$alarm_h);
  $alarm_m   = $this->getValue("calev_evalarmmin", 0);
  $this->lay->set("evalarmm",$alarm_m);

  $this->lay->set("ALARMCHK", ($alarm_set==1?"checked":""));
  $this->lay->set("ALRMVIS", ($alarm_set==1?"visible":"hidden"));

  for ($d=0; $d<5; $d++) 
    $da[] = array("ALRMPERIOD_V"=>$d,"ALRMPERIOD_S"=>($alarm_d==$d ? "selected" : ""));
  $this->lay->SetBlockData("ALARM_DA", $da);


  for ($hour=0; $hour<24; $hour++) 
    $h[] = array( "ALRMPERIOD_V"=>$hour, "ALRMPERIOD_S"=>($alarm_h==$hour?"selected":""));
  $this->lay->SetBlockData("ALARM_HR", $h);

  $inc = 15;
  for ($min=0; $min<60; $min+=$inc) 
    $m[] = array( "ALRMPERIOD_V" => $min, "ALRMPERIOD_S" => ($alarm_m==$m ? "selected" : "" ));
  $this->lay->SetBlockData("ALARM_MIN", $m);
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


function EventAddAttendees($ownerid, $attendees = array(), $attendeesState = array(), $attendeesGroup = array(), $withme=true, $ro=false) {
  global  $action;
  $udbaccess = $action->GetParam("COREUSER_DB");
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $sendmext = ($action->GetParam("WGCAL_G_SENDMAILS_EXTERN", 0) == 1 ? true : false);
  $this->lay->set("SendMailToExternal", $sendmext);
  $this->lay->set("mailtoexternals", "");
  $this->lay->set("evmailext", 0);
  if ($sendmext && $this->getValue("calev_attextmail", 0)==1) {
    $this->lay->set("mailtoexternals", "checked");
    $this->lay->set("evmailext", 1);
  }

  $this->lay->set("mconvo", "");
  $this->lay->set("evconvocation", 0);
  if ($this->getValue("calev_convocation", 0)==1) {
    $this->lay->set("mconvo", "checked");
    $this->lay->set("evconvocation", 1);
  }

  
  $ugrp = new User($udbaccess);
  $groupfid = getIdFromName($dbaccess, "GROUP");
  $igroupfid = getIdFromName($dbaccess, "IGROUP");
  $att = array();
  $a = 0;
  $tallgrp = array(); $grp = 0;
  $inatt = false;
  foreach ($attendees as $k => $v) {
    if ($v == "" || $v==0 || $ownerid==$v) {
      $inatt = true;
      continue;
    }
    if ($attendeesGroup[$k] != -1) continue;
    $res = new_Doc($dbaccess, $v);
    $att[$a]["attId"]    = $v;
    $att[$a]["attSelect"]    = "true";
    $att[$a]["attState"] = $attendeesState[$k];
    $att[$a]["attTitle"] = addslashes(ucwords(strtolower(($res->getTitle()))));
    $att[$a]["attIcon"]  = $res->GetIcon();
    if ($res->fromid==$groupfid || $res->fromid==$igroupfid) {
      $ulist = $ugrp->GetUsersGroupList($res->getValue("US_WHATID"));
      $tugrp = array(); $rgrp = 0;
      foreach ($ulist as $ku=>$vu) {
	$rg = new_Doc($dbaccess, $vu["fid"]);
        if ($rg->fromid==$groupfid || $rg->fromid==$igroupfid) continue;
	$tugrp[$rgrp]["atticon"] = $rg->GetIcon();;
	$tugrp[$rgrp]["atttitle"] = addslashes(ucwords(strtolower(($rg->getTitle()))));
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
      $tallgrp[$grp]["grouptitle"] = addslashes(ucwords(strtolower(($res->getTitle()))));
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
  $this->lay->set("WITHMERO", ($ro?"disabled":""));
  if ($ownerid==$action->user->fid) {
    if (!$onlyme) $this->lay->set("vnatt", "");
  }
  $this->lay->setBlockData("ADD_RESS", $att);
  $this->lay->set("attendeesro", ($ro?"none":""));

  $dress = $this->getWgcalUParam("WGCAL_U_RESSDISPLAYED", "");
  $tdress = explode("|", $dress);
  $to = array(); $ito = 0;
  $ts = array(); $its = 0;
  foreach ($tdress as $k => $v) {
    if ($v=="") continue;
    $tx = explode("%", $v);
    if ($tx[0]=="" || $tx[0]==$ownerid) continue;
    if (wGetiUserCalAccessMode($tx[0]) != 1) continue;
    $res = new_Doc($dbaccess, $tx[0]);
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

    
    
  $dress = $this->getWgcalUParam("WGCAL_U_PREFRESSOURCES", "");
  $tdress = explode("|", $dress);
  $to = array(); $ito = 0;
  foreach ($tdress as $k => $v) {
    if ($v=="" || $v==$ownerid) continue;
    if (!wUserHaveCalVis($v, 1)) continue;
    $res = new_Doc($dbaccess, $v);
    $to[$ito]["idress"] = $ito;
    $to[$ito]["resstitle"] = addslashes(ucwords(strtolower(($res->getTitle()))));
    $to[$ito]["ressid"] = $v;
    $to[$ito]["ressico"] = $res->getIcon();
    $ito++;
  }
  wUSort($to, "resstitle");
  $this->lay->setBlockData("PRESS", $to);
}

function evColorByOwner() {

  global $action;
  $ressd = wgcalGetRessourcesMatrix($this->id);
  $myid = $action->user->fid;
  $ownerid = $this->getValue("CALEV_OWNERID");

  // Si je suis convié / j'ai refusé / affichable => Ma couleur
  // Si le propriétaire est dans les affichables / pas refusé => Couleur du propriétaire
  // Si le propriétaire n'est pas affichable => Couleur du premier convié ET AFFICHE et pas refusé.... 
  $showrefused = $this->getWgcalUParam("WGCAL_U_DISPLAYREFUSED", 0);
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
	if ($v["state"]!=EVST_REJECT && $v["displayed"]) {
	  $event_color = $v["color"];
	}
      }
    }
  }
  $event_color = ($event_color!=""?$event_color:"#d2f5f7");
  return $event_color;
}

function RvSetPopup($rg) {
  include_once('WGCAL/Lib.wTools.php');
  global $action;

  PopupInvisible($this->popup_name,$rg, 'acceptrv');
  PopupInvisible($this->popup_name,$rg, 'rejectrv');
  PopupInvisible($this->popup_name,$rg, 'tbcrv');
  PopupInvisible($this->popup_name,$rg, 'dacceptrv');
  PopupInvisible($this->popup_name,$rg, 'drejectrv');
  PopupInvisible($this->popup_name,$rg, 'dtbcrv');
  PopupInvisible($this->popup_name,$rg, 'historyrv');
  PopupInvisible($this->popup_name,$rg, 'viewrv');
  PopupInvisible($this->popup_name,$rg, 'deloccur');
  PopupInvisible($this->popup_name,$rg, 'editrv');
  PopupInvisible($this->popup_name,$rg, 'deleterv');
  PopupInvisible($this->popup_name,$rg, 'cancelrv');
  PopupInvisible($this->popup_name,$rg, 'showaccess');

  // Delegation for acceptation
  $delegate = -1;
  $filter[]="(id = ".$this->getValue("calev_ownerid")." ) and ( us_wgcal_dguid ~ '\\\\\\\\y(".$action->user->fid.")\\\\\\\\y' )";
  $dusers = GetChildDoc($this->dbaccess, 0, 0, "ALL", $filter, 1, "TABLE", "IUSER");
  if (count($dusers)>0) {
    foreach ($dusers as $k => $v) {
      $duid = Doc::_val2array($v["us_wgcal_dguid"]);
      $dumode = Doc::_val2array($v["us_wgcal_dgumode"]);
      foreach ($duid as $ku => $vu) {
	if ($vu == $action->user->fid ) $delegate =  $dumode[$ku];
      }
    }
  }

  if ($delegate==1 || ($delegate==0 && ($this->getValue("calev_creatorid")==$action->user->fid))) {
    $ownerstate = $this->RvAttendeeState($this->getValue("calev_ownerid"));
    if ($ownerstate>-1 && $ownerstate!=2) PopupActive($this->popup_name,$rg, 'dacceptrv');
    if ($ownerstate>-1 && $ownerstate!=3) PopupActive($this->popup_name,$rg, 'drejectrv');
    if ($ownerstate>-1 && $ownerstate!=4) PopupActive($this->popup_name,$rg, 'dtbcrv');
  }


  if (wDebugMode())   if ($this->UHaveAccess('viewacl')) PopupActive($this->popup_name,$rg, 'showaccess');
  
  if ($this->UHaveAccess("confidential") || ($this->confidential==0 && $this->UHaveAccess("view")) ) {
    PopupActive($this->popup_name,$rg, 'historyrv');
    PopupActive($this->popup_name,$rg, 'viewrv');
  }

  if ($this->UHaveAccess("execute")) {
    $mystate = $this->RvAttendeeState($action->user->fid);
    if ($mystate>-1 && $mystate!=2) PopupActive($this->popup_name,$rg, 'acceptrv');
    if ($mystate>-1 && $mystate!=3) PopupActive($this->popup_name,$rg, 'rejectrv');
    if ($mystate>-1 && $mystate!=4) PopupActive($this->popup_name,$rg, 'tbcrv');
  }

  if ($this->UHaveAccess("edit")) {
    PopupActive($this->popup_name,$rg, 'editrv');
  }
    
  if ($this->UHaveAccess("delete")) {
    PopupActive($this->popup_name,$rg, 'deleterv');
    if ($this->getValue("calev_repeatmode") > 0) PopupActive($this->popup_name,$rg, 'deloccur');
  }
      
}

function RvAttendeeState($ufid) {
  $state = -1;
  $attr = $this->getTValue("calev_attid");
  $attrst = $this->getTValue("calev_attstate");
  if (count($attr)>1) {
    foreach ($attr as $ka => $va) {
      if ($va==$ufid) {
	$state = $attrst[$ka];
      }
    }
  }
  return $state;
}


function getWgcalUParam($pname, $def="", $uid=-1) {
  global $action;
  $uid = ($uid==-1 ? $action->user->id : $uid);
  $r = $action->parent->param->getUParam($pname, $uid, $action->parent->GetIdFromName("WGCAL"));
  return ($r=="" ? $def : $r);
}


function resetAcceptStatus() {
  include_once("WGCAL/Lib.WGCal.php");
  $att_ids = $this->getTValue("CALEV_ATTID");
  if (count($att_ids)>0) {
    $att_wid = $this->getTValue("CALEV_ATTWID");
    $att_sta = $this->getTValue("CALEV_ATTSTATE");
    $att_grp = $this->getTValue("CALEV_ATTGROUP");
    foreach ($att_ids as $k => $v) {
      if ($att_grp[$k]==-1 && $att_sta[$k] != -1) {
	if ($v == $this->getValue("calev_ownerid")) $att_sta[$k] = EVST_ACCEPT;
	else $att_sta[$k] = EVST_NEW;
      }
    }
    $this->setValue("CALEV_ATTSTATE", $att_sta);
    $err = $this->Modify();
    if ($err=="") $err = $this->PostModify();
    if ($err!="") AddWarningMsg(__FILE__."::".__LINE__.">$err");
  }
}
      
function APISetAccess() {
   setHttpVar("fprof",1);
   $this->setConfidentiality();
   $this->setAccessibility();
}

function setConfidentiality() {
   if ($this->isAffected()) {
     if ($this->getValue("calev_visibility")>0 ) $this->confidential = 1;
     else $this->confidential = 0;
     $this->Modify();
   }
}

function setAccessibility() {

  include_once("WGCAL/Lib.wTools.php");

  define("DEFGROUP", 2);

  // Force profil and acl setting
  $fprof = GetHttpVars("fprof", 0);

  if (!$this->isAffected()) return;

  $aclv = array();
  $aclv["all"] = array( "view"=>"view", 
			"confidential"=>"confidential", 
			"send"=>"send", 
			"edit"=>"edit", 
			"delete"=>"delete", 
			"execute"=>"execute", 
			"unlock"=>"unlock",
			"viewacl"=>"viewacl", 
			"modifyacl"=>"modifyacl");
  $aclv["none"] = array();
  $aclv["edit"] = array( "edit"=>"edit", 
			 "unlock"=>"unlock",
			 "execute"=>"execute", 
			 "viewacl"=>"viewacl", 
			 "modifyacl"=>"modifyacl",
			 "confidential"=>"confidential",
			 "view"=>"view" );
  $aclv["read"] = array( "view"=>"view");
  $aclv["read_state"] = array( "view"=>"view", 
			       "execute"=>"execute");
  $aclv["read_conf"] = array( "view"=>"view", 
			      "confidential"=>"confidential");
  $aclv["read_conf_state"] = array( "view"=>"view", 
				    "confidential"=>"confidential", 
				    "execute"=>"execute");

  $acl = array();
  

//   if ($fprof==1) echo "Force profil setting...";

  if ($this->profid==0 || $fprof==1) {
    $this->disableEditControl();
    $this->SetProfil($this->id);
    $this->SetControl();
    $err = $this->Modify();
    if ($err!="") AddWarningMsg(__FILE__."::".__LINE__."> $err");
    $this->enableEditControl();
  }  

  $ownerid = $this->getValue("calev_ownerid");
  $creatorid = $this->getValue("calev_creatorid");
  $userf = getTDoc($this->dbaccess, $ownerid);
  $ownerwid = $userf["us_whatid"];
  $dcrea = getTDoc($this->dbaccess, $creatorid);
  $creatorwid = $dcrea["us_whatid"];
 
  $conf    = $this->getValue("calev_visibility");
  
  $sdeb = "Confidential=".$this->getValue("confidential")."\nRV Confidentiality=[$conf]\nAgenda visibility:".($userf["us_wgcal_vcalgrpmode"]==1?"Groups":"Public")."\n";
  
  
  // User agenda visibility
  $vcalrestrict = false;
  $vcal = array();
  $calgvis = $userf["us_wgcal_vcalgrpmode"];
  if ($calgvis == 1) {
    $vcalrestrict = true;
    $tvcal = Doc::_val2array($userf["us_wgcal_vcalgrpwid"]);
    foreach ($tvcal as $k => $v) $vcal[$v] = $v; 
  } else {
    $vcal[DEFGROUP] = DEFGROUP;
  }
  
  $tcfg = explode("|", $this->getValue("calev_confgroups", ""));
  $rvcgroup = array();
  foreach ($tcfg as $k => $v) {
    if ($v!="") $rvcgroup[$v] = $v;
  }
  
  
  // foreach attendees (except owner) get agenda groups
  $attendeesid = $this->getTValue("calev_attid");
  $attendeeswid = $this->getTValue("calev_attwid");
  $attendeesstate = $this->getTValue("calev_attstate");
  $attgrps = array();
  foreach ($attendeesid as $k => $v) {
    if ($attendeesstate[$k]!=-1) {
      $ugrp = wGetUserGroups($v);
      if (count($ugrp)>0) {
	foreach ($ugrp as $kg => $vg) {
	  $thisg = getTDoc($this->dbaccess, $kg);
	  if (!isset($rvcgroup[$thisg["us_whatid"]])) {
	    $attgrps[$thisg["us_whatid"]] = $thisg["us_whatid"];
	  }
	}
      }
    }
  }

  $aclvals = array();
  foreach ($aclv as $ka => $va) {
    foreach ($aclv["all"] as $kr => $vr) {
      if (isset($va[$kr])) $aclvals[$ka][$kr] = $this->dacls[$kr]["pos"];
      else $aclvals[$ka][$kr] = 0;
    }
  }


  $acls = array();

  switch ($conf) {
  case 1: // Private
    if ($calgvis==0) $acls[2] = $aclvals["read"];
    else $acls[2] = $aclvals["none"];
    foreach ($vcal as $k => $v) $acls[$k] = $aclvals["read"] ;
    foreach ($attgrps as $k => $v) $acls[$k] = $aclvals["read"];
    break;
    
  case 2: // My groups
    $acls[2] = $aclvals["read"];
    foreach ($vcal as $k => $v) $acls[$k] = $aclvals["read"];
    foreach ($attgrps as $k => $v) $acls[$k] = $aclvals["read"];
    foreach ($rvcgroup as $k => $v) $acls[$k] = $aclvals["read_conf"];
    break;
    
  default: // Public
    if ($calgvis==0)     $acls[2] = $aclvals["read"];
    else $acls[2] = $aclvals["none"];
    foreach ($vcal as $k => $v) $acls[$k] =  $aclvals["read"];
    foreach ($attgrps as $k => $v) {
      if ($calgvis==0)  $acls[$k] = $aclvals["read"];
      else $acls[$k] = $aclvals["read"];
    }
  }
  
  // Attendees -> read, confidential and execute at least
  foreach ($attendeeswid as $k => $v) {
    if ($attendeesstate[$k]!=-1) {
      if ($v!=$ownerwid && $v!=$creatorwid) $acls[$v] = $aclvals["read_conf_state"];
    }
  }

  // Owner, creator and delegate ==> owner rights
  $acls[$ownerwid] = $aclvals["all"];
  if ($creatorid!=$ownerid) $acls[$creatorwid] = $aclvals["all"];
  $duid = Doc::_val2array($userf["us_wgcal_dguid"]);
  if (count($duid)>0) {
    $duwid = Doc::_val2array($userf["us_wgcal_dguwid"]);
    $dumode = Doc::_val2array($userf["us_wgcal_dgumode"]);
    foreach ($duid as $k=>$v) {
      if ($dumode[$k] == 1) $acls[$duwid[$k]] = $aclvals["all"];
    }
  }


  $this->RemoveControl();
  foreach ($acls as $user => $uacl) {
    if ($user!="") {
      $dt = getDocFromUserId($this->dbaccess,$user);
      $sdeb .= "[".$dt->GetTitle()."($user)] = ";
      $perm = new DocPerm($this->dbaccess, array($this->id,$user));
      $perm->UnSetControl();
      foreach ($uacl as $k => $v) {
	$sdeb .= "$k:$v ";
	if (intval($v) > 0)  {
	  $perm->SetControlP($v);
	} else {
	  $perm->SetControlN($v);
	}	
      }
      if ($perm->isAffected()) 
	$perm->modify();
      else 
	$perm->Add();
      $sdeb .= "\n";
    }
  }
//     AddWarningMsg(  $sdeb );
}
