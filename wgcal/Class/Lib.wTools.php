<?php
include_once("Class.Param.php");
include_once("FDL/freedom_util.php");
include_once("FDL/Lib.Color.php");
include_once("FDL/Lib.Dir.php");
include_once("EXTERNALS/WGCAL_external.php");


define(SEC_PER_DAY, 24*3600);
define(SEC_PER_HOUR,3600);
define(SEC_PER_MIN, 60);

// ----------------------------------------------------
// Date and time format
// ----------------------------------------------------
function w_dbsec($dbtime) { return substr($dbtime,17,2); }
function w_dbmin($dbtime) { return substr($dbtime,14,2); }
function w_dbhou($dbtime) { return substr($dbtime,11,2); }
function w_dbday($dbtime) { return substr($dbtime,0,2); }
function w_dbmon($dbtime) { return substr($dbtime,3,2); }
function w_dbyea($dbtime) { return substr($dbtime,6,4); }


function wDebugMode() {
  global $action;
  return (file_exists("WGCAL/wgcal.debug") && $action->user->id==1);
}

function w_dbdate2ts($dbtime, $utc=true) {
  if ($utc) 
    return gmmktime(w_dbhou($dbtime), 
			       w_dbmin($dbtime), 
			       w_dbsec($dbtime), 
			       w_dbmon($dbtime), 
			       w_dbday($dbtime), 
			       w_dbyea($dbtime));
  else 
    return gmmktime(w_dbhou($dbtime), 
		    w_dbmin($dbtime), 
		    w_dbsec($dbtime), 
		    w_dbmon($dbtime), 
		    w_dbday($dbtime), 
		    w_dbyea($dbtime));
}

function ts2db($t, $f="H:i d/m/Y") {
  return gmdate($f, $t);
}

function w_datets2db($d, $hm = true) {  	 
  $fmt = ($hm ? "d/m/Y H:i" : "d/m/Y" ); 	 
  $s = gmdate($fmt, $d); 	 
  return $s; 	 
}


define(WD_FMT_DAYSTEXT, 1); // Day Short 
define(WD_FMT_DAYLTEXT, 2); // Day Long
define(WD_FMT_DAYFTEXT, 3); // Day Full
define(WD_FMT_TDAY, 4); // Day Full
define(WD_FMT_TMONTH, 5); // YDay Full
function w_strftime($ts, $fmt=0, $ucw=true) {
  global $action;

  switch ($fmt) {
  case WD_FMT_TDAY: $fms = "%A %d"; break;
  case WD_FMT_TMONTH: $fms = "%B"; break;
  case WD_FMT_DAYSTEXT: $fms = "%a %d %b"; break;
  case WD_FMT_DAYLTEXT: $fms = "%A %d %B"; break;
  case WD_FMT_DAYFTEXT: $fms = "%A %d %B %Y"; break;
  case WD_FMT_QSEARCH: $fms = "%Y-%m-%d %H:%M:%S"; break;
  default: $fms = "%d/%m/%Y %H:%M"; break;
  }
  $locz = array("C", $action->getParam("WGCAL_U_LANG", "fr_FR"));
  $x = setlocale(LC_TIME, "$z");
  $dr = ($ucw?ucwords(strftime($fms,$ts)):strftime($fms,$ts));
  return $dr;
}

/* 
 * Return timestamp for first week day 
 */
function w_GetFirstDayOfWeek($ts) 
{
	if ($ts<=0) return false;
 	$iday  = gmdate("w",$ts);
	$dt = 1-$iday;
        $tsfwd = $ts - (($iday-1) * SEC_PER_DAY);
	$dd = gmdate("d", $tsfwd);
 	$mm = gmdate("n", $tsfwd);
 	$yy = gmdate("Y", $tsfwd);
	$fwdt = gmmktime ( 0, 0, 0, $mm, $dd, $yy);
	return $fwdt;
}

function w_GetDayFromTs($ts) 
{
  if ($ts<=0) return false;
  $dd = gmdate("d", $ts);
  $mm = gmdate("n", $ts);
  $yy = gmdate("Y", $ts);
  $fwdt = gmmktime ( 0, 0, 0, $mm, $dd, $yy);
  return $fwdt;
}

function w_DaysInMonth($ts) 
{
  $timepieces = getdate($ts);
  $thisYear          = $timepieces["year"];
  $thisMonth        = $timepieces["mon"];
  for($thisDay=1;checkdate($thisMonth,$thisDay,$thisYear);$thisDay++);
  return ($thisDay-1);
} 

/*
 * Return N for date, Nth day in month
 */
function wComputeNWeekDayInMonth($date) {
  $year  = substr($date,6,4);
  $month = substr($date,3,2);
  $day   = substr($date,0,2);
  $dayinweek = gmdate("w", gmmktime(0,0,0,$month,$day,$year));
  $t_dayinweek = gmdate("D", gmmktime(0,0,0,$month,$day,$year));
  
  $start = 1;
  
  $occur = 0;
  for ($i=$start; $i<=$day; $i++) {
    $cd = gmdate("w", gmmktime(0,0,0,$month,$i,$year));
    if ($cd==$dayinweek) $occur++;
  }
//   echo "Le $day - $month - $year, le $occur ieme $t_dayinweek du mois \n";
  return $occur;
}

// return day nu (1..31) for Nth weekday (0..6) in month in year
function wGetNWeekDayForMonth($n, $weekday, $month, $year) {
  $firstday = 1;
  $lastday  = gmdate("d", gmmktime(0,0,0,$month+1,0,$year));
  $occ = 0;
  $day = 0;
  for ($i=$firstday; $i<=$lastday && $day==0; $i++) {
    $cd = gmdate("w", gmmktime(0,0,0,$month,$i,$year));
    if ($cd==$weekday) $occ++;
    if ($occ==$n) $day=$i;
  }
//   echo "Le $n $weekday du  mois de $month/$year est le $day\n";
  return $day;
}

// ----------------------------------------------------
// Groups....
// ----------------------------------------------------
function wPrintGroups() {
  global $action;
  print_r2(wGetGroups());
}

function  wGetGroups() {
  global $action;
  static $tgroups = false;
  if ($tgroups===false) {
    $p = new Param($action->dbaccess, array("WGCAL_USEDGROUPS", PARAM_APP, $action->parent->GetIdFromName("WGCAL")));
    $tgroups = false;
    if ($p->val!="") {
      $tga = explode("|", $p->val);
      $tgroups = array();
      foreach ($tga as $kg => $vg) {
	if ($vg!="") $tgroups[$vg] = $vg;
      }
    }
  }
  return $tgroups;
}

function  wSetGroups($groups) {
  global $action;
  $action->parent->param->Set("WGCAL_USEDGROUPS", $groups, PARAM_APP, $action->parent->GetIdFromName("WGCAL"));
  return;
}
  

function wGroupIsUsed($gfid) {
  global $action;
  $tg = wGetGroups();
  return isset($tg[$gfid]);
}

function wGetUserGroups($fid=-1) {
  global $action;

  static $u_rgroups = array();

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $fid = ($fid==-1 ? $action->user->fid : $fid);

  if (!isset($u_rgroups[$fid])) {

    // get wgcal used groups
    $wgcal_groups = wGetGroups();
    
    // get user groups
    $user = new_Doc($dbaccess, $fid);
    $user_groups = $user->getTValue("us_idgroup");

    // compute user real groups (user groups used in Agenda)
    $u_rgroups[$fid] = array();
    foreach ($user_groups as $kg => $vg) {
      if (isset($wgcal_groups[$vg])) {
	$u_rgroups[$fid][$vg]["gid"] = $vg;
	$u_rgroups[$fid][$vg]["sel"] = false;
      }
    }
    
    $tg = $user->getTValue("us_wgcal_gid");
    foreach ($tg as $kg => $vg) {
      if (isset($u_rgroups[$fid][$vg])) $u_rgroups[$fid][$vg]["sel"] = true;
    }
  }
  return $u_rgroups[$fid];
}


// ----------------------------------------------------
// Others....
// ----------------------------------------------------
function wPbool($b) {
  return ($b?"true":"false");
}

function setToolsLayout(&$action, $tool="", $forced=false) {

  // Set initial visibility
  $all =  explode("|", $action->GetParam("WGCAL_U_TOOLSSTATE", ""));
  $state = array();
  foreach ($all as $k => $v) {
    $t = explode("%",$v);
    $state[$t[0]] = ($t[1]==0?0:1);
  }
  $s = (isset($state[$tool]) ? $state[$tool] : 1 );
  $vis = ($s ? "block" : "none" );
  if ($forced) {
     $action->lay->set( "b".$tool, "wToolButtonUnselect");
     $action->lay->set( "o".$tool, true);
     $action->lay->set( "v".$tool, "block");
  } else {
     $action->lay->set( "b".$tool, ($s==1? "wToolButtonUnselect":"wToolButtonSelect"));
     $action->lay->set( "o".$tool, ($s==1? true : false ));
     $action->lay->set( "v".$tool, $vis);
  }
  return;
}

function wGetRessDisplayed() {
  global $action;
  static $ress = false;
  if ($ress===false) {
    $ress = array();
    $ir = 0;
    $cals = explode("|", $action->GetParam("WGCAL_U_RESSDISPLAYED", $action->user->id));
    while (list($k,$v) = each($cals)) {
      if ($v!="") {
	$tc = explode("%", $v);
	if ($tc[0] != "" && $tc[1] == 1) {
	  $ress[$ir]->id = $tc[0];
	  $ress[$ir]->color = $tc[2]; 
	  $ir++;
	}
      }
    }
  }
  return $ress;
}

function initCategories() {
  global $action;
  $dbaccess = $action->getParam("FREEDOM_DB");
  $calf = getIdFromName($dbaccess, "CALEVENT");
  $glist = GetChildDoc($dbaccess, 0, 0, "ALL", array("catg_famid = $calf"), $action->user->id, "LIST", "CATEGORIES");
  if (count($glist)==0) {
    $catl = createDoc($dbaccess,"CATEGORIES");
    if (!$catl) {
      return false;
    }
    $catl->setValue("catg_famid", getFamIdFromName($dbaccess, "CALEVENT"));
    $rv = new_Doc($dbaccess, "CALEVENT");
    $catl->setValue("catg_fam", $rv->getTitle());
    $catl->setValue("catg_id",array(0));
    $catl->setValue("catg_name",array(_("no category")));
    $catl->setValue("catg_order",array(0));
    $catl->setValue("catg_color",array(""));
    $catl->Add();
  } else {
    $catl = $glist[0];
  }
  return $catl;
}

function wGetCategories() {
  global $action;
  static $categories = false;
  if ($categories===false) {
    $dbaccess = $action->getParam("FREEDOM_DB");
    $catl = initCategories();
    $ids = $catl->getTValue("catg_id");
    $col = $catl->getTValue("catg_color");
    $nam = $catl->getTValue("catg_name");
    $categories = array();
    foreach ($ids as $k=>$v) {
      $categories[] = array( "id" => $ids[$k], "label" => $nam[$k], "color" => $col[$k] );
    }
  }
  return $categories;
}

function wGetCategoriesLabel($id) {
  global $action;
  $catg = wGetCategories();
  foreach ($catg as $kc => $vc) {
    if ($id==$vc["id"]) return $vc["label"];
  }
  return "";
}

global $aTrace;
$aTrace = array();
function addTrace($s) {
  global $aTrace;
  $aTrace[count($aTrace)] = $s;
}

function showTrace() {
  global $aTrace;
  echo '<div style="bgcolor:yellow; color:black; border:2px ouset">';
  foreach ($aTrace as $k => $v) echo $v."<br>";
  echo "<div>";
}


function wUSort(&$ar, $field) {
  global $fsort;
  $fsort = $field;
  if (count($ar)>0) usort($ar, wUSortCmp);
  return;
}

function wUSortCmp(&$a, &$b) {
  global $fsort;
  return (strcmp($a[$fsort], $b[$fsort]));
}
  




function  wgcalGetRColor($r=-1) {
  global $action;
  static $rcolor = array();
  $r = ($r==-1 ? $action->user->fid : $r);

  if (!isset($rcolor[$r])) {
    $color = "red";
    $cals = explode("|", $action->GetParam("WGCAL_U_RESSDISPLAYED", $action->user->id));
    while (list($k,$v) = each($cals)) {
      if ($v!="") {
	$tc = explode("%", $v);
	if ($tc[0] != "" && $tc[0]==$r) $color = $tc[2];
      }
    }
    $rcolor[$r] = $color;
  }
  return $rcolor[$r];
}

 function wgcalGetRessourcesMatrix($ev)  {
  global $action;

  static $ressd = array();

  if (!isset($ressd[$ev])) {

    $event = new_Doc($action->getParam("FREEDOM_DB"), $ev);
    
    $tress  = $event->getTValue("CALEV_ATTID");
    $tresst = $event->getTValue("CALEV_ATTTITLE");
    $tresse = $event->getTValue("CALEV_ATTSTATE");
    $tressg = $event->getTValue("CALEV_ATTGROUP");
    
    foreach ($tress as $k => $v) {
      if (!(isset($ressd[$ev][$v]) && $tressg[$k]==-1)) {
	$ressd[$ev][$v]["id"] = $event->id;
	$ressd[$ev][$v]["title"] = $tresst[$k];
	$ressd[$ev][$v]["state"] = $tresse[$k];
	$ressd[$ev][$v]["color"] = "white";
	$ressd[$ev][$v]["displayed"] = false;
	$ressd[$ev][$v]["group"] = $tressg[$k];
      }
    }

    $cals = explode("|", $action->GetParam("WGCAL_U_RESSDISPLAYED", $action->user->id));
    while (list($k,$v) = each($cals)) {
      if ($v!="") {
	$tc = explode("%", $v);
	if ($tc[0] != "" && isset($ressd[$ev][$tc[0]])) {
	  $ressd[$ev][$tc[0]]["displayed"] = ($tc[1] == 1 ? true : false );
	  $ressd[$ev][$tc[0]]["color"] = $tc[2];
	}
      }
    }
  }
  return $ressd[$ev];
}

function wGetSinglePEvent($id) {
  global $action;
  $filter = array();
  $vm = $action->GetParam("WGCAL_U_DAYSVIEWED");
  $stdate = $action->GetParam("WGCAL_U_CALCURDATE");
  $sdate = w_GetDayFromTs($stdate); 
  $firstWeekDay = w_GetFirstDayOfWeek($sdate);
  $edate = $firstWeekDay + ($vm * SEC_PER_DAY) - 1;
  $d1 = ts2db($firstWeekDay, "Y-m-d 00:00:00");
  $d2 = ts2db($edate, "Y-m-d 23:59:59");
  $filter[] = "(evt_idinitiator = ".$id.")";
  $ev = wGetEvents($d1, $d2, true, $filter);
  return $ev;
}

function wGetEvents($d1, $d2, $explode=true, $filter=array(), $famid="EVENT_FROM_CAL") {

  include_once("WGCAL/Lib.WGCal.php");
  global $action;
 
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $qev = GetHttpVars("qev", getIdFromName($dbaccess,"WG_AGENDA"));

  $fl =  $action->getParam("WGCAL_G_VFAM", "CALEVENT");

  $famr = GetHttpVars("famref", $fl);
  $famrt = explode("|", $famr);
  $fti = array();
  foreach ($famrt as $k => $v) {
    $fti[] = (is_int($v) ? $v : getIdFromName($dbaccess, chop($v)));
  }
  $idfamref = implode("|", $fti);
  if ($idfamref=="") $idfamref=" ";
  setHttpVar("idfamref", $idfamref);
  
  // Init the ressources
  $showall = false;
  $dforce = false;
  $res = GetHttpVars("ress", "");
  if ($res!="") {
    if ($res=="-") {
      $showall = true;
      $idres = "";
      $dforce = true;
    } else {
      $idres = $res;
      $dforce = true;
   }
  } else {  
    $ress = wGetRessDisplayed();
    $tt = array();
    foreach ($ress as $k => $v) $tt[]=$v->id;
    $idres = implode("|", $tt);
  }
  $action->log->error("res=[$res], idres=[$idres]");
  global $ZONE_ARGS;
  $ZONE_ARGS["idres"]=$idres;
//   setHttpVar("idres", $idres);
  $tr = explode("|", $idres);
  foreach ($tr as $k=>$v)   $tress[$v] = $v;

  $events = array();
  $dre=new_Doc($dbaccess, $qev);
  $dre->setValue("se_famid", getIdFromName($dbaccess, $famid));
  $events = $dre->getEvents($d1, $d2, $explode, $filter);

  // Post process search results --------------
  $defaults = array( "icons" => array(),
		     "bgColor" => "lightblue",
		     "fgColor" => "black",
		     "topColor" => "lightblue",
		     "rightColor" => "lightblue",
		     "bottomColor" => "lightblue",
		     "leftColor" => "lightblue",
		     );
  $rg = 0;
  $showrefused = ( $action->getParam("WGCAL_U_DISPLAYREFUSED")!=1 ? false : true);
  $myid = $action->user->fid;

  $newevents = array();
  foreach ($events as $k=>$v) {

    $evdisplay = true;
    $ev = getDocObject($dbaccess, $v);
    if (!$showall) {

      $tm = $ev->getRMatrix();
      
      $disp = -1;
      if ($tress[$myid] && $tm[$myid] && ($tm[$myid]["displayed"]||$dforce)) {
	if ( ($showrefused && $tm[$myid]["refused"]) || !$tm[$myid]["refused"]) $disp=1;
      }
      if ($disp!=1) {
	foreach ($tress as $kr => $vr) {
	  if ($vr!=$myid && isset($tm[$vr]) && ($tm[$vr]["displayed"]||$dforce) && !$tm[$vr]["refused"]) $disp=1;
	}
      }
      
      $evdisplay = ($disp==1 ? true : false );
    }
    if ($evdisplay) {
      $events[$k]["rg"] = $rg;
      $events[$k]["jscode"] = $ev->viewdoc($ev->viewCalJsCode);
      $events[$k]["dattr"] = (method_exists($ev, "getDisplayAttr") ? $ev->getDisplayAttr() : $defaults );
      $rg++;
      $newevents[] = $events[$k];
    }
  }
  return $newevents;
}

function wGetUsedFamilies() {
  global $action;
  static $famused = false;
  if ($famused===false) {
    $dbaccess = $action->getParam("FREEDOM_DB");
    $famused = array();
    $ftu = $action->GetParam("WGCAL_FAMRUSED", "IUSER,1,1|");
    $ftt = explode("|", $ftu);
    $doct = createDoc($dbaccess, "BASE", false);
    foreach ($ftt as $k => $v) {
      if ($v=="") continue;
      $suf = explode(",", $v);
      $dt = getTDoc($dbaccess, getIdFromName($dbaccess, $suf[0]));
      $id = getV($dt, "id");
      if (!is_numeric($id) || !$id>0) continue;
      $famused[] = array( "id" => $id,
			  "name" => $suf[0],
			  "title" => getV($dt, "title"),
			  "icon" => $doct->getIcon(getV($dt, "icon")),
			  "isSelected" => ($suf[1]==1? true : false ),
			  "isInteractive" => ($suf[2]==1? true : false ),
			  "inMeeting" => ($suf[2]>=0? true : false ) );
    }
  }
  return $famused;
}

function wIsFamilieInteractive($fid) {
  global $action;
  $dbaccess = $action->getParam("FREEDOM_DB");
  if (!is_numeric($fid)) $fid = getIdFromName($dbaccess, $fid);
  if (!$fid>0) return false;
  $rf = wGetUsedFamilies();
  foreach ($rf as $k => $v) {
    if ($v["id"]==$fid && $v["isInteractive"]) return true;
  }
  return false;
}

function GetFilesByExt($dir=".", $ext="") {
  $flist = array();
  if ($dh = opendir($dir)) {
    while (($file = readdir($dh)) !== false) {
      $fn  = basename($file);
      $fne = basename($file, $ext);
      if ($fne!="." && $fne!=".." && $fn == $fne.$ext) {
        $flist[] = $fne;
      }
    }
    closedir($dh);
  }
  return $flist;
}

function getThemeValue($var, $def="") {
  $themef = getParam("WGCAL_U_THEME", "default");
  @include_once("WGCAL/Themes/default.thm");
  @include_once("WGCAL/Themes/".$themef.".thm");
  $vars = get_object_vars($theme);
  if (isset($theme->$var)) return $theme->$var;
  return $def;
}

function setThemeValue() {
  global $action;
  $fsz = GetHttpVars("fonts", $action->getParam("WGCAL_U_FONTSZ", "normal"));
  @include_once("WGCAL/Themes/normal.fsz");
  if ($fsz!="normal") @include_once("WGCAL/Themes/".$fsz.".fsz");
  $themef = getParam("WGCAL_U_THEME", "default");
  @include_once("WGCAL/Themes/default.thm");
  @include_once("WGCAL/Themes/".$themef.".thm");
  $vars = get_object_vars($theme);
  foreach ($vars as $k => $v) $action->lay->set($k, $v);
  return $def;
}


function getCompColor($col) {
  $rcol = getParam("WGCAL_U_EVENTTEXTCOLOR");
  if ($rcol=="") {
    $hsl=srgb2hsl($col);
    $h = (1.0*$hsl[0]);
    $s = ((100.0*$hsl[1]) + 50.0 ) % 100.0 ;
    $l = ((100.0*$hsl[2]) + 50.0 ) % 100.0 ;
    $fcol = HSL2RGB($h, $s/100, $l/100);
    //   global $action; $action->log->info("couleur $col (".$hsl[0].",".$hsl[1].",".$hsl[2].") transformée ==> $fcol ($h,$s,$l)");
    return $fcol;
  } else {
    return getParam("WGCAL_U_EVENTTEXTCOLOR");
  }
}

function fcalGetIcon($key, $norm=true, $color="#000000") {
  
  global $action;
  
  $ricons = array( "CONFID" => array( "iconsrc" => "wm-confidential.gif", 
				      "iconmini" => "fcal-small-confidential.gif",
				      "icontitle" => _("icon text confidential event") ),
		   "VIS_CONFI" => array( "iconsrc" => "wm-confidential.gif", 
					 "iconmini" => "fcal-small-confidential.gif",
					"icontitle" => _("icon text visibility confidendial")),
		   "VIS_PRIV" => array( "iconsrc" => "wm-private.gif", 
					"iconmini" => "fcal-small-private.gif",
					"icontitle" => _("icon text visibility private") ),
		   "CAL_PRIVATE" => array( "iconsrc" => "wm-private.gif", 
					"iconmini" => "fcal-small-privatecalendar.gif",
					"icontitle" => _("icon text visibility private") ),
		   "VIS_GRP" => array( "iconsrc" => "wm-privgroup.gif", 
				      "iconmini" => "fcal-small-visgroup.gif",
				       "icontitle" => _("icon text visibility group") ),
		   "REPEAT" => array( "iconsrc" => "wm-icorepeat.gif", 
				      "iconmini" => "fcal-small-repeat.gif",
				      "icontitle" => _("icon text repeat event") ),
		   "REPEATEXCLUDE" => array( "iconsrc" => "wm-icorepeat.gif", 
				      "iconmini" => "fcal-small-repeatexclude.gif",
				      "icontitle" => _("icon text repeat event") ),
		   "ALARM" => array( "iconsrc" => "wm-alarm.gif", 
				     "iconmini" => "fcal-small-alarm.gif",
				     "icontitle" => _("icon text alarm") ),
		   "GROUP" => array( "iconsrc" => "wm-attendees.gif", 
				     "iconmini" => "fcal-small-attendees.gif",
				     "icontitle" => _("icon text with attendees") )
		   ); 
  $ik = ($norm?"iconsrc":"iconmini");
  return array( "code" => $key, 
                "text" => $ricons[$key]["title"], 
                "src"  => $action->getFilteredImageUrl($ricons[$key][$ik].":0,0,0|$color") );
}

function fcalLocalFrenchDateToUnixTs($fdate, $utc=false) {
  if (preg_match("/^(\d\d)\/(\d\d)\/(\d\d\d\d)\s?(\d\d)?:?(\d\d)?:?(\d\d)?\s?(\w+)?$/", $fdate,$r)) {   
    if ($utc) $ds = gmmktime($r[4], $r[5], $r[6], $r[2], $r[1], $r[3]);
    else $ds = mktime($r[4], $r[5], $r[6], $r[2], $r[1], $r[3]);
  } else {
    $ds = -1;
  }
  return $ds;
}

?>
