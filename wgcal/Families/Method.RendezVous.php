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

var $sifeview = "WGCAL:SIFEVENT:U";


function getCurTime() {
  $t = mktime();
  $trs = getParam("TIMEREF_SYNC4J", 0);
  $trf = getParam("TIMEREF_FREEDOM", 0);
  $td = array( "sync4j" => gmdate("d/m/Y H:i:s", $t - ($trf - $trs))." UTC", "freedom" =>  gmdate("d/m/Y H:i:s", $t)." UTC");
  return $td;
}

function preDelete() {
  $ctime = $this->getCurTime();
  $this->setValue("calev_s4j_mtime", $ctime["sync4j"]);
  $this->modify(true, array("calev_s4j_mtime"), true);
}

function postModify($settime=true) {
  if (!$this->fromS4j && $settime) {
    $ctime = $this->getCurTime();
    $this->setValue("calev_s4j_mtime", $ctime["sync4j"]);
    $this->modify(true, array("calev_s4j_mtime"), true);
  }
  $err = $this->setEvent(); 
  if ($err!="") print_r2($err);
}

function postCreated() {
  if (!$this->fromS4j) {
    $ctime = $this->getCurTime();
    $this->setValue("calev_s4j_mtime", $ctime["sync4j"]);
    $this->setValue("calev_s4j_ctime", $ctime["sync4j"]);
    $this->modify(true, array("calev_s4j_ctime", "calev_s4j_mtime"), true);
  }
  $err = $this->setSync4jGuid(); 
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

  $e->setValue("evt_idcreator", $this->getValue("calev_ownerid"));
  $e->setValue("evt_creator", $this->getValue("calev_owner"));
  $e->setValue("evfc_dcreatorid", $this->getValue("calev_creatorid"));
  $e->setValue("evfc_idowner", $this->getValue("calev_ownerid"));
  $e->setValue("evfc_idowner", $this->getValue("calev_ownerid"));
  $e->setValue("evt_desc", $this->getValue("calev_evnote"));
  $e->setValue("evt_code", $this->getValue("calev_category"));
  $e->setValue("evfc_location", $this->getValue("calev_location"));
  $e->setValue("evfc_visibility", $this->getValue("calev_visibility"));
  $e->setValue("evfc_realenddate", $this->getValue("calev_end"));
  $e->setValue("evfc_repeatmode", $this->getValue("calev_repeatmode"));
  $e->setValue("evfc_repeatweekday", $this->getValue("calev_repeatweekday"));
  $e->setValue("evfc_repeatmonth", $this->getValue("calev_repeatmonth"));
  $e->setValue("evfc_repeatuntil", $this->getValue("calev_repeatuntil"));
  $e->setValue("evfc_repeatuntildate", $this->getValue("calev_repeatuntildate"));

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
    
  $tv = $this->getTValue("calev_excludedate");
  $e->deleteValue("evfc_excludedate");
  if (count($tv)>0) {
    foreach ($tv as $kv => $vv) {
      $texc[] = $vv;
    }
    $e->setValue("evfc_excludedate", $texc);
  }
  $e->setValue("evfc_repeatfreq", $this->getValue("calev_frequency"));
  if ($this->getValue("calev_repeatmode") > 0) {
    $renddate = ($this->getValue("calev_repeatuntil")==1 ? $this->getValue("calev_repeatuntildate") : jd2cal((5000001), 'FrenchLong') );
    $e->setValue("evt_enddate", $renddate);
  }


  $tattid = $this->getTValue("calev_attid");
  $tattwid = $this->getTValue("calev_attwid");
  $tattst = $this->getTValue("calev_attstate");
  $tattgp = $this->getTValue("calev_attgroup");
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
  if (count($nattid)==0) $e->deleteValue("evfc_listattid");
  else {
    $e->setValue("evfc_listattid", $nattid);
    $e->setValue("evfc_listattst", $nattst);  
  }
  if (count($rejattid)==0)  $e->deleteValue("evfc_rejectattid");  
  else $e->setValue("evfc_rejectattid", $rejattid);  

  $e->setValue("evfc_calendarid", $this->getValue("calev_evcalendarid"));
  
  $icons = $this->setIcons();
  $icol = "";
  foreach ($icons as $k => $v) $icol .= ($icol==""?"":"|").$v["code"];
  $icol = ($icol=="" ? " " : $icol);
  $e->setValue("evfc_iconlist", $icol);
  $e->confidential = $this->confidential;
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

function _WsetDateTime($d="") {
  $r = "";
  $r = $this->_WsetDate($d)."T".$this->_WsetHour($d)."00Z";
  return $r;
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

  $textvis = "Ooops";
  $visgroup = false;
  $glist = "";
  switch ($conf) {
  case 0 : // Public
    $textvis = _("public");
    break;
  case 1 : // Confidential
    $textvis = _("confidential");
    break;
  case 2 : // Groups
    $textvis = _("view groups");
    $visgroup = true;
    $ogrp = $this->getValue("CALEV_CONFGROUPS");
    $t = explode("|", $ogrp);
    foreach ($t as $k => $v ) {
      if ($v!="") {
	$du = getDocFromUserId($this->dbaccess, $v);
        if (!$du) continue;
	$glist .= ($glist=="" ? "" : ", " ) . ucwords(strtolower($du->title));
      }
    }
    break;
  case 3: // private
    $textvis = _("private");
    break;
  default: // Ooops
    $textvis = "Ooops";
  }
  $this->lay->set("TextVisibility", $textvis);
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

  $this->lay->set("v_location", false);
  if ($this->getValue("calev_location")!="") {
    $this->lay->set("v_location", true);
    $this->lay->set("rvlocation", $this->getValue("calev_location"));
  }
  $this->lay->set("iconevent", $this->getIcon($this->icon));

  $otitle = ucwords(strtolower($this->getValue("CALEV_OWNER")));
  if ($this->getValue("calev_ownerid")!=$this->getValue("calev_creatorid") && $this->getValue("calev_creatorid")>0) $otitle .= " (".$this->getValue("calev_creator").")";
  $this->lay->set("owner", $otitle);
  $this->lay->set("ShowDate", false);
  $this->lay->set("modifdate", "");
  $this->lay->set("ShowCalendar", false);
  $this->lay->set("incalendar", "");
  $this->lay->set("Confidential", $private);
  if (!$private) {
    $this->lay->set("ShowDate", true);
    $this->lay->set("modifdate", strftime("%d %B %Y %H:%M",$this->revdate));
    $this->lay->set("incalendar", $this->getValue("CALEV_EVCALENDAR"));
    $this->lay->set("hasCat", false);
    $catg = wGetCategories();
    $cat = $this->getValue("CALEV_CATEGORY");
    if ($cat>0) {
      foreach ($catg as $k=>$v) {
	if ($v["id"] == $cat) {
	  $this->lay->set("category", $v["label"]);
	  $this->lay->set("catcolor", $v["color"]);
	  $this->lay->set("hasCat", true);
	}
      }
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
  $this->lay->set("s_repeatmoreinfos", false);
  $tday = array( _("monday"), _("tuesday"),_("wenesday"),_("thursday"),_("friday"),_("saturday"), _("sunday"));
  if (!$private) {
    $rmode = $this->getValue("CALEV_REPEATMODE", 0);
    $rday = $this->getValue("CALEV_REPEATWEEKDAY", 0);
    $rmonth = $this->getValue("CALEV_REPEATMONTH", 0);
    $runtil = $this->getValue("CALEV_REPEATUNTIL", 0);
    $runtild = $this->getValue("CALEV_REPEATUNTILDATE", "");
    $rexclude = $this->getValue("CALEV_EXCLUDEDATE", array());
    if ($rmode>0 && $rmode<5) {
      $this->lay->set("repeatdisplay", "inherit");
      $tr2 = "";
      switch ($rmode) {
      case 1:
        $tr = ucwords(_("dayly"));
        break;
      case 2:
        $tr = ucwords(_("weekly"));
	$tr2 = "(";
	for ($i=0; $i<=6; $i++) $tr2 .= ( ($rday & pow(2,$i)) == pow(2,$i) ? $tday[$i]." " : "" );
	$tr2 .= ")";
        break;
      case 3:
        $tr = ucwords(_("monthly"));
	$day = substr($this->getValue("CALEV_START"),0,2);
	if ($rmonth!=1) {
	  $tr2 = "($day du mois)";
	} else {
	  $occur = wComputeNWeekDayInMonth($this->getValue("CALEV_START"));
	  $ts = w_dbdate2ts($this->getValue("CALEV_START", ""));
	  $dayn = strftime("%u", $ts);
	  $dayt = strftime("%A", $ts);
	  $tr2 = "($occur".($occur>1?"ième":"ier")." $dayt du mois)";
	}
        break;
      case 4: 
	$tr = ucwords(_("yearly"));
 	$day = substr($this->getValue("CALEV_START"),0,2);
 	$month = substr($this->getValue("CALEV_START"),3,2);
	$ts = w_dbdate2ts($this->getValue("CALEV_START", ""));
	$dayn = strftime("%u", $ts);
	$dayt = strftime("%A", $ts);
	$montht = strftime("%B", $ts);
	if ($rmonth!=1) {
	  $tr2 = "(le $day $montht)";
	} else {
	  $rday = 0;
	  $cancel = false;
	  while (!$cancel) {
	    if ($day-($rday*7)>0) $rday++;
	    else $cancel = true;
	  }
	  $tr2 = "($rday".($rday>1?"ème":"er")." $dayt de $montht)";
	}
	break;
      }
      $tru = "";
      if ($runtil==1 && $runtild>0) $tru = " "._("until")." ".substr($runtild,0,10);
      if (count($rexclude)>0) $tru .= " <br>("._("there is excluded days").")";
      $this->lay->set("repeatinfos", $tr);
      $this->lay->set("s_repeatmoreinfos", (strlen($tr2)>0?true:false));
      $this->lay->set("repeatmoreinfos", $tr2);
      $this->lay->set("repeatuntil", $tru);
    }
  }
      
  $sico = $this->getWgcalUParam("WGCAL_U_RESUMEICON", 1);
  if ($sico==1) {
    $icons = $this->setIcons();
    $this->lay->SetBlockData("icons", $icons);
  } else {
    $this->lay->SetBlockData("icons", null);
  }

  $this->ev_showattendees($ressd, $private, $bgresumecolor);

  $nota = str_replace("\n", "<br>", $this->getValue("CALEV_EVNOTE"));
  if ($nota!="" && !$private) {
    $this->lay->set("displaynote", true);
    $this->lay->set("NOTE", $nota);
  } else {
    $this->lay->set("displaynote", false);
  }    
}

/*
 *
 */
function setIcons() {
  include_once('WGCAL/Lib.wTools.php');
  global $action;
  $myid = $action->user->fid;
  $icons = array();
  if ($this->UHaveAccess("confidential") || ($this->confidential==0 && $this->UHaveAccess("view")) ) {
    if ($this->getValue("CALEV_EVCALENDARID") > -1)  $icons[] = fcalGetIcon("CAL_PRIVATE");
    if ($this->getValue("CALEV_VISIBILITY") == 1)  $icons[] = fcalGetIcon("VIS_CONFI");
    if ($this->getValue("CALEV_VISIBILITY") == 2)  $icons[] = fcalGetIcon("VIS_GRP");
    if ($this->getValue("CALEV_VISIBILITY") == 3)  $icons[] = fcalGetIcon("VIS_PRIV");
    if ($this->getValue("CALEV_REPEATMODE") != 0)  {
      $texcl = $this->getTValue("calev_excludedate");
      if (!is_array($texcl) || count($texcl)==0) $icons[] = fcalGetIcon("REPEAT");
      else $icons[] = fcalGetIcon("REPEATEXCLUDE");
    }
    if ((count($this->getTValue("CALEV_ATTID"))>1))  $icons[] = fcalGetIcon("GROUP");
    if ($this->getValue("CALEV_EVALARM") == 1 && ($this->getValue("CALEV_OWNERID") == $action->user->fid)) $icons[] = fcalGetIcon("ALARM");
  } else {
    $icons[] = fcalGetIcon("CAL_PRIVATE");
  }
  return $icons;
}


/*
 *
 */
function ev_showattendees($ressd, $private, $dcolor="") {
  include_once('EXTERNALS/WGCAL_external.php');
  include_once('WGCAL/Lib.WGCal.php');
  global $action;

  $dbaccess = $action->getParam("FREEDOM_DB");

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
	$imgaccess = $d->GetIcon($attru["icon"]);
	if (strncmp($imgaccess,"FDL",3)==0) $t[$a]["atticon$curcol"] = $action->getParam("CORE_ABSURL")."/".$imgaccess;
	else $t[$a]["atticon$curcol"] = $imgaccess;
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
  include_once('WGCAL/Lib.Agenda.php');
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

  $action->parent->AddJsRef("FDL/Layout/popupdoc.js");  
  $action->parent->AddCssRef("FDL:POPUP.CSS",true);

  $action->parent->AddJsRef("jscalendar/Layout/calendar.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-setup.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-fr.js");

  $action->parent->AddJsRef("WGCAL:wgcal.js", true);
  $action->parent->AddJsRef("WGCAL:wgcal_editevent.js", true);
  $action->parent->AddJsRef("WGCAL:wgcal_searchcontacts.js", true);
  
  $this->lay->set("checkConflict", $action->getParam("WGCAL_U_CHECKCONFLICT", 1));

  $nh = GetHttpVars("nh", 0);

//   $times = GetHttpVars("ts", strftime("%s", time()));
//   $timee = GetHttpVars("te", $times+3600);
  $times =  strftime("%s", time());
  $timee =  $times+3600;

  $withress = GetHttpVars("wress", "");

  if ($this->isAffected()) 
    {

      setHttpVar("HBUL", "if (!DocumentSaved) return closeMsg;");

      $eventid = $this->id;
      $ownerid = $this->getValue("CALEV_OWNERID", "");
      $ownertitle = $this->getValue("CALEV_OWNER", "");
      $creatorid = $this->getValue("CALEV_CREATORID", $ownerid);
      $evtitle  = GetHttpVars("ti", $this->getValue("CALEV_EVTITLE", ""));
      $evnote   = GetHttpVars("no", $this->getValue("CALEV_EVNOTE", ""));
      $evlocation = GetHttpVars("lo", $this->getValue("calev_location"));
//       $evstart  = $times;
//       $evend    = $timee;
      $evstart  = GetHttpVars("ts", w_dbdate2ts($this->getValue("CALEV_START", "")));
      $evend    = GetHttpVars("te", w_dbdate2ts($this->getValue("CALEV_END", "")));
      $evtype   = $this->getValue("CALEV_TIMETYPE", "");
      $evfreq   = $this->getValue("CALEV_FREQUENCY", 1);
      $evcal    = $this->getValue("CALEV_EVCALENDARID", -1);
      $evvis    = GetHttpVars("co", $this->getValue("CALEV_VISIBILITY", 0));
      $ogrp = $this->getValue("CALEV_CONFGROUPS");
      $evrepeat = $this->getValue("CALEV_REPEATMODE", 0);
      $evrweekd = $this->getValue("CALEV_REPEATWEEKDAY", pow(2, gmdate("w",$evstart)-1));
      $evrmonth = $this->getValue("CALEV_REPEATMONTH", 0);
      $evruntil = $this->getValue("CALEV_REPEATUNTIL", 0);
      $tt = $this->getValue("CALEV_REPEATUNTILDATE");
      $evruntild = mktime(w_dbhou($tt), w_dbmin($tt), w_dbsec($tt), w_dbmon($tt), w_dbday($tt), w_dbyea($tt));
      $evrexcld  = $this->getTValue("CALEV_EXCLUDEDATE", array());
      $attendees = $this->getTValue("CALEV_ATTID", array());
      $attendeesWid = $this->getTValue("CALEV_ATTWID", array());
      $attendeesState = $this->getTValue("CALEV_ATTSTATE", array());
      $attendeesGroup = $this->getTValue("CALEV_ATTGROUP", array());
      $evcategory = GetHttpVars("ca", $this->getValue("CALEV_CATEGORY"));
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
    } 
  else 
    {

      $eventid = -1;
      $mailadd = "";
      $evtitle  = GetHttpVars("ti","");
      $evnote   = GetHttpVars("no","");
      $evstart  = GetHttpVars("ts", $times);
      $evend    = GetHttpVars("te", $times);
//       $evstart  = $times;
//       $evend    = $timee;
      $evtype   = $nh;
      $evfreq   = 1;
      $evlocation = GetHttpVars("lo", "");
      $evcal    = -1;
      $evvis    = GetHttpVars("co", $this->getWgcalUParam("WGCAL_U_RVDEFCONF",0));
      $ogrp    = "-";
      $evrepeat = 0;
      $evrweekd = pow(2, gmdate("w",$evstart)-1);
      $evrmonth = 0;
      $evruntil = 0;
      $evruntild = $timee + (7*24*3600);
      $evrexcld  = array();
      $evstatus = EVST_ACCEPT;
      $evcategory = GetHttpVars("ca", "");
      $withme = true;
      $attendees = array( );
      $attendeesState = array( );
      $attendeesGroup = array( );
      if ($withress!="") {
	$tress = explode("|", $withress);
        foreach ($tress as $k => $v) {
          if ($v=="") continue;
          $u = GetTDoc($action->GetParam("FREEDOM_DB"), $v);
          $attendees[] = $v;
          $attendeesState[] = EVST_NEW;
          $attendeesWid = $u["us_whatid"];
          $attendeesGroup[] = -1;
        }
      }
      $ownerid = $this->getWgcalUParam("WGCAL_U_DCALEDIT", $action->user->fid);
      $creatorid = $action->user->fid;
      $attru = GetTDoc($action->GetParam("FREEDOM_DB"), $ownerid);
      $ownertitle = $attru["title"];
    }

  $this->lay->set("EVENTID", $eventid);

  $this->lay->set("newEvent", false);
  if ($eventid==-1) $this->lay->set("newEvent", true);
  
  $this->lay->set("sendMail", false);
  if ($mailadd!="") {
    $command = $action->getParam("WGCAL_G_MAILTO", "mailto:%TO%");
    $mailcommand = str_replace(array("%TO%", "%SUBJECT%"), array($mailadd, $evtitle), $command);
    $this->lay->set("mailcommand", $mailcommand);
    $this->lay->set("sendMail", true);
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
  // ---------------------------------------------------
  $ownerlist = array();
  $this->lay->set("mforuser", false);
  $this->lay->set("mforusermod", false);
  $this->lay->set("mforuseralert", false);
  $this->lay->set("foruname", $ownertitle);
  if ($eventid == -1) {
    $dcal = myDelegation();
    $tdusers = array();
    if (count($dcal)>0) {
      $this->lay->set("mforuser", true);
      $tdusers[] = array( "forufid"      => $action->user->fid, 
			  "foruname"     => ucwords(strtolower($action->user->lastname." ".$action->user->firstname)), 
			  "foruselected" => ($ownerid==$action->user->fid ? "selected" : "") );
      $ownerlist[$action->user->fid] = $action->user->fid;
      foreach ($dcal as $k => $v) {
	if ($v!="") {
	  $dcaluser = getTDoc($this->dbaccess, $v["agd_oid"]);
	  $tdusers[] = array( "forufid"      => $dcaluser["id"], 
			      "foruname"     => ucwords(strtolower($dcaluser["title"])),
			      "foruselected" => ($ownerid==$dcaluser["id"] ? "selected" : "") );
	  $ownerlist[$dcaluser["id"]] = $v["id"];
	}
      }
      $this->lay->setBlockData("foruser", $tdusers);
    }    
    if ($ownerid != $action->user->fid) $this->lay->set("mforuseralert", true);
  } else {
    if ($ownerid != $action->user->fid) {
      $this->lay->set("mforuseralert", true);
      $this->lay->set("mforusermod", true);
    }
  }
  $ownerlist[$ownerid] = $ownerid;

  // Display searchable ressource families
  $sfamr = $famr = array();
  $rf = wGetUsedFamilies();
  foreach ($rf as $k => $v) {
    if ($v["inMeeting"]) {
      $famr[] = array( "fid" => $v["id"], 
		       "ftitle" => addslashes(ucwords(strtolower($v["title"]))),
		       "ficon" => $v["icon"],
		       "fselect" => $v["isSelected"],
		       "finter" => $v["isInteractive"]);
      if ($v["isSelected"]) $sfamr[]["fid"] = $v["id"];
    }
  }
  $this->lay->setBlockData("FAMR", $famr);
  $this->lay->setBlockData("sFAMR", $sfamr);

  $this->lay->set("rvlocation", $evlocation);

  $this->EventSetDate($evstart, $evend, $evtype);
  $this->EventSetVisibility($ownerid, $ownerlist, $evvis, $ogrp);
  $this->EventSetCalendar($evcal);
  $this->EventSetAlarm();
  $this->EventSetRepeat($evrepeat, $evrweekd, $evrmonth, $evruntil, $evruntild, $evfreq, $evrexcld);
  $this->EventSetCategory($evcategory);
  $this->EventAddAttendees($ownerid, $attendees, $attendeesState, $attendeesGroup, $withme);

  return;  
}    

function EventSetCategory($evcategory) {
  global $action;
  include_once("WGCAL/Lib.wTools.php");
  $this->lay->set("evcategory", $evcategory);
  $catg = wGetCategories();
  $tcat = array(); $ntc = 0;
  foreach ($catg as $k => $v) {
    $tcat[$ntc]["value"] = $v["id"];
    $tcat[$ntc]["descr"] = ucwords(strtolower($v["label"]));
    $tcat[$ntc]["selected"] = ($v["id"] == $evcategory ? "selected" : "");
    $ntc++;
  }
  $this->lay->setBlockData("RVCATEGORY", $tcat);
}

function EventSetDate($dstart, $dend, $type) 
{
  global $action;
  $this->lay->set("evtimemode", $type);
  $this->lay->set("NOHOURINIT", ($type==1?"checked":""));
  $this->lay->set("NOHOURDISP", ($type==2?"hidden":"visible"));
  $this->lay->set("ALLDAYINIT", ($type==2?"checked":""));
  $this->lay->set("ALLDAYDISP", ($type==1?"hidden":"visible"));
  if ($type==1 || $type==2) $this->lay->set("HVISIBLE", "hidden");
  else $this->lay->set("HVISIBLE", "visible");
  
  
  $start_H = gmdate("H", $dstart);
  $start_M = gmdate("i", $dstart);
  $this->lay->set("START", ($dstart*1000));
  $this->lay->set("STARTsec", $dstart);
  $this->lay->set("STARTREAD", ucwords(strftime("%a %d %b %Y", $dstart)));
  $th = array();
  for ($h=0; $h<24; $h++) {
    $th[$h]["optvalue"] = $h;
    $th[$h]["optdescr"] = (strlen($h)==1?"0".$h:$h)."h";
    $th[$h]["optselect"] = ($h==$start_H?"selected":"");
  }
  $this->lay->setBlockData("SHSEL", $th);
  $th = array();
  $incm = $this->getWgcalUParam("WGCAL_U_MINCUSED",15);
  $mselect = false;
  for ($h=0; $h<60; $h+=$incm) {
    $th[$h]["optvalue"] = $h;
    if (!$mselect) { 
       $th[$h]["optselect"] = ($start_M>=$h && $start_M<($h+$incm) ? "selected" : "" );
       $mselect = $th[$h]["optselect"];
    } else $th[$h]["optselect"] = "";
    $th[$h]["optdescr"] = (strlen($h)==1?"0".$h:$h);
  }
  $this->lay->setBlockData("SHMSEL", $th);
  
  $end_H = gmdate("H", $dend);
  $end_M = gmdate("i", $dend);
  $this->lay->set("END", $dend*1000);
  $this->lay->set("ENDsec", $dend);
  $this->lay->set("ENDREAD", ucwords(strftime("%a %d %b %Y", $dend)));
  $th = array();
   for ($h=0; $h<24; $h++) {
    $th[$h]["optvalue"] = $h;
    $th[$h]["optdescr"] = (strlen($h)==1?"0".$h:$h)."h";
    $th[$h]["optselect"] = ($h==$end_H?"selected":"");
  }
  $this->lay->setBlockData("EHSEL", $th);
  $th = array();
  $mselect = false;
  for ($h=0; $h<60; $h+=$incm) {
    $th[$h]["optvalue"] = $h;
    $th[$h]["optvalue"] = $h;
        if (!$mselect) {
       $th[$h]["optselect"] = ($end_M>=$h && $end_M<($h+$incm) ? "selected" : "" );
       $mselect = $th[$h]["optselect"];
    } else $th[$h]["optselect"] = "";
    $th[$h]["optdescr"] = (strlen($h)==1?"0".$h:$h);
  }
  $this->lay->setBlockData("EHMSEL", $th);
 
}

function EventSetVisibility($ownerid, $ownerlist, $vis, $ogrp) {
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
      $allgroups[$iall]["gownerdispl"] = ($ownerid==$ko?"block":"none");
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
    $tconf[$ic]["descr"] = ucwords(strtolower($v));
    $tconf[$ic]["selected"] = ($vis==$k?"selected":"");
    $ic++;
  }
  $this->lay->SetBlockData("RVCONFID", $tconf);
  if ($vis==2) $this->lay->set("vis_groups", "visible");
  else $this->lay->set("vis_groups", "hidden");

}
  
function EventSetCalendar($cal) {
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
  $this->lay->set("fullattendees", ($cal==-1?"block":"none"));
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
			$runtildate, $freq, $recxlude = array())
{

  $this->lay->set("D_RUNTIL", ($rmode>0?"visible":"hidden"));
  $this->lay->set("REPEAT_SELECTED", "");
  
  $this->lay->set("evrepeattype", $rmode);
  for ($i=0; $i<=4; $i++) $this->lay->set("REPEATTYPE_".$i, ($rmode==$i?"checked":""));

  $tday = array( _("monday"), _("tuesday"),_("wenesday"),_("thursday"),_("friday"),_("saturday"), _("sunday"));
  for ($i=0; $i<=6; $i++) {
    $td[$i]["rdstate"] = (($rday & pow(2,$i)) == pow(2,$i) ? "checked" : "" );
    $td[$i]["dayn"] = $i;
    $td[$i]["tDay"] = $tday[$i];
    if ($i==4) $td[$i]["weekend"] = true;
    else $td[$i]["weekend"] = false;
  }
  $this->lay->SetBlockData("D_RWEEKDISPLAY", $td);
  $this->lay->set("RWEEKDISPLAY", ($rmode==2?"block":"none"));

  $this->lay->set("D_RMONTH", ($rmode==3 || $rmode==4?"block":"none"));
  $this->lay->set("D_RMONTH_DATE_CHECKED", ($rmonthdate==0?"checked":""));
  $this->lay->set("D_RMONTH_DAY_CHECKED", ($rmonthdate==1?"checked":""));
  
  $this->lay->set("D_RUNTIL_INFI", ($runtil==0?"checked":""));
  $this->lay->set("D_RUNTIL_DATE", ($runtil==1?"checked":""));
  $this->lay->set("RUNUNTIL_DATE_DISPLAY", ($rmode>0 && $runtil==1?"visible":"hidden"));
  
  $this->lay->set("uDate", ucwords(strftime("%a %d %b %Y", $runtildate))); //w_strftime($runtildate, WD_FMT_DAYLTEXT));
  $this->lay->set("umDate", $runtildate*1000);
  $this->lay->set("usDate", $runtildate);
  

  // Frequency
  $tif = array();
  for ($if=1; $if<=12; $if++) $tif[] = array( "iFreq" => $if, "IFreqSel" => ($freq==$if?"selected":""));
  $this->lay->setBlockData("BFREQ", $tif);
  

  // Excluded dates
  $this->lay->set("displayExcludedDays", false);
  if (is_array($recxlude) && count($recxlude)>0) {
    $ide = 0;
    foreach ($recxlude as $kd => $vd) {
      if ($vd!="" && $vd>0) {
        $this->lay->set("displayExcludedDays", true);
        $ld = dbdate2ts($vd);
        $rx[$ide]["rDate"] = w_strftime($ld, WD_FMT_DAYFTEXT);
        $rx[$ide]["mDate"] = $ld;
        $rx[$ide]["iDate"] = $i;
	$ide++;
      }
    }
    if ($ide>0) $this->lay->setBlockData("EXCLDATE", $rx);
  }
  
}


function EventAddAttendees($ownerid, $attendees = array(), $attendeesState = array(), $attendeesGroup = array(), $withme=true, $ro=false) {
  global  $action;
  $udbaccess = $action->GetParam("COREUSER_DB");
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $sendmext = ($action->getParam("WGCAL_G_SENDMAILS_EXTERN", 0) == 1 ? true : false);
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
    $this->lay->set("diparticipe", "none");
    $this->lay->set("dconvocation", "none");
  } else {
    $this->lay->set("voneatt", "");
    $this->lay->set("vnatt", "");
    $this->lay->set("diparticipe", "");
    $this->lay->set("dconvocation", "");
  }

  $this->lay->set("vnatt", "none");
  if ($ownerid==$action->user->fid) {
    if (!$onlyme) $this->lay->set("vnatt", "");
  }
  $this->lay->setBlockData("ADD_RESS", $att);

  $iuserfam = getFamIdFromName($dbaccess, "IUSER");
    
  $dress = $this->getWgcalUParam("WGCAL_U_RESSDISPLAYED", "");
  $tdress = explode("|", $dress);
  $to = array(); $ito = 0;
  $ts = array(); $its = 0;
  foreach ($tdress as $k => $v) {
    if ($v=="") continue;
    $tx = explode("%", $v);
    if ($tx[0]=="" || $tx[0]==$ownerid) continue;
    $ress = getTDoc($this->dbaccess, $tx[0]);
    if ($ress["fromid"] == $iuserfam) {
      $cal = getUserPublicAgenda($ress["id"], false);
      $view = true;
      if (!$cal || ($cal->Control("invite") != "")) $view = false;
    } else {
      $view = true;
    }
    if ($view) {
      $to[$ito]["idress"] = $ito;
      $to[$ito]["resstitle"] = addslashes(ucwords(strtolower($ress["title"])));
      $to[$ito]["ressid"] = $ress["id"];
      $to[$ito]["ressico"] = Doc::GetIcon($ress["icon"]);
      $to[$ito]["ressst"] = ( $ress["fromid"]==$iuserfam?0:-1);
      if ($tx[1] == 1) {
	$ts[$its] = $to[$ito];
	$ts[$its]["idress"] = $its;
	$its++;
      }
      $ito++;
    }
  }
  if (count($to)>0) {
    wUSort($to, "resstitle");
    $this->lay->setBlockData("DRESS", $to);
    $this->lay->setBlockData("DLIST", $to);
    $this->lay->set("hasDisplayed", true);
  } else {
    $this->lay->setBlockData("DLIST", $to);
    $this->lay->setBlockData("DLIST", null);
    $this->lay->set("hasDisplayed", false);
  }
  if (count($ts)>0) {
    wUSort($ts, "resstitle");
    $this->lay->setBlockData("SRESS", $ts);
    $this->lay->setBlockData("SLIST", $ts);
    $this->lay->set("hasSelected", true);
  } else {
    $this->lay->setBlockData("SRESS", null);
    $this->lay->setBlockData("SLIST", null);
    $this->lay->set("hasSelected", false);
  }

    
  $dress = $this->getWgcalUParam("WGCAL_U_PREFRESSOURCES", "");
  $tdress = explode("|", $dress);
  $to = array(); $ito = 0;
  foreach ($tdress as $k => $v) {
    if ($v=="" || $v==$ownerid) continue;
    $ress = getTDoc($this->dbaccess, $v);
    if ($ress["fromid"] == $iuserfam) {
      $cal = getUserPublicAgenda($ress["id"], false);
      $view = true;
      if (!$cal || ($cal->Control("invite") != "")) $view = false;
    } else {
      $view = true;
    }
    if ($view) {
      $to[$ito]["idress"] = $ito;
      $to[$ito]["resstitle"] = addslashes(ucwords(strtolower($ress["title"])));
      $to[$ito]["ressid"] = $ress["id"];
      $to[$ito]["ressico"] = Doc::GetIcon($ress["icon"]);
      $to[$ito]["ressst"] = ( $ress["fromid"]==$iuserfam?0:-1);
      $ito++;
    }
  }
  if (count($to)>0) {
    wUSort($to, "resstitle");
    $this->lay->setBlockData("PRESS", $to);
    $this->lay->setBlockData("PLIST", $to);
    $this->lay->set("hasPrefered", true);
  } else {
    $this->lay->setBlockData("PRESS", null);
    $this->lay->setBlockData("PLIST", null);
    $this->lay->set("hasPrefered", false);
  }
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
  if ( isset($ressd[$myid]) && $ressd[$myid]["displayed"] 
      && (($ressd[$myid]["state"]==EVST_REJECT && $showrefused==1) || $ressd[$myid]["state"]!=EVST_REJECT )) {
    $event_color = $ressd[$myid]["color"];
  } else {
    if (isset($ressd[$ownerid]) && $ressd[$ownerid]["displayed"]) {
      $event_color = $ressd[$ownerid]["color"];
    } else {
      foreach ($ressd as $k => $v) {
	if ($v["displayed"]) {
	  $event_color = $v["color"];
	  break;
	}
      }
    }
  }
  $event_color = ($event_color!=""?$event_color:"#d2f5f7");
  return $event_color;
}

function RvIsMeeting() {
  global $action;
  $atts  = $this->getTValue("calev_attid");
  $owner = $this->getValue("calev_ownerid");
  $redac = $this->getValue("calev_creatorid");

  foreach ($atts as $k => $v) {
    if ($owner==$v) continue;
    $dt = getTDoc(getParam("FREEDOM_DB"), $v);
    if (wIsFamilieInteractive($dt["fromid"])) return true;
  }
  return false;
}

function RvAttendeeState($ufid) {
  $state = -1;  
  $attr = $this->getTValue("calev_attid");
  $attrst = $this->getTValue("calev_attstate");
  $ownerfid = $this->getValue("calev_ownerid");
  if (count($attr)==1 && $ownerfid==$attr[0]) return $state;
  if (count($attr)>0) {
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



function ResetAttendeesStatus() {
  include_once("WGCAL/Lib.WGCal.php");
  $att_ids = $this->getTValue("calev_attid");
  $convoc = $this->getValue("calev_convocation");
  if (count($att_ids)>0) {
    $att_wid = $this->getTValue("calev_attwid");
    $att_sta = $this->getTValue("calev_attstate");
    $att_grp = $this->getTValue("calev_attgroup");
    foreach ($att_ids as $k => $v) {
      if ($att_grp[$k]==-1 && ($att_sta[$k]!= -1 || ($att_sta[$k]==-1&&$convoc==0))) {
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
  include_once('WGCAL/Lib.Agenda.php');

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
  
  $aclvals = array();
  foreach ($aclv as $ka => $va) {
    foreach ($aclv["all"] as $kr => $vr) {
      if (isset($va[$kr])) $aclvals[$ka][$kr] = $this->dacls[$kr]["pos"];
      else $aclvals[$ka][$kr] = 0;
    }
  }


  $acls = array();

  switch ($conf) {

  case 3: // Private
    $acls[2] = $aclvals["read"];
    break;

  case 1: // Confidential
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
    if ($v!=$ownerwid && $v!=$creatorwid) $acls[$v] = $aclvals["read_conf_state"];
  }

  // Owner, creator and delegate ==> owner rights
  $acls[$ownerwid] = $aclvals["all"];
  if ($conf!=3) {
    if ($creatorid!=$ownerid) $acls[$creatorwid] = $aclvals["all"];
    $ownercal = getUserPublicAgenda($ownerid, false);
    $duid = $ownercal->getTValue("agd_dfid");
    if (count($duid)>0) {
      $duwid = $ownercal->getTValue("agd_dwid");
      $dumode = $ownercal->getTValue("agd_dmode");
      foreach ($duid as $k=>$v) {
	if ($dumode[$k] == 1) $acls[$duwid[$k]] = $aclvals["all"];
      }
    }
  }

  $this->RemoveControl();
  foreach ($acls as $user => $uacl) {
    if ($user!="") {
      $dt = getDocFromUserId($this->dbaccess,$user);
      if (!$dt) continue;
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


function sifevent() {

  global $action;

  include_once('WGCAL/Lib.WGCal.php');
  include_once("WGCAL/Lib.wTools.php");

  $eltList = array( "Subject",
		    "Body",
		    "Start",
		    "End",
		    "AllDayEvent",
		    "NoEndDate",
		    "Duration",
		    "Sensitivity",
		    "Importance",
		    "Categories",
		    "Companies",
		    "Location",
		    "Status",
		    "MeetingStatus",
		    "Mileage",
		    "ReminderMinutesBeforeStart",
		    "ReminderSet",
		    "ReminderSoundFile",
		    "ReminderOptions",
		    "ReplyTime",
		    "IsRecurring",
		    "RecurrenceType",
		    "Interval",
		    "MonthOfYear",
		    "DayOfMonth",
		    "DayOfWeekMask",
		    "Instance",
		    "PatternStartDate",
		    "PatternEndDate",
		    "BusyStatus" );

  // Initialize all fields to empty
  foreach ($eltList as $k => $v) {
    $this->lay->set("s_$v", false);
  }

  $this->lay->set("v_Subject", utf8_encode($this->getValue("calev_evtitle")));
  $this->lay->set("s_Subject", true);

  $this->lay->set("v_Body", utf8_encode($this->getValue("calev_evnote")));
  $this->lay->set("s_Body", true);


  $this->lay->set("v_Start", $this->_WsetDateTime($this->getValue("calev_start")));
  $this->lay->set("s_Start", true);

  $this->lay->set("v_End", $this->_WsetDateTime($this->getValue("calev_end")));
  $this->lay->set("s_End", true);

  $allday = 0;
  $dur = (dbdate2ts($this->getValue("calev_end")) - dbdate2ts($this->getValue("calev_start"))) / 60;
  if ($this->getValue("calev_timetype")==1 || $this->getValue("calev_timetype")==2) {
    $dur = 1440;
    $allday = 1;
  }
  $this->lay->set("v_Duration", $dur);
  $this->lay->set("s_Duration", true);

  $this->lay->set("v_AllDayEvent", $allday);
  $this->lay->set("s_AllDayEvent", true);
  
  $this->lay->set("v_BusyStatus", 2);  $this->lay->set("s_BusyStatus", true);

  if ($this->getValue("calev_category")>0) {
    $catg = wGetCategories();
    $this->lay->set("v_Categories", utf8_encode($catg[$this->getValue("calev_category")]["label"]));
    $this->lay->set("s_Categories", true);
  }

  // Sensitivity
  $sen = 0;
  switch ($this->getValue("calev_visibility")) {
  case 1 : // Private --> olConfidential
    $sen = 3;
    break;
  case 2 : // My groups --> Private
    $sen = 2;
    break;
  }
  $this->lay->set("v_Sensitivity", $sen);
  $this->lay->set("s_Sensitivity", true);
  

  $this->lay->set("v_Importance", 1);
  $this->lay->set("s_Importance", true);

  // Meeting or not meeting
  $attFid = $this->getTValue("calev_attid");
  $attSta = $this->getTValue("calev_attstate");
  $meeting = 0;
  if (count($attFid)>1 || $attFid[0]!=$action->user->fid) {
    $r = -1;
    foreach ($attFid as $k => $v) if ($v==$action->user->fid) $r=$k;
    if ($r>=0) {
      switch ($attStat[$r]) {
      case 0:
      case 1:
      case 4:
	$meeting = 3;
	break;
      case 2:
	$meeting = 1;
	break;
      case 3:
	$meeting = 5;
	break;
      }
    }
  }
  $this->lay->set("v_MeetingStatus", $meeting);
  $this->lay->set("s_MeetingStatus", true);
 

  // Recurring
  $this->lay->set("v_Instance", 1);
  $this->lay->set("s_Instance", true);
  $this->lay->set("v_Interval", 1);
  $this->lay->set("s_Interval", true);
  $isRecurring = 0;
  $rmode = $this->getValue("calev_repeatmode");
  if ($rmode>0 && $rmode<5) {
    $isRecurring = 1;
    $this->lay->set("v_Interval", 1);
    $this->lay->set("s_Interval", true);

    switch ($rmode) {
    
    case 1: // Dayly
      $rType = 0;
      break;
    
    case 2: // Weekly 
      $olPow = array( 0=>2, 1=>4,  2=>6, 3=>16, 4=>32, 5=>64, 6=>1 );
      $rType = 1;
      $dm = 0;
      $rday = $this->getValue("calev_repeatweekday", 0);
      for ($i=0; $i<6; $i++) $dm += (($rday & pow(2,$i)) == pow(2,$i) ? $olPow[$i] : 0 );
      $this->lay->set("v_DayOfWeekMask", $dm);
      $this->lay->set("s_DayOfWeekMask", true);
      break;
    
    case 3: 
      if ($rmonth!=1) {  // Monthly by date
	$rType = 2;
	$this->lay->set("v_DayOfTheMonth", substr($this->getValue("calev_start"), 0, 2));
	$this->lay->set("s_DayOfTheMonth", true);
      } else {            // Monthly by day (Nth day of the month)
	$rType = 3;
	$sd = $this->getValue("calev_start");
	$date = substr($sd,0,2);
	$rday = 0;
	$cancel = false;
	while (!$cancel) {
	  if ($date-($rday*7)>0) $rday++;
	  else $cancel = true;
	}
	$this->lay->set("v_Instance", $rday);
	$this->lay->set("s_Instance", true);
	$d = strftime("%u", gmmktime( substr($sd,11,2),
				      substr($sd,14,2),
				      substr($sd,17,2),
				      substr($sd,3,2),
				      substr($sd,0,2),
				      substr($sd,6,4) ));
	$this->lay->set("v_DayOfWeekMask", $olPow[($d-1)]);
	$this->lay->set("s_DayOfWeekMask", true);
      }
      break;
    case 4: 
      if ($rmonth!=1) {  // Yearly by date
	$rType = 5;
	$this->lay->set("v_MonthOfYear", substr($this->getValue("calev_start"), 3, 2));
	$this->lay->set("s_MonthOfYear", true);
	$this->lay->set("v_DayOfMonth", substr($this->getValue("calev_start"), 0, 2));
	$this->lay->set("s_DayOfMonth", true);
      } else {            // Yearly by day (Nth day of the year)
	$rType = 6;
	$sd = $this->getValue("calev_start");
	$d = strftime("%u", gmmktime( substr($sd,11,2),
				      substr($sd,14,2),
				      substr($sd,17,2),
				      substr($sd,3,2),
				      substr($sd,0,2),
				      substr($sd,6,4) ));
	$this->lay->set("v_DayOfWeekMask", $olPow[($d-1)]);
	$this->lay->set("s_DayOfWeekMask", true);
	$this->lay->set("v_MonthOfYear", substr($sd, 3, 2));
	$this->lay->set("s_MonthOfYear", true);
	$this->lay->set("v_Instance", substr($this->getValue("calev_start"), 3, 2));
	$this->lay->set("s_Instance", true);
      }
      break;
    }
    $this->lay->set("v_RecurrenceType", $rType);
    $this->lay->set("s_RecurrenceType", true);
    if ($this->getValue("calev_repeatuntil")==0) $this->lay->set("v_NoEndDate", 0);
    else $this->lay->set("v_NoEndDate", 1);
    $this->lay->set("s_NoEndDate", true);
    
    $this->lay->set("v_PatternStartDate", $this->_WsetDate($this->getValue("calev_start")));
    $this->lay->set("s_PatternStartDate", true);
    if ($this->getValue("calev_repeatuntil")!=0) {
      $this->lay->set("v_PatternEndDate", $this->_WsetDate($this->getValue("calev_repeatuntildate")));
      $this->lay->set("s_PatternEndDate", true);
    }
  }
  $this->lay->set("v_IsRecurring", $isRecurring);
  $this->lay->set("s_IsRecurring", true);
}

function setSync4jGuid($force=false) {
  if ($force || (!$force && $this->getValue("calev_s4j_guid")=="")) { 
     $this->setValue("calev_s4j_guid", "FREEDOM-EVENT-".str_pad($this->id, 20, "0", STR_PAD_LEFT));
  }
}    

function forceSync4jGuid() {
  $this->setSync4jGuid(true);
}



function agendaMenu($ctx="CAL", $ue=false, $occurrence="") {
  include_once('WGCAL/Lib.wTools.php');
  include_once('WGCAL/Lib.Agenda.php');
  global $action;

  if ($ue)$caledit = $action->GetParam("WGCAL_U_DCALEDIT", $action->user->fid);
  else $caledit = $action->user->fid;

  if ($caledit == $action->user->fid) $dt = "";
  else { 
    $d = getTDoc($this->dbaccess, $caledit);
    $dt = "(".ucwords(strtolower($d["title"])).")";
  }
   
  $surl = $action->getParam("CORE_STANDURL");
  $sico = $action->getParam("WGCAL_U_ICONPOPUP", true);
  
  $menu["sub"] = array();
  $menu["main"] =     array(	  
			    'acceptrv' => array("descr" => _("accept this")." $dt",
						"jsfunction" => ($ctx=="WRV"? "fcalToolbarSetEvState" : "fcalSetEventState")."(event,".$this->id.", 2)",
						"confirm" => "false",
						"tconfirm" => "",
						"control" => "false",
						"target" => "wgcal_calendar",
						"visibility" => POPUP_INVISIBLE,
						"icon" => ($sico?$action->getImageUrl("wm-evaccept.gif"):""),
						"submenu" =>  "",
						"barmenu" => "false"
						), 
			    'rejectrv' => array("descr" => _("reject this")." $dt",
						"jsfunction" => ($ctx=="WRV"? "fcalToolbarSetEvState" : "fcalSetEventState")."(event,".$this->id.", 3)",
						"confirm" => "false",
						"tconfirm" => "",
						"control" => "false",
						"target" => "wgcal_calendar",
						"visibility" => POPUP_INVISIBLE,
						"icon" => ($sico?$action->getImageUrl("wm-evrefuse.gif"):""),
						"submenu" =>  "",
						"barmenu" => "false"
						), 	  
			    'confirmrv' => array("descr" => _("to be confirm this")." $dt",
						"jsfunction" => ($ctx=="WRV"? "fcalToolbarSetEvState" : "fcalSetEventState")."(event,".$this->id.", 4)",
						 "confirm" => "false",
						 "tconfirm" => "",
						 "control" => "false",
						 "target" => "wgcal_calendar",
						 "visibility" => POPUP_INVISIBLE,
						 "icon" => ($sico?$action->getImageUrl("wm-evconfirm.gif"):""),
						 "submenu" =>  "",
						 "barmenu" => "false"
						 ), 
			    'sepstate' => array("separator" => true,
						"visibility" => POPUP_INVISIBLE,
						),
			    'editrv' => array("descr" => _("edit this"),
					      "url" => $surl."&app=GENERIC&action=GENERIC_EDIT&id=".$this->id,
					      "confirm" => "false",
					      "tconfirm" => "",
					      "control" => "false",
					      "target" => "wgcal_edit",
					      "visibility" => POPUP_INVISIBLE,
					      "icon" => ($sico?$action->getImageUrl("wm-evedit.gif"):""),
					      "submenu" =>  "",
					      "barmenu" => "false"
					      ), 
			    'deloccur' => array("descr" => _("delete this occurence")." [$occurrence]",
						"jsfunction" => "fcalDeleteEventOcc(event, ".$this->id.",'".$occurrence."')",
						"confirm" => "true",
						"tconfirm" => _("confirm delete for this occurrence"),
						"control" => "false",
						"target" => "wgcal_calendar",
						"visibility" => POPUP_INVISIBLE,
						"icon" => ($sico?$action->getImageUrl("wm-deloccur.gif"):""),
						"submenu" =>  "",
						"barmenu" => "false"
						), 
			    'deleterv' => array("descr" => _("delete this"),
						"jsfunction" => "fcalDeleteEvent(event, ".$this->id.")",
						"confirm" => "true",
						"tconfirm" => _("confirm delete for this event"),
						"control" => "false",
						"target" => "wgcal_calendar",
						"visibility" => POPUP_INVISIBLE,
						"icon" => ($sico?$action->getImageUrl("wm-evdelete.gif"):""),
						"submenu" =>  "",
						"barmenu" => "false"
						), 
			    'sephisto' => array("separator" => true,
						"visibility" => POPUP_INVISIBLE,
						),
			    'viewrv' => array("descr" => _("view this"),
					      "url" => $surl."&app=FDL&action=IMPCARD&id&id=".$this->id,
					      "jsfunction" => "",
					      "confirm" => "false",
					      "tconfirm" => "",
					      "control" => "false",
					      "target" => "wgcal_view",
					      "visibility" => POPUP_INVISIBLE,
					      "icon" => ($sico?$action->getImageUrl("wm-evview.gif"):""),
					      "submenu" =>  "",
					      "barmenu" => "false"
					      ), 
			    'gotodate' => array("descr" => _("go to date")." ".substr($this->getValue("calev_start"),0,11),
						"url" => "",
						"jsfunction" => "loadPeriod('$surl', ".w_dbdate2ts($this->getValue("calev_start")).")",
						"confirm" => "false",
						"tconfirm" => "",
						"control" => "false",
						"target" => "wgcal_calendar",
						"visibility" => POPUP_INVISIBLE,
						"icon" => ($sico?$action->getImageUrl("wm-evdate.gif"):""),
						"submenu" =>  "",
						"barmenu" => "false"
						), 
			    'historyrv' => array("descr" => _("history"),
						 "url" => $surl."&app=WGCAL&action=WGCAL_HISTO&id=".$this->id,
						 "confirm" => "false",
						 "tconfirm" => "",
						 "control" => "false",
						 "target" => "wgcal_history",
						 "visibility" => POPUP_INVISIBLE,
						 "icon" => ($sico?$action->getImageUrl("wm-evhistory.gif"):""),
						 "submenu" =>  "",
						 "barmenu" => "false"
						 ), 
			    'sepaccess' => array("separator" => true,
						 "visibility" => POPUP_INVISIBLE ),
			    'access' => array("descr" => _("view accessibilities"),
					      "url" => $surl."&app=FREEDOM&action=FREEDOM_ACCESS&id=".$this->id,
					      "confirm" => "false",
					      "tconfirm" => "",
					      "control" => "false",
					      "target" => "wgcal_showaccess",
					      "visibility" => POPUP_INVISIBLE,
					      "icon" => ($sico?$action->getImageUrl("wm-privgroup.gif"):""),
					      "submenu" =>  "",
					      "barmenu" => "false"
					      ), 
			    );
  $rvst = $this->RvAttendeeState($caledit);
  if ($rvst!=-1 && $this->RvIsMeeting() && $this->UHaveAccess("execute")) {
    if ($rvst!=EVST_ACCEPT) $menu["main"]["acceptrv"]["visibility"] = POPUP_ACTIVE;
    if ($rvst!=EVST_TBC) $menu["main"]["confirmrv"]["visibility"] = POPUP_ACTIVE;
    if ($rvst!=EVST_REJECT) $menu["main"]["rejectrv"]["visibility"] = POPUP_ACTIVE;
    $menu["main"]["sepstate"]["visibility"] = POPUP_ACTIVE;
  }

  if ($this->UHaveAccess("confidential") || ($this->confidential==0 && $this->UHaveAccess("view")) ) {
    $menu["main"]["historyrv"]["visibility"] = POPUP_ACTIVE;
    $menu["main"]["gotodate"]["visibility"] = POPUP_ACTIVE;
    $menu["main"]["viewrv"]["visibility"] = POPUP_ACTIVE;
    $menu["main"]["sephisto"]["visibility"] = POPUP_ACTIVE;  
  }
  
  if ($ue) {
    if ($this->UHaveAccess("edit")) $menu["main"]["editrv"]["visibility"] = POPUP_ACTIVE;
    if ($this->UHaveAccess("delete")) {
      $menu["main"]["deleterv"]["visibility"] = POPUP_ACTIVE;
      if ($this->getValue("calev_repeatmode") > 0 && $occurrence!="") $menu["main"]["deloccur"]["visibility"] = POPUP_ACTIVE;
    }
  }

  if (wDebugMode())   if ($this->UHaveAccess('viewacl')) {
    $menu["main"]["access"]["visibility"] = POPUP_ACTIVE;
    $menu["main"]["sepaccess"]["visibility"] = POPUP_ACTIVE;
  }

  return $menu;
}


function addJsValues() {
  include_once("WGCAL/Lib.WGCal.php");
  $t = array( "tsstart" => w_dbdate2ts($this->getValue("calev_start")),
	      "tsend" => w_dbdate2ts($this->getValue("calev_end")),
	     );
  return $t;
}

function postChangeProcess($old=false) {

  global $action;

  $change = array();
  if (is_array($old)) {
    $newevent = false;
    $new = $this->getValues();
    $change = $this->rvDiff($old, $new);
  } else {
    $newevent = true;
  }
  // 1) Creation => envoi d'un mail à tout les participants (sauf proprio)
  // 2) Modification de l'heure, répétition => envoi d'un mail à tout les participants et reset des acceptations
  // 3) Modification de l'acceptation => envoi d'un mail au proprio D'ICI CA VA ETRE DUR...
  // Modification du contenu => rien
  // Modification de la liste des participants => rien
  
  $mail_msg = $comment = "";
  $mail_who = -1;

  if ($newevent) {
    $mail_msg = _("event creation information message");
    $mail_who = 2;
    $comment = _("event creation");
  } else {
    if ($change["hours"]) {
      $mail_msg = _("event time modification message");
      $mail_who = 2;
      $comment = _("event modification time");
      $this->ResetAttendeesStatus();
    } else {
      if ($change["attendees"]) {
	$mail_msg = $comment = _("event modification attendees list");
	$mail_who = 2;
      } else {
	if ($change["status"]) {
	  $mail_msg = _("event acceptation status message");
	  $mail_who = 0;
	  $comment = _("event modification acceptation status");
	}
      }
    }
  }

  if ($comment!="") $this->AddComment($comment);
  if ($mail_who!=-1) {
    $title = $action->getParam("WGCAL_G_MARKFORMAIL", "[RDV]")." ".$this->getValue("calev_evtitle");
    sendRv($action, $this, $mail_who, $title, $mail_msg, true);
  }
  $creatortitle = $this->getValue("calev_creator");
  $owner = $this->getValue("calev_ownerid");
  if ($action->user->fid!=$owner && $mail_who!=-1) {
    // Get Agenda delegation information : does the owner want to received mail ?
    $owneragenda = getUserAgenda($owner, true, "", false);
    if ($owneragenda[0]->isAffected() && $owneragenda[0]->getValue("agd_dmail")==1) {
      $title = $action->getParam("WGCAL_G_MARKFORMAIL", "[RDV]")."(par $creatortitle) ".$this->getValue("calev_evtitle");
      sendRv($action, $this, 0, $title, "<i>"._("event set/change by")." ".$creatortitle."</i><br><br>".$mail_msg);
    }
  }

}

function rvDiff( $old, $new) {
  $diff = array();
  foreach ($old as $ko => $vo) {
    if (!isset($new[$ko])) {
      $diff[$ko] = "D";
    } else {
      if (strcmp($ko,"calev_start")==0 || strcmp($ko,"calev_end")==0) 
	{
	  if (strcmp(substr($vo, 0, 16), substr($new[$ko], 0, 16))!=0) $diff[$ko] = "M";
	}	
      else if ($vo!=$new[$ko]) $diff[$ko] = "M";
    }
  }
  foreach ($new as $ko => $vo) {
    if (!isset($new[$ko])) $diff[$ko] = "A";
  }

  $result = array( "content" => false, 
		   "hours" => false, 
		   "attendees" => false, 
		   "status" => false, 
		   "others" => false);


   foreach ($diff as $k => $v) {

    switch ($k) {
    case "calev_evtitle":      
    case "calev_evnote":
      $result["content"] = true;
      break;
    case "calev_start":
    case "calev_end":
    case "calev_timetype":
    case "calev_frequency":
    case "calev_repeatmode":
    case "calev_repeatweekday":
    case "calev_repeatmonth":
//     case "calev_repeatuntil":
//     case "calev_repeatuntildate":
    case "calev_excludedate":
      $result["hours"] = true;
      break;
    case "calev_attid":
      $result["attendees"] = true;
      break;
    case "calev_attstate":
      $result["status"] = true;
      break;
    default:
      $result["others"] = true;
    }
  }
//     print_r2($diff);
//     print_r2($result);
  return $result;
}
  
function computeCMTime() {
  $ctime = $this->getValue("calev_s4j_ctime");
  $mtime = $this->getValue("calev_s4j_mtime");
  $revdate = strftime("%d/%m/%Y %H:%M:%S", $this->revdate);
  if ($ctime=="" || $mtime=="") {
    $ctime = $this->cdate;
    $mtime = $revdate;
    $this->setValue("calev_s4j_ctime", $ctime);
    $this->setValue("calev_s4j_mtime", $mtime);
    echo "           * calev_s4j_ctime=[$ctime] calev_s4j_mtime=[$mtime]";
  } else {
    echo "           * dates already set";
  }
  echo "\n";
  return;
}

function migr_2_1() {
  $this->computeCMTime();
  $this->forceSync4jGuid();
  $this->setEvent();
}