
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: Method.RendezVousEvent.php,v 1.32 2006/05/22 09:47:46 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage
 */

var $calVResume     = "WGCAL:CALEV_ABSTRACT";
var $calVCard       = "WGCAL:CALEV_CARD";
var $calVLongText   = "WGCAL:CALEV_VIEWLTEXT";
var $calVShortText  = "WGCAL:CALEV_VIEWSTEXT";
 
var $viewShortEvent = "WGCAL:VIEWSHORTEVENT:U";
 
var $viewCalJsCode  = "WGCAL:VIEWCALJSCODE:U";

/*
 * 
 */
function viewCalJsCode() {
  include_once('WGCAL/Lib.wTools.php');
  $this->lay->set("ID", $this->id);
  $this->lay->set("FROMID", $this->fromid);
  $this->lay->set("EVT_IDINITIATOR", $this->getValue("evt_idinitiator"));
  $this->lay->set("EVT_FROMINITIATORID", $this->getValue("evt_frominitiatorid"));

  $this->lay->set("displayable", ($this->isDisplayable()?"true":"false"));
  $this->lay->set("title", addSlashes($this->getTitleInfo()));
  $this->lay->set("start", fcalLocalFrenchDateToUnixTs($this->getValue("evt_begdate"), true));
  $this->lay->set("lstart", $this->getValue("evt_begdate"));
  $end = ($this->getValue("evfc_realenddate") == "" 
	  ?  $this->getValue("evt_enddate") : $this->getValue("evfc_realenddate"));
  $this->lay->set("lend", $end);
  $this->lay->set("end", fcalLocalFrenchDateToUnixTs($end, true));
  $dattr = $defaults;
  if (method_exists($this, "getMenuLoadUrl")) $this->lay->set("menuurl", $this->getMenuLoadUrl());
  else  $this->lay->set("menuurl", "");
  if (method_exists($this, "getDisplayAttr")) $dattr = $this->getDisplayAttr();
  $this->lay->set("icons", $dattr["icons"]);
  $this->lay->set("bgColor", $dattr["bgColor"]);
  $this->lay->set("fgColor", $dattr["fgColor"]);
  $this->lay->set("topColor", $dattr["topColor"]);
  $this->lay->set("bottomColor", $dattr["bottomColor"]);
  $this->lay->set("rightColor", $dattr["rightColor"]);
  $this->lay->set("leftColor", $dattr["leftColor"]);
  $this->lay->set("editable", ($this->isEditable()?"true":"false"));
}

function getMenuLoadUrl() {
  return getParam("CORE_STANDURL")."app=WGCAL&action=WGCAL_GETMENU&id=".$this->getValue("evt_idinitiator");
}

function getDisplayAttr() {
  global $action;
  include_once("WGCAL/Lib.wTools.php");
  $myid = $action->user->fid;
  $ressd = $this->getRMatrix();
  $attrd["icons"] =  "";
  $attrd["bgColor"] =  
    $attrd["rightColor"] = 
    $attrd["bottomColor"] = 
    $attrd["leftColor"] = 
    $attrd["topColor"] = $this->getDisplayColor();

  if ($this->isDisplayable()) {

    if ($this->getValue( "evfc_iconlist")!='') {
      $icol = explode('|',$this->getValue( "evfc_iconlist"));
      foreach ($icol as $k => $v) if ($v!='') {
	$ics = fcalGetIcon($v, false);
	$icol[$k] = "'".$ics["src"]."'";
      }
      $attrd["icons"] = implode(',', $icol);
    }

    if (count($ressd)>1 && isset($ressd[$myid]) && $ressd[$myid]["displayed"]) {
      $attrd["topColor"]  = WGCalGetColorState($ressd[$myid]["state"], $attrd["bgColor"]);
    }
    if ($this->getValue("evfc_dcreatorid")==$myid && $this->getValue("evfc_dcreatorid")!=$this->getValue("evt_idcreator")) {
      $attrd["rightColor"]  = $this->getUColor($myid);
    }
    
    $cat = $this->getValue("evt_code");
    $catg = wGetCategories();
    if ($cat>0) {
      foreach ($catg as $k=>$v) { if ($v["id"] == $cat) $attrd["leftColor"] = $v["color"];  }
    }
  }
  return $attrd;
}


function isEditable() {
  return ($this->Control("edit")=="" ? true : false);
}

function getTitleInfo() {
  return ($this->isDisplayable() ? $this->getValue("evt_title") : _("confidential event"));
}

function isDisplayable() {
  return ($this->Control("confidential")=="" 
	  || ($this->confidential==0 && $this->Control("view")==""));
}

function getIconsBlock() {
  $tico = null;
  if ($this->getValue("icons")!="") {
    $it = explode(",", $this->getValue("icons"));
    if (count($it)>0) {
      foreach ($it as $ki => $vi) $tico[]= array("icosrc" => str_replace("'","",$vi));
    }
  }
  return $tico;
}

function getHoursInfos() {
  $start = $this->getValue("evt_begdate");
  $end = ($this->getValue("evfc_realenddate") == "" ? $this->getValue("evt_enddate") : $this->getValue("evfc_realenddate"));
  $dstart = substr($start, 0, 2);
  $dend = substr($end, 0, 2);
  $hstart = substr($start,11,5);
  $hend = substr($end,11,5);
  $hour1 = $hour2 = "";
  $hour1 = substr($start, 0, 2)."/".substr($start, 3, 2)."/".substr($start, 8, 2);
  if (substr($start, 0, 10)!=substr($end, 0, 10)) {
    $hour1 .= " ".$hstart;
    $hour2 = substr($end, 0, 2)."/".substr($end, 3, 2)." ".substr($end, 8, 2);
    $hour2 .= " ".$hend;
    $p = 4;
  } else {
    if ($hstart==$hend && $hend=="00:00") {
      $hour2 = "("._("no hour").")";
      $p = 0;
    } else if ($hstart=="00:00" && $hend=="23:59") {
      $hour2 = "("._("all the day _ short").")";
      $p = 1;
   } else {
      $hour2 = $hstart."&nbsp;-&nbsp;".$hend;
      $p = 4;
   }
  }
  return array($hour1, $hour2, $p);
}

function viewShortEvent() {
  $th = $this->getHoursInfos();
  $hour1 = $th[0];
  $hour2 = $th[1];
  $this->lay->set("hour1", $hour1);
  $this->lay->set("hour2", $hour2);
 
  $this->lay->setBlockData("Icons", $this->getIconsBlock());
 
  $this->lay->set("title", stripSlashes($this->getValue("evt_title")));
  $this->lay->set("owner", $this->getValue("evt_creator"));
  $this->lay->set("note", $this->getValue("evt_desc"));
  $tl = ($this->getValue("evt_desc")!="" || $hour2!="");
  $this->lay->set("twoLine", $tl);
 
}
     

function getRMatrix() {
  global $action;
  static $ressd = false;

//   if ($ressd===false) {

    $cals = explode("|", $action->GetParam("WGCAL_U_RESSDISPLAYED", $action->user->id));
    $attids = $this->getTValue("evfc_listattid");
    $attsta = $this->getTValue("evfc_listattst");
    $attref = $this->getTValue("evfc_rejectattid");

    $ressd=array();

    foreach ($attids as $k => $v) {
      if (! isset($ressd[$v]) ) {
	$ressd[$v]["state"] = $attsta[$k];
	$ressd[$v]["displayed"] = false;
	$ressd[$v]["color"] = "white";
      }
    }
    while (list($k,$v) = each($cals)) {
      if ($v!="") {
	$tc = explode("%", $v);
	if ($tc[0] != "" && isset($ressd[$tc[0]])) {
	  $ressd[$tc[0]]["displayed"] = ($tc[1] == 1 ? true : false );
	  $ressd[$tc[0]]["color"] = $tc[2];
	}
      }
    }
//     print_r2($ressd,false);
//   }
  return $ressd;
}
  


function getDisplayColor() {

  global $action;

  $myid = $action->user->fid;
  static $rcolor = false;

//   if ($rcolor===false) {
  
  $color = "";
  $ressd = $this->getRMatrix();
  
  $showrefused = $action->getParam("WGCAL_U_DISPLAYREFUSED", 0);
  $ownerid = $this->getValue("evt_idcreator");
  
  if (isset($ressd[$myid]) && $ressd[$myid]["displayed"] && (($ressd[$myid]["state"]!=3 && $showrefused==0) || $showrefused==1)) {
    $color = $ressd[$myid]["color"];
  } else {
    if ($myid!=$ownerid && (isset($ressd[$ownerid]) && $ressd[$ownerid]["state"]!=3 && $ressd[$ownerid]["displayed"])) {
      $color = $ressd[$ownerid]["color"];
    } else {
      foreach ($ressd as $kr => $vr) {
	if ($vr["displayed"]) {
	  $color = $vr["color"];
	  break;
	}
      }
    }
  }
  $rcolor = ($color!=""?$color:"#d2f5f7");
  //   }
  return $rcolor;
}
  


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
//   AddWarningMsg("start=".$this->__trcJdDate($jdDateStart)." end=".$this->__trcJdDate($jdREndDate));
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
      if (($jdhs<=$jd2 && $jdhe>=$jd1)) {
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
	if (($jdhs<=$jd2 && $jdhe>=$jd1)) {
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
	if (($jdhs<=$jd2 && $jdhe>=$jd1)) {
          $eve[$ix++] = $this->CalEvDupEvent($ref, $hs, $he);
        }
      }
    
    } else {

      $odate = jd2cal($jdDateStart, 'FrenchLong');
      $day = substr($odate,0,2);
      $month = substr($odate,3,2);
      $year  = substr($odate,6,4);
      $dayweek = gmdate("w", gmmktime(0,0,0,$month,$day,$year));
      $occur = wComputeNWeekDayInMonth($odate);
      for ($iday=$jd1; $iday<=$jd2; $iday++) {
	$thed = jd2cal($iday, 'FrenchLong');
	$thedn = substr($thed,0,2);
	$themn = substr($thed,3,2);
	if ($this->CalEvIsExclude($thed)) continue;
	$ndate = wGetNWeekDayForMonth($occur, $dayweek, substr($thed,3,2), substr($thed,6,4));
	if ($ndate==$thedn) {
	  $hs = str_pad($ndate,2,"0",STR_PAD_LEFT)."/".substr($thed,3,2)."/".substr($thed,6,4)." ".$hstart;
	  $jdhs = StringDateToJD($hs);
	  $jdhe = $jdhs+$jdDuration;
	  $he = jd2cal($jdhe, 'FrenchLong');
	  $eve[$ix++] = $this->CalEvDupEvent($ref, $hs, $he);
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
      $day = substr($odate,0,2);
      $month = substr($odate,3,2);
      $year  = substr($odate,6,4);
      $dayweek = gmdate("w", gmmktime(0,0,0,$month,$day,$year));
      $occur = wComputeNWeekDayInMonth($odate);
      for ($iday=$jd1; $iday<=$jd2; $iday++) {
	$thed = jd2cal($iday, 'FrenchLong');
	$thedn = substr($thed,0,2);
	$themn = substr($thed,3,2);
 	if ($themn!=$month) continue;
	if ($this->CalEvIsExclude($thed)) continue;
	$ndate = wGetNWeekDayForMonth($occur, $dayweek, substr($thed,3,2), substr($thed,6,4));
	if ($ndate==$thedn) {
	  $hs = str_pad($ndate,2,"0",STR_PAD_LEFT)."/".substr($thed,3,2)."/".substr($thed,6,4)." ".$hstart;
	  $jdhs = StringDateToJD($hs);
	  $jdhe = $jdhs+$jdDuration;
	  $he = jd2cal($jdhe, 'FrenchLong');
	  $eve[$ix++] = $this->CalEvDupEvent($ref, $hs, $he);
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
  static $ucolor = array();

  if (!isset($ucolor[$ufid])) {
    $ucolor[$ufid] = "";
    $r = $action->parent->param->getUParam("WGCAL_U_RESSDISPLAYED", 
					   $action->user->id, 
					   $action->parent->GetIdFromName("WGCAL"));
    $dcals = explode("|", $r);
    if (count($dcals)>0) {
      foreach ($dcals as $k => $v) {
	if ($v=="") continue;
	$tc = explode("%", $v);
	if ($tc[0]==$ufid) {
	  $ucolor[$ufid] = $tc[2];
	  break;
	} 
      }
    }
  }
  return $ucolor[$ufid];
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

