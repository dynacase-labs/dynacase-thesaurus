<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_calendar.php,v 1.50 2005/08/11 17:01:21 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("WGCAL/Lib.WGCal.php");
include_once("WGCAL/Lib.wTools.php");
include_once('FDL/popup_util.php');
include_once('WHAT/Lib.Common.php');

function wgcal_calendar(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $debug = GetHttpVars("debug", 0);

  // Check for standalone mode 
  $sm = (GetHttpVars("sm", 0) == 0 ? false : true);
  
  // Event search
  // qev =  query event
  // famref = family producer
  // ress = ressources

  $qev = GetHttpVars("qev", getIdFromName($dbaccess,"WG_AGENDA"));

  $famr = GetHttpVars("famref", $action->getParam("WGCAL_G_VFAM", "CALEVENT"));
  $ft = explode("|", $famr);
  $fti = array();
  foreach ($ft as $k => $v)     $fti[] = (is_numeric($v) ? $v : getIdFromName($dbaccess, $v));
  $idfamref = implode("|", $fti);
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
  
  // Init start time, view mode (month, week, ...)
  $vm = GetHttpVars("vm", "");
  if ($vm=="" || !is_int($vm)) $vm = $action->GetParam("WGCAL_U_DAYSVIEWED", 7);
  $dayperweek = $vm;
  if ($dayperweek==-1) redirect($action,"WGCAL","WGCAL_TEXTMONTH");
  $swe = $action->GetParam("WGCAL_U_VIEWWEEKEND", "yes");
  if ($swe!="yes") {
    $ndays = $dayperweek - 2;
  } else {
    $ndays = $dayperweek;
  }

  $ts = GetHttpVars("ts", 0);
  $stdate = $ts;
  if ($stdate == 0) $stdate = $action->GetParam("WGCAL_U_CALCURDATE", time());
  if (!$sm) $action->parent->param->set("WGCAL_U_CALCURDATE", $stdate, PARAM_USER.$action->user->id, $action->parent->id);
  $sdate = w_GetDayFromTs($stdate); 
  $firstWeekDay = w_GetFirstDayOfWeek($sdate);
  $edate = $firstWeekDay + ($ndays * SEC_PER_DAY) - 1;

  if ($debug==1) AddWarningMsg("Query = [$qev]   Producters = [$idfamref] Ressources = [$idres] Dates = [".ts2db($firstWeekDay, "Y-m-d H:i:s").",".ts2db($edate, "Y-m-d H:i:s")."]");

  $events = array();
  $dre=new Doc($dbaccess, $qev);
  $events = $dre->getEvents(ts2db($firstWeekDay, "Y-m-d H:i:s"), ts2db($edate, "Y-m-d H:i:s"));

  // Post process search results ------------------------------------------------------------------------------------
  $tout=array(); 
  $first = false;
  popupInit('calpopup',  array('editrv', 'deloccur', 'viewrv', 'deleterv',
                               'acceptrv', 'rejectrv', 'tbcrv', 'historyrv',
                               'cancelrv'));
  $showrefused = $action->getParam("WGCAL_U_DISPLAYREFUSED", 0);
  $rvfamid = getIdFromName($dbaccess, "CALEVENT");
  foreach ($events as $k=>$v) {
    $end = ($v["evfc_realenddate"] == "" ? $v["evt_enddate"] : $v["evfc_realenddate"]);
    $item = array( "ID" => $v["id"],
		   "START" => localFrenchDateToUnixTs($v["evt_begdate"]),
		   "TSSTART" => $v["evt_begdate"],
		   "END" => localFrenchDateToUnixTs($end), 
		   "IDP" =>  $v["evt_idinitiator"],
		   "IDC" =>  $v["evt_idcreator"] );
    $displayEvent = true;

    // Traitement de refus => spécifique à CALEVENT
    if ($v["evt_frominitiatorid"] == $rvfamid && !$nofilter) {

      $displayEvent = false;
      
      // Affichage
      // - si une ressource affiché est dedans et pas refusé
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

    if ($displayEvent) { 

      $n = new Doc($dbaccess, $v["id"]);  
      $item["RG"] = count($tout);
      $d = new Doc($dbaccess, $v["evt_idinitiator"]);
      $item["EvRCard"] = $d->viewDoc($d->defaultabstract);
      $item["EvPCard"] = $d->viewDoc($d->defaultview);
      
      PopupInvisible('calpopup',$item["RG"], 'acceptrv');
      PopupInvisible('calpopup',$item["RG"], 'rejectrv');
      PopupInvisible('calpopup',$item["RG"], 'tbcrv');
      PopupInactive('calpopup',$item["RG"], 'historyrv');
      PopupActive('calpopup',$item["RG"], 'viewrv');
      PopupInvisible('calpopup',$item["RG"], 'deloccur');
      PopupActive('calpopup',$item["RG"], 'cancelrv');
      PopupInactive('calpopup',$item["RG"], 'editrv');
      PopupInactive('calpopup',$item["RG"], 'deleterv');
      $action->lay->set("popupState",false);
      
      if ($action->user->fid == $v["evt_idcreator"]) {
	if ($v["evfc_repeatmode"] > 0) PopupActive('calpopup',$item["RG"], 'deloccur');
	PopupActive('calpopup',$item["RG"], 'editrv');
	PopupActive('calpopup',$item["RG"], 'deleterv');
	$item["EditCard"] = true;
      }	else {
 	$item["EditCard"] = false;
      }
      
      $withme = false;
      $attr = Doc::_val2array($v["evfc_listattid"]);
      $attrst = Doc::_val2array($v["evfc_listattst"]);
      if (count($attr)>1) {
	foreach ($attr as $ka => $va) {
	  if ($va==$action->user->fid) {
	    $withme = true;
	    $mystate = $attrst[$ka];
	  }
	}
      }
      
      $conf = $v["evfc_visibility"];
      $private = ((($v["evt_idcreator"] != $action->user->fid) && ($conf!=0)) ? true : false );
      if (!$private) PopupActive('calpopup',$item["RG"], 'historyrv');
      else PopupInactive('calpopup',$item["RG"], 'viewrv');
      
      if ($withme) {
        $action->lay->set("popupState",true);
        if ($mystate!=2) PopupActive('calpopup',$item["RG"], 'acceptrv');
        if ($mystate!=3) PopupActive('calpopup',$item["RG"], 'rejectrv');
        if ($mystate!=4) PopupActive('calpopup',$item["RG"], 'tbcrv');
      }
      
      $tout[] = $item;
    }
  }
  popupGen(count($tout));
  $action->lay->SetBlockData("SEP",array(array("zou")));// to see separator
    

  // Display results ------------------------------------------------------------------------------------

  $action->lay->set("sm", $sm);
  $action->lay->set("vm", $vm);
  $action->lay->set("ts", $ts);
  $action->lay->set("res", $res);

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef("WHAT/Layout/DHTMLapi.js");
  $action->parent->AddJsRef("WHAT/Layout/AnchorPosition.js");
  $action->parent->AddJsRef("WHAT/Layout/geometry.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_calendar.js");

  $action->lay->set("standAlone", $sm);

  $hcolsize = 5;
  $colsize = round((100 - $hcolsize) / $ndays);

  $cdate = w_GetDayFromTs(time());
  $pafter = $sdate + ($ndays * SEC_PER_DAY);
  $pbefore = $sdate - ($ndays * SEC_PER_DAY);

  $year  = strftime("%Y",$sdate);
  $month = strftime("%B",$sdate);
  $week  = strftime("%V",$sdate);
  $iday  = strftime("%u",$sdate);
  $day   = strftime("%d",$sdate);

  $hstart = $action->GetParam("WGCAL_U_STARTHOUR", 8);
  $hstop  = $action->GetParam("WGCAL_U_STOPHOUR", 20);
  $hdiv   = $action->GetParam("WGCAL_U_HOURDIV", 1);
  for ($h=0; $h<=3; $h++) {
    $tdiv[$h]["value"] = $h+1;
    $tdiv[$h]["descr"] = ($h==0?"1h":"1/".($h+1)."h");
    $tdiv[$h]["selected"] = ($hdiv==$h+1?"selected":"");
  }
  $action->lay->SetBlockData("CHHDIV", $tdiv);
  if ($hdiv>1) $hhight = $action->GetParam("WGCAL_U_HLINEHOURS",40) / ($hdiv - 1);
  else $hhight = $action->GetParam("WGCAL_U_HLINEHOURS",40);
  

  $action->lay->set("DIVSTART", "calareastart");
  $action->lay->set("DIVEND", "calareaend");
  
  $action->lay->set("colspan", $ndays+1 );
  $action->lay->set("week", $week);
  $action->lay->set("month", $month);
  $action->lay->set("year", $year);
  $action->lay->set("pafter", $pafter);
  $action->lay->set("pbefore", $pbefore);
  $action->lay->set("pcurrent", time());

  $action->lay->set("WEEKNUMBER", $week);
  $curday = -1;
  $tabdays = array(); $itd=0;
  for ($i=0; $i<$ndays; $i++) { 
    $tabdays[$i]["iday"] =  $i;
    $tabdays[$i]["days"] =  strftime("%s", $firstWeekDay+($i*SEC_PER_DAY));
    $tabdays[$i]["vstart"] =  $tabdays[$i]["days"] + (SEC_PER_HOUR*($hstart-1));
    $tabdays[$i]["vend"] =  $tabdays[$i]["days"] + (SEC_PER_HOUR*($hstop)); //+ (SEC_PER_HOUR*$hstop) -1;
    if ($cdate==$tabdays[$i]["days"]) {
      $class[$i] = "WGCAL_DayCur";
      $classh[$i] = "WGCAL_DayLineCur";
      $curday = $i; 
    } else if ($sdate==$tabdays[$i]["days"]) {
      $classh[$i] = "WGCAL_DayLineCur";
      $class[$i] = "WGCAL_Day";
    } else {
      $classh[$i] = "WGCAL_DayLine"; 
      if ($i==5||$i==6) $class[$i] = "WGCAL_DayWE";
      else $class[$i] = "WGCAL_Day";
    }
    $t[$i]["IDD"] = $i;
    $t[$i]["colsize"] = $colsize;
    $t[$i]["CSS"] = $classh[$i];
    $t[$i]["LABEL"] = w_strftime($firstWeekDay+($i*SEC_PER_DAY), WD_FMT_DAYLTEXT);
    $t[$i]["times"] = $tabdays[$i]["vstart"] ;
    $t[$i]["timee"] = $t[$i]["times"] +  SEC_PER_HOUR;
  }
  $action->lay->SetBlockData("DAYS_LINE", $t);
  
  $urlroot = $action->GetParam("CORE_STANDURL");
  $lcell = new Layout( "WGCAL/Layout/wgcal-cellcalendar.xml", $action );
  $nl = 0;
  for ($h=$hstart-1; $h<=($hstop+1); $h++) {
    if ($h<$hstart || $h>$hstop) $ndiv = 1;
    else $ndiv = $hdiv;
    $mdiv = round(SEC_PER_HOUR/$ndiv);
    for ($hd=0; $hd<$ndiv; $hd++) {
      $thr[$nl]["LID"] = $nl;
      $thr[$nl]["HLINEHOURS"] = $hhight;
      $thr[$nl]["HCLASS"] = "WGCAL_DayHours";
      if ($h==($hstart-1) || $h==$hstop+1) 
	$thr[$nl]["HOURR"] = "";
      else if ($hd==0) {
	$thr[$nl]["HOURR"] = ($h==($hstart-1)?"":$h)."H00";
	$thr[$nl]["HCLASS"] = "WGCAL_DayHours";
      } else {
	$thr[$nl]["HOURR"] = printhdiv(($h==($hstart-1)?"":$h), $ndiv,$hd);
	$thr[$nl]["HCLASS"] = "WGCAL_DayMin";
      }
      $tcell = array();
      $itc = 0;
      for ($id=0; $id<$ndays; $id++) {
	if ($id>6) $mo = $id;
	else $mo = $id % 7;
	$tcell[$itc]["cellref"] = 'D'.$id.'H'.$nl;
	$tcell[$itc]["colsize"] = $colsize;
	$tcell[$itc]["urlroot"] = $urlroot;
	$tcell[$itc]["times"] = $firstWeekDay + ($id*SEC_PER_DAY)+($h*SEC_PER_HOUR) + ($hd*$mdiv);
	$tcell[$itc]["timee"] = $tcell[$itc]["times"] + (($hd==0?1:$hd) * $mdiv);
	$tcell[$itc]["rtime"] = w_strftime($firstWeekDay+($id*SEC_PER_DAY), WD_FMT_DAYLTEXT);
	if ($h==($hstart-1) || $h==($hstop+1)) {
	  $tcell[$itc]["nh"] = 1;
	  $tcell[$itc]["rtime"] .= " "._("no hour");
	} else {
	  $tcell[$itc]["nh"] = 0;
	  $tcell[$itc]["rtime"] .= ", ".ts2db($tcell[$itc]["times"],"H:i")." - ";
	  $tcell[$itc]["rtime"] .= ts2db($tcell[$itc]["timee"],"H:i");
	}
	$tcell[$itc]["lref"] = "L".$nl;
	$tcell[$itc]["cref"] = "D".$id;
        if ($h<$hstart || $h>$hstop) $tcell[$itc]["cclass"] = "WGCAL_DayNoHours";
	else $tcell[$itc]["cclass"] = $class[$id];
	$tcell[$itc]["dayclass"] = $thr[$nl]["HCLASS"];
	$tcell[$itc]["hourclass"] = $classh[$id];
	$tcell[$itc]["cellcontent"] = "";
	$itc++;
      }
      $lcell->SetBlockData("CELLS", $tcell);
      $thr[$nl]["C_LINE"] =  $lcell->Gen();
      $nl++;
    }
  }

  $action->lay->SetBlockData("HOURS", $thr);
  $action->lay->SetBlockData("DAYS", $tabdays);
  
  $action->lay->set("DAYCOUNT", $ndays);
  $action->lay->set("HSTART", ($hstart - 1)); // Minutes
  $action->lay->set("HCOUNT", (($hstop - $hstart + 1) * $hdiv ) + 1 ); // Minutes
  $action->lay->set("HDIV", $hdiv); // Minutes
  $action->lay->set("YDURATION", (60/$hdiv) );
  $action->lay->set("IDSTART", "D0H0");
  $action->lay->set("IDSTOP", "D".($ndays-1)."H".($nl-1));
  $action->lay->set("ALTFIXED", $action->GetParam("WGCAL_U_ALTFIXED", "Float"));
  $action->lay->set("ALTTIMER", $action->GetParam("WGCAL_U_ALTTIMER", "500"));
  
  $action->lay->set("WGCAL_U_HLINETITLE", $action->GetParam("WGCAL_U_HLINETITLE", 20));
  $action->lay->set("WGCAL_U_HLINEHOURS", $action->GetParam("WGCAL_U_HLINEHOURS", 40));
  $action->lay->set("WGCAL_U_HCOLW", $action->GetParam("WGCAL_U_HCOLW", 20));

  $action->lay->SetBlockData("EVENTS", $tout);
  $action->lay->SetBlockData("EVENTSSC", $tout);

}


function printhdiv($h, $hdiv, $hd) {
  $sd = $h."H";
  $sh = "00";
  $sh = sprintf("%d",((60/$hdiv)*$hd));
  if (strlen($sh) == 1) $sh = "0".$sh;
  return $sd.$sh;
}

?>
