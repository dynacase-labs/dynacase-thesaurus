<?php
include_once("Class.Param.php");
include_once("FDL/freedom_util.php");
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

function w_dbdate2ts($dbtime) {
  return mktime(w_dbhou($dbtime), 
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
function w_strftime($ts, $fmt=0, $ucw=true) {
  global $action;

  switch ($fmt) {
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
 	$iday  = strftime("%u",$ts);
	$dt = 1-$iday;
        $tsfwd = $ts - (($iday-1) * SEC_PER_DAY);
	$dd = strftime("%d", $tsfwd);
 	$mm = strftime("%m", $tsfwd);
 	$yy = strftime("%Y", $tsfwd);
	$fwdt = gmmktime ( 0, 0, 0, $mm, $dd, $yy);
	return $fwdt;
}

function w_GetDayFromTs($ts) 
{
  if ($ts<=0) return false;
  $dd = strftime("%d", $ts);
  $mm = strftime("%m", $ts);
  $yy = strftime("%Y", $ts);
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



// ----------------------------------------------------
// Groups....
// ----------------------------------------------------
function wPrintGroups() {
  global $action;
  print_r2(wGetGroups());
}

function  wGetGroups() {
  global $action;
  $p = new Param($action->dbaccess, array("WGCAL_USEDGROUPS", PARAM_APP, $action->parent->GetIdFromName("WGCAL")));
  $tgroups = false;
  if ($p->val!="") {
    $tga = explode("|", $p->val);
    $tgroups = array();
    foreach ($tga as $kg => $vg) {
      if ($vg!="") $tgroups[$vg] = $vg;
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
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  $fid = ($fid==-1 ? $action->user->fid : $fid);

  // get wgcal used groups
  $wgcal_groups = wGetGroups();

  // get user groups
  $user = new Doc($dbaccess, $fid);
  $user_groups = $user->getTValue("us_idgroup");

  // compute user real groups (user groups used in Agenda)
  $u_rgroups = array();
  foreach ($user_groups as $kg => $vg) {
    if (isset($wgcal_groups[$vg])) {
      $u_rgroups[$vg]["gid"] = $vg;
      $u_rgroups[$vg]["sel"] = false;
    }
  }
  
  $tg = $user->getTValue("us_wgcal_gid");
  foreach ($tg as $kg => $vg) {
    if (isset($u_rgroups[$vg])) $u_rgroups[$vg]["sel"] = true;
  }

  return $u_rgroups;
}


// ----------------------------------------------------
// Others....
// ----------------------------------------------------
function wPbool($b) {
  return ($b?"true":"false");
}

function setToolsLayout(&$action, $tool="") {

  // Set initial visibility
  $all =  explode("|", $action->GetParam("WGCAL_U_TOOLSSTATE", ""));
  $state = array();
  foreach ($all as $k => $v) {
    $t = explode("%",$v);
    $state[$t[0]] = ($t[1]==0?0:1);
  }
  $s = (isset($state[$tool]) ? $state[$tool] : 1 );
  $vis = ($s ? "" : "none" );
  $action->lay->set( "v".$tool, $vis);
  $action->lay->set( "b".$tool, ($s==1? "wToolButtonUnselect":"wToolButtonSelect"));
  return;
}

function wGetRessDisplayed() {
  global $action;
  $r = array();
  $ir = 0;
  $cals = explode("|", $action->GetParam("WGCAL_U_RESSDISPLAYED", $action->user->id));
  while (list($k,$v) = each($cals)) {
    if ($v!="") {
      $tc = explode("%", $v);
      if ($tc[0] != "" && $tc[1] == 1) {
	$r[$ir]->id = $tc[0];
	$r[$ir]->color = $tc[2]; 
	$ir++;
      }
    }
  }
  return $r;
}

function wGetCategories() {
  global $action;
  $freedomdb = $action->getParam("FREEDOM_DB");
  $dt = new Doc($freedomdb, "CALEVENT");
  $ctg = $dt->GetAttribute("CALEV_CATEGORY");
  $enum = $ctg->getEnum();
  return $enum;
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
  usort($ar, wUSortCmp);
  return;
}

function wUSortCmp(&$a, &$b) {
  global $fsort;
  return (strcmp($a[$fsort], $b[$fsort]));
}
  


function MonAgenda() 
{
  global $action;
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $rq=getChildDoc($dbaccess,0,0,1,array("(agd_oid = ".$action->user->id." and agd_omain = 1)"),
		  $action->user->id, "LIST", "AGENDA");
  if (count($rq)>0)  {
    $home = $rq[0];
  } else {
    $home = NULL;
  }

  $mycalendar = _("My calendar");
  $calname = "UDCAL".$action->user->id;

  $mcal = new Doc($dbaccess, getIdFromName($dbaccess, $calname)); 
  if (!$mcal->isAffected()) {
    $mcal = createDoc($dbaccess,"DCALENDAR");
    if (!$mcal) $action->exitError(_("Can't create : ").$mycalendar);
    $mcal->setTitle($mycalendar. " (".ucwords(strtolower($action->user->firstname.' '.$action->user->lastname)).")");
    $mcal->owner = $action->user->id;
    $mcal->icon = 'mycal.gif';
    $mcal->name = $calname;
    $mcal->setValue("se_famid", getFamIdFromName($dbaccess, "EVENT"));
    $mcal->setValue("se_ols", array( "and", "and"));
    $mcal->setValue("se_attrids", array( "evt_idres", "evt_frominitiatorid"));
    $mcal->setValue("se_funcs", array( '~y', '~*' ));
    $mcal->setValue("se_keys", array( $action->user->fid, getFamIdFromName($dbaccess, "CALEVENT") ));
    $mcal->Add();
    $mcal->PostModify();
    $mcal->Modify();
    if ($home!=NULL) {
      $home->AddFile($mcal->id);
    }
  }

}


function wgcalGetRessourcesMatrix($ev) {
  global $action;
  
  $event = new Doc($action->getParam("FREEDOM_DB"), $ev);
  
  $tress  = $event->getTValue("CALEV_ATTID");
  $tresse = $event->getTValue("CALEV_ATTSTATE");
  $tressg = $event->getTValue("CALEV_ATTGROUP");

  $ressd = array();
  foreach ($tress as $k => $v) {
    if (!(isset($ressd[$v]) && $tressg[$k]==-1)) {
      $ressd[$v]["id"] = $id;
      $ressd[$v]["title"] = ucwords(strtolower($event->getTitle()));
      $ressd[$v]["state"] = $tresse[$k];
      $ressd[$v]["color"] = "white";
      $ressd[$v]["displayed"] = false;
      $ressd[$v]["group"] = $tressg[$k];
    }
  }

  $cals = explode("|", $action->GetParam("WGCAL_U_RESSDISPLAYED", $action->user->id));
  while (list($k,$v) = each($cals)) {
    if ($v!="") {
      $tc = explode("%", $v);
      if ($tc[0] != "" && isset($ressd[$tc[0]])) {
	$ressd[$tc[0]]["displayed"] = ($tc[1] == 1 ? true : false );
	$ressd[$tc[0]]["color"] = $tc[2];
      }
    }
  }
  return $ressd;
}



function wGetEvents($d1, $d2, $explode=true, $filter=array()) {

  global $action;

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $qev = GetHttpVars("qev", getIdFromName($dbaccess,"WG_AGENDA"));

  $famr = GetHttpVars("famref", $action->getParam("WGCAL_G_VFAM", "CALEVENT"));
  $ft = explode("|", $famr);
  $fti = array();
//   foreach ($ft as $k => $v)     $fti[] = (is_numeric($v) ? $v : getIdFromName($dbaccess, $v));
//   $idfamref = implode("|", $fti);
  $idfamref = "1010";
  setHttpVar("idfamref", $idfamref);

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
  setHttpVar("idres",$idres);
  
  $sdebug = "Query = [$qev]\n\t- Producters = [$idfamref]\n\t- Ressources = [$idres]\n\t- Dates = [".$d1.",".$d2."]\n";

  $events = array();
  $dre=new Doc($dbaccess, $qev);
  $events = $dre->getEvents($d1, $d2, $explode, $filter);

  // Post process search results ------------------------------------------------------------------------------------
  $tout=array(); 
  $first = false;
  $showrefused = $action->getParam("WGCAL_U_DISPLAYREFUSED", 0);
  $rvfamid = getIdFromName($dbaccess, "CALEVENT");
  foreach ($events as $k=>$v) {
    $end = ($v["evfc_realenddate"] == "" ? $v["evt_enddate"] : $v["evfc_realenddate"]);
  $sdebug .= "[".$v["evt_frominitiatorid"]."::".$v["evt_idinitiator"]."] title=[".$v["evt_title"]."]";
    $item = array( "ID" => $v["id"],
		   "TSSTART" => $v["evt_begdate"],
		   "TSEND" => $end,
		   "START" => localFrenchDateToUnixTs($v["evt_begdate"]),
		   "END" => localFrenchDateToUnixTs($end), 
		   "IDP" =>  $v["evt_idinitiator"],
		   "IDC" =>  $v["evt_idcreator"] );
    $displayEvent = true;

    // Traitement de refus => spécifique à CALEVENT
    if ($v["evt_frominitiatorid"] == $rvfamid) {

      $displayEvent = false;
      
      // Affichage
      // - si une ressource affiché est dedans et pas refusé
      $attlist  = Doc::_val2array($v["evfc_listattid"]);
      $attrstat = Doc::_val2array($v["evfc_listattst"]);
      $attinfo = array();
      foreach ($attlist as $kat => $vat) {
	$attinfo[$vat]["status"] = $attrstat[$kat];
	$attinfo[$vat]["display"] = isset($tr[$vat]);
      }
      
      foreach ($attinfo as $kat => $vat) {
	
	if ($vat["display"]) {
	  if ($action->user->fid!=$kat) {
	    if ($vat["status"]!=EVST_REJECT) {
	      $displayEvent = true;
	    }
	  } else {
	    if ($vat["status"]!=EVST_REJECT || $showrefused==1) {
	      $displayEvent = true;
	    }
	  }
	}
      }
    }

  $sdebug .= " display=[".($displayEvent?"true":"false")."]\n";
    if ($displayEvent) { 
      $item["RG"] = count($tout);
      $tout[] = $item;
    }
  } 
    AddWarningMsg($sdebug);
   
  return $tout;
}

?>
