
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: Method.RendezVousEvent.php,v 1.17 2006/03/01 10:05:55 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage
 */

var $calVResume     = "WGCAL:CALEV_ABSTRACT";
var $calVCard       = "WGCAL:CALEV_CARD";
var $calVLongText   = "WGCAL:CALEV_VIEWLTEXT";
var $calVShortText  = "WGCAL:CALEV_VIEWSTEXT";


function explodeEvt($d1, $d2) {
  include_once("FDL/Lib.Util.php");  
  include_once("WGCAL/Lib.wTools.php");
    
  $eve = array();

  // return event if there are not repeatable to produce 
  $ref = get_object_vars($this);
  if ($this->getValue("evfc_repeatmode")==0) {
    $eve[] = $ref;
    return $eve;
  }

  $jd1 = ($d1==""?0:Iso8601ToJD($d1));
  $jd2 = ($d2==""?5000000:Iso8601ToJD($d2));

  $jdDateStart = StringDateToJD($this->getValue("evt_begdate"));
  $jdDateEnd   = StringDateToJD($this->getValue("evfc_realenddate"));
  $jdDuration = $jdDateEnd - $jdDateStart;
  $jdREndDate  = StringDateToJD($this->getValue("evt_enddate"));

  if ($this->getValue("evfc_repeatmode")==0 || $jdREndDate<$jd1 || $jdDateStart>$jd2 ) {
    return array();
  }

  $freq      = $this->getValue("evfc_repeatfreq");

  $start = ($jdDateStart>$jd1 ? $jdDateStart : $jd1);
  $stop = ($jdREndDate<$jd2 ?  $jdREndDate : $jd2);
  $hstart = substr($this->getValue("evt_begdate"), 11, 5);

  $ix = 0;
  switch ($this->getValue("evfc_repeatmode")) {
    
  case 1: // daily repeat
    $if=1;
    for ($iday=$start; $iday<=$stop; $iday++) {
      if ($this->CalEvIsExclude(jd2cal($iday, 'FrenchLong'))) continue;
      $hs = substr(jd2cal($iday, 'FrenchLong'),0,10)." ".$hstart;
      $jdhs = StringDateToJD($hs);
      $jdhe = $jdhs+$jdDuration;
      $he = jd2cal($jdhe, 'FrenchLong');
      if (($jdhs<=$jd1 && ($jdhe>=$jd2||$jdhe==$jdhs)) || ($jdhs>$jd1 && $jdhs<$jd2) || ($jdhe>$jd1 && $jdhe<$jd2)) {
	$eve[$ix++] = $this->CalEvDupEvent($ref, $hs, $he);
      }
    }
    break;

  case 2: // weekly repeat
    $mdays = $this->getValue("evfc_repeatweekday");
    for ($i=0; $i<=6; $i++) $days[$i] = (($mdays & pow(2,$i)) == pow(2,$i) ? true : false );
    for ($iday=$start; $iday<=$stop; $iday++) {
      if ($this->CalEvIsExclude(jd2cal($iday, 'FrenchLong'))) continue;
      $id = (jdWeekDay($iday) - 1);
      if ($days[$id]) {
        $hs = substr(jd2cal($iday, 'FrenchLong'),0,10)." ".$hstart;
        $jdhs = StringDateToJD($hs);
        $jdhe = $jdhs+$jdDuration;
        $he = jd2cal(($jdhe), 'FrenchLong');
	if (($jdhs<=$jd1 && ($jdhe>=$jd2||$jdhe==$jdhs)) || ($jdhs>$jd1 && $jdhs<$jd2) || ($jdhe>$jd1 && $jdhe<$jd2)) {
	  $eve[$ix++] = $this->CalEvDupEvent($ref, $hs, $he);
        }
      }
    }
    break;
    

  case 3: // monthly repeat submode 0=by date 1=by day
    if ($this->getValue("evfc_repeatmonth")!=1) {
      $ds = $this->getValue("evt_begdate");
      $rdaynum = substr($ds, 0, 2);
      for ($iday=$start; $iday<=$stop; $iday++) {
	$dayn = substr(jd2cal($iday, 'FrenchLong'), 0, 2);
	if ($dayn != $rdaynum || $this->CalEvIsExclude(jd2cal($iday, 'FrenchLong'))) continue;
	$hs = substr(jd2cal($iday, 'FrenchLong'),0,10)." ".$hstart;
	$jdhs = StringDateToJD($hs);
        $jdhe = $jdhs+$jdDuration;
	$he = jd2cal($jdhe, 'FrenchLong');
	if (($jdhs<=$jd1 && ($jdhe>=$jd2||$jdhe==$jdhs)) || ($jdhs>$jd1 && $jdhs<$jd2) || ($jdhe>$jd1 && $jdhe<$jd2)) {
          $eve[$ix++] = $this->CalEvDupEvent($ref, $hs, $he);
        }
      }
    } else {
      $odate = jd2cal($jdDateStart, 'FrenchLong');
      $tsodate = FrenchDateToUnixTs($odate);
      $dayn = jdWeekDay($jdDateStart); // Monday, Tuesday....
      $date = substr($odate, 0, 2);
      $tdate = strftime("%A", $tsodate);
      $rday = 0;
      $cancel = false;
      while (!$cancel) {
	if ($date-($rday*7)>0) $rday++;
	else $cancel = true;
      }
      for ($iday=$start; $iday<=$stop; $iday++) {
	$fdate = jd2cal($iday, 'FrenchLong');
	$cdate = substr($fdate, 0, 2);
	if (jdWeekDay($iday)==$dayn && !$this->CalEvIsExclude($fdate)) {
	  $sd = $cdate-(($rday-1)*7);
	  if ($sd> 0 && $sd<7) {
	    $hs = substr($fdate,0,10)." ".$hstart;
	    $jdhs = StringDateToJD($hs);
	    $jdhe = $jdhs+$jdDuration;
	    $he = jd2cal($jdhe, 'FrenchLong');
	    if (($jdhs<=$jd1 && ($jdhe>=$jd2||$jdhe==$jdhs)) || ($jdhs>$jd1 && $jdhs<$jd2) || ($jdhe>$jd1 && $jdhe<$jd2)) {
	      $eve[$ix++] = $this->CalEvDupEvent($ref, $hs, $he);
	    }
	  }
	}
      }
    }
    break;

  case 4: // yearly repeat
    if ($this->getValue("evfc_repeatmonth")!=1) {
      $ds = $this->getValue("evt_begdate");
      $cyear = substr(jd2cal($start, 'FrenchLong'),6,4);
      $rday = substr($ds,0,6) . $cyear . substr($ds,10,6);
      $jdrday = StringDateToJD($rday);
      if ($jdrday>=$start && $jdrday<=$stop) {
	$hs = substr($rday,0,10)." ".$hstart;
	$jdhs = StringDateToJD($hs);
	$he = jd2cal($jdhs + $jdDuration, 'FrenchLong');
	$eve[$ix] = $this->CalEvDupEvent($ref, $hs, $he);
	$ix++;
      }
      $cnyear = substr(jd2cal($stop, 'FrenchLong'),6,4);
      if ($cnyear!=$cyear) {
	$rday = substr($ds,0,6) . $cnyear . substr($ds,10,6);
	$jdrday = StringDateToJD($rday);
	if ($jdrday>=$start && $jdrday<=$stop) {
	  $hs = substr($rday,0,10)." ".$hstart;
	  $jdhs = StringDateToJD($hs);
	  $he = jd2cal($jdhs + $jdDuration, 'FrenchLong');
	  $eve[$ix] = $this->CalEvDupEvent($ref, $hs, $he);
	  $ix++;
	}
      }
    } else {
      $odate = jd2cal($jdDateStart, 'FrenchLong');
      $tsodate = FrenchDateToUnixTs($odate);
      $dayn = jdWeekDay($jdDateStart); // Monday, Tuesday....
      $date = substr($odate, 0, 2);
      $tdate = strftime("%A", $tsodate);
      $month = substr($odate, 3, 2);
      $tmonth = strftime("%B", $tsodate);
      $rday = 0;
      $cancel = false;
      while (!$cancel) {
	if ($date-($rday*7)>0) $rday++;
	else $cancel = true;
      }
      for ($iday=$start; $iday<=$stop; $iday++) {
	$fdate = jd2cal($iday, 'FrenchLong');
	$cmonth = substr($fdate, 3, 2);
	$cdate = substr($fdate, 0, 2);
	if ($cmonth==$month && jdWeekDay($iday)==$dayn && !$this->CalEvIsExclude($fdate)) {
	  $sd = $cdate-(($rday-1)*7);
	  if ($sd> 0 && $sd<7) {
	    $hs = substr($fdate,0,10)." ".$hstart;
	    $jdhs = StringDateToJD($hs);
	    $he = jd2cal(($jdhs+$jdDuration), 'FrenchLong');
	    $eve[$ix++] = $this->CalEvDupEvent($ref, $hs, $he);
	  }
	}
      }
    }
    break;
    
  }
  return $eve;
}

function CalEvIsExclude($date) {
  $te = $this->getTValue("evfc_excludedate");
  if (count($te)>0) {
    foreach ($te as $k => $v) {
      if ($v=="") continue;
      if (substr($v, 0, 10) == substr($date, 0, 10)) return true;
    }
  }
  return false;
}

function CalEvDupEvent($ref, $start, $end) {
  include_once("WGCAL/Lib.wTools.php");
  $e = $ref;
  $e["evt_begdate"] = $start;
  $e["evt_enddate"] = $e["evfc_realenddate"] = $end;
  if ($ref["evfc_evalarm"]==1) {
    $htime = w_dbdate2ts($e["evt_begdate"]);
    $hd = ($e["evfc_alarmd"] * 3600 * 24)  + ($e["evfc_alarmh"] * 3600) + ($e["evfc_alarmm"] * 60);
    $e["evfc_alarmtime"] = w_datets2db($htime - $hd);
  }
  return $e;
}




function __trcJdDate($jd) {
  return "($jd) ".jd2cal($jd, 'FrenchLong');
}

function getJs2DateField($fdate="") {
  // Db 30/08/2005 11:00:00 CEST
  if (preg_match("/^(\d\d)\/(\d\d)\/(\d\d\d\d)\s?(\d\d)?:?(\d\d)?:?(\d\d)?\s?(\w+)?$/", $fdate,$r)) {
    return $r;
  }
  return false;
}

/*
 * Xml production
 *
 */


var $XmlHtmlContent =  "WGCAL:XMLHTMLCONTENT";


function XmlHtmlContent() {

  include_once("EXTERNALS/WGCAL_external.php");
  global $action;

  $this->lay->set("id", $this->id);
  $this->lay->set("title", $this->title);

  $medisplayed = $this->isDisplayed($action->user->fid);
  $myst = $this->myState();

  //echo "[".$this->title."] Displayed : ".($medisplayed?"true":"false")." St $myst<br>";

  $this->lay->set("headSet", false);
  if (count($this->getTValue("evfc_listattid"))>1) {
    if ($myst!=-99) {
      $this->lay->set("headSet", true);
      $this->lay->set("evglobalstate", WGCalGetColorState($myst));
    }
  }

  $color = '';
  if ($color=='' && $medisplayed && $myst!=-99) $color = $this->getUColor($action->user->fid);
  if ($color=='' && $this->getValue("evt_idcreator")!=$action->user->fid && $this->isDisplayed($this->getValue("evt_idcreator"))) $color = $this->getUColor($this->getValue("evt_idcreator"));
  if ($color=="") $color = $this->getUColor($this->getUFirstDisplayed());


  $this->lay->set("bgresumecolor", $color);
  $this->lay->set("textcolor", "black");
  $this->lay->set("borderColor",$color);

  $this->lay->setBlockData("icons", $this->getIcons());
}

function  isDisplayed($fid) {
  global $action;
  $r = $action->parent->param->getUParam("WGCAL_U_RESSDISPLAYED", $action->user->id, $action->parent->GetIdFromName("WGCAL"));
  $dcals = explode("|", $r);
  if (count($dcals)>0) {
    foreach ($dcals as $k => $v) {
      if ($v=="") continue;
      $tc = explode("%", $v);
      if ($tc[0]==$fid && $tc[1]==1) return true;
    } 
  }
  return false;
}

function getUFirstDisplayed() {
  global $action;
  $r = $action->parent->param->getUParam("WGCAL_U_RESSDISPLAYED", $action->user->id, $action->parent->GetIdFromName("WGCAL"));
  $dcals = explode("|", $r);
  if (count($dcals)>0) {
    foreach ($dcals as $k => $v) {
      if ($v=="") continue;
      $tc = explode("%", $v);
      if ($tc[1]==1) return $tc[0];
    } 
  }
  return -1;
}
function getUColor($ufid) {
  global $action;
  $r = $action->parent->param->getUParam("WGCAL_U_RESSDISPLAYED", $action->user->id, $action->parent->GetIdFromName("WGCAL"));
  $dcals = explode("|", $r);
  if (count($dcals)>0) {
    foreach ($dcals as $k => $v) {
      if ($v=="") continue;
      $tc = explode("%", $v);
      if ($tc[0]==$ufid) return $tc[2];
    } 
  }
  return "";
}

function getIcons() {
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
				     "icontitle" => _("icon text with attendees") ));

  $icons = array();
  if ($action->parent->param->getUParam("WGCAL_U_RESUMEICON", $action->user->id, $action->parent->GetIdFromName("WGCAL"))==1) {
    if ($this->isConfidential())  $icons[] = $ricons["CONFID"];
    else {
      if ($this->getValue("EVFC_CALENDARID") > -1)  $icons[] = $ricons["CAL_PRIVATE"];
      if ($this->getValue("EVFC_VISIBILITY") == 1)  $icons[] = $ricons["VIS_PRIV"];
      if ($this->getValue("EVFC_VISIBILITY") == 2)  $icons[] = $ricons["VIS_GRP"];
      if ($this->getValue("EVFC_REPEATMODE") != 0)  $icons[] = $ricons["REPEAT"];
      if ((count($this->getTValue("EVFC_LISTATTID"))>1))  $icons[] = $ricons["GROUP"];
    }
  }
  return $icons;
}

function myState() {
  global $action;
  $attid = $this->getTValue("evfc_listattid");
  $attst = $this->getTValue("evfc_listattst");
  foreach ($attid as $k => $v) {
    if ($action->user->fid == $v) return $attst[$k];
  }
  return -99;
}


function setEventMenu() {
  global $action;
  $url = htmlentities("[CORE_STANDURL]app=WGCAL&action=WGCAL_SETEVENTSTATE&st=2&id=%EVID%");
  $pico = ($action->parent->param->getUParam("WGCAL_U_ICONPOPUP", $action->user->id, $action->parent->GetIdFromName("WGCAL"))==1);
  $menu = array(
		array( "item" => "accept", "status"=>2, "type"=>1, "icon"=>"[IMG:wm-evaccept.gif]", "popupIcon" => $pico,
		       "label" => "[TEXT:accept this]", "descr" => "[TEXT:accept this]",
		       "actionmode" => 1, "actionevent" => 0, "actiontarget" => "wwwww", 
		       "action" => htmlentities("[CORE_STANDURL]app=WGCAL&action=WGCAL_SETEVENTSTATE&st=2&id=%EVPID%") ),
		array( "item" => "reject", "status"=>2, "type"=>1, "icon"=>"[IMG:wm-evrefuse.gif]", "popupIcon" => $pico,
		       "label" => "[TEXT:reject this]", "descr" => "[TEXT:reject this]",
		       "actionmode" => 1, "actionevent" => 0, "actiontarget" => "wwwww", 
		       "action" => htmlentities("[CORE_STANDURL]app=WGCAL&action=WGCAL_SETEVENTSTATE&st=3&id=%EVPID%") ),
		array( "item" => "tbcrv", "status"=>2, "type"=>1, "icon"=>"[IMG:wm-evconfirm.gif]", "popupIcon" => $pico,
		       "label" => "[TEXT:to be confirm this]", "descr" => "[TEXT:to be confirm this]",
		       "actionmode" => 1, "actionevent" => 0, "actiontarget" => "wwwww", 
		       "action" => htmlentities("[CORE_STANDURL]app=WGCAL&action=WGCAL_SETEVENTSTATE&st=4&id=%EVPID%") ),
		array( "item" => "sep1", "status"=>2, "type"=>2, "popupIcon" => false),
		array( "item" => "view", 
		       "status"=>2, 
		       "type"=>1, 
		       "icon"=>"[IMG:wm-evview.gif]",
		       "label" => "[TEXT:view this]", 
		       "descr" => "[TEXT:view this]",
		       "actionmode" => 1, 
		       "actionevent" => 0, 
		       "actiontarget" => "EditCalendarEvent", 
		       "action" => htmlentities("[CORE_STANDURL]app=FDL&action=IMPCARD&id=%EVPID%"),
		       "popupIcon" => $pico ),
		array( "item" => "edit", 
		       "status"=>2, 
		       "type"=>1, 
		       "icon"=>"[IMG:wm-evedit.gif]",
		       "label" => "[TEXT:edit this]", 
		       "descr" => "[TEXT:edit this]",
		       "actionmode" => 3, 
		       "actionevent" => 0, 
		       "actiontarget" => "EditCalendarEvent", 
		       "action" => htmlentities("subwindow(400,600,'rvedit','[CORE_STANDURL]app=GENERIC&action=GENERIC_EDIT&id=%EVPID%')"),
		       "popupIcon" => $pico ),
		array( "item" => "deloccur", 
		       "status"=>2, 
		       "type"=>1, 
		       "icon"=>"[IMG:wm-deloccur.gif]",
		       "label" => "[TEXT:delete this occurence]", 
		       "descr" => "[TEXT:delete this occurence]",
		       "actionmode" => 1, 
		       "actionevent" => 0, 
		       "actiontarget" => "EditCalendarEvent", 
		       "action" => htmlentities("[CORE_STANDURL]app=WGCAL&action=WGCAL_DELOCCUR&id=%EVPID%&evocc=%TS%"),
		       "popupIcon" => $pico ),
		array( "item" => "delete", 
		       "status"=>2, 
		       "type"=>1, 
		       "icon"=>"[IMG:wm-evdelete.gif]",
		       "label" => "[TEXT:delete this]", 
		       "descr" => "[TEXT:delete this]",
		       "actionmode" => 1, 
		       "actionevent" => 0, 
		       "actiontarget" => "EditCalendarEvent", 
		       "action" => htmlentities("[CORE_STANDURL]&app=WGCAL&action=WGCAL_DELETEEVENT&id=%EVPID%"),
		       "popupIcon" => $pico ),
		array( "item" => "history", 
		       "status"=>2, 
		       "type"=>1, 
		       "icon"=>"[IMG:wm-evhistory.gif]",
		       "label" => "[TEXT:history]", 
		       "descr" => "[TEXT:history]",
		       "actionmode" => 3, 
		       "actionevent" => 0, 
		       "actiontarget" => "EditCalendarEvent", 
		       "action" => htmlentities("subwindow(200,450,'rvhisto','[CORE_STANDURL]&app=WGCAL&action=WGCAL_HISTO&id=%EVPID%')"),
		       "popupIcon" => $pico ),
		array( "item" => "dactions", "status"=>2, "type"=>0, "label" => "[TEXT:Delegation]", "popupIcon" => false),
		array( "item" => "daccept", "status"=>2, "type"=>1, "icon"=>"[IMG:wm-evaccept.gif]", "popupIcon" => $pico,
		       "label" => "[TEXT:accept this]", "descr" => "[TEXT:accept this]",
		       "actionmode" => 1, "actionevent" => 0, "actiontarget" => "wwwww", 
		       "action" => htmlentities("[CORE_STANDURL]app=WGCAL&action=WGCAL_SETEVENTSTATE&owner=1&st=2&id=%EVPID%") ),
		array( "item" => "reject", "status"=>2, "type"=>1, "icon"=>"[IMG:wm-evrefuse.gif]", "popupIcon" => $pico,
		       "label" => "[TEXT:reject this]", "descr" => "[TEXT:reject this]",
		       "actionmode" => 1, "actionevent" => 0, "actiontarget" => "wwwww", 
		       "action" => htmlentities("[CORE_STANDURL]app=WGCAL&action=WGCAL_SETEVENTSTATE&owner=1&st=3&id=%EVPID%") ),
		array( "item" => "tbcrv", "status"=>2, "type"=>1, "icon"=>"[IMG:wm-evconfirm.gif]", "popupIcon" => $pico,
		       "label" => "[TEXT:to be confirm this]", "descr" => "[TEXT:to be confirm this]",
		       "actionmode" => 1, "actionevent" => 0, "actiontarget" => "wwwww", 
		       "action" => htmlentities("[CORE_STANDURL]app=WGCAL&action=WGCAL_SETEVENTSTATE&owner=1&st=4&id=%EVPID%") ),
		);
  return $menu;
  
}
    

function setMenuRef() {
  return array();
}

