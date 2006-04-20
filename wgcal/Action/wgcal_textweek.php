<?php


function wgcal_textweek(&$action) {

  global $_SERVER;
  $hsep = "&nbsp;-&nbsp;";

  include_once("WGCAL/Lib.wTools.php");
  include_once("WGCAL/Lib.Agenda.php");
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $action->lay->set("dayheight", "290px");
  $action->lay->set("daywidth", "50%");
  $action->lay->set("server", $_SERVER["HTTP_HOST"]);

  $week = GetHttpVars("week", 0);
  if ($week>0) $ctime = $week;
  else $ctime = $action->GetParam("WGCAL_U_CALCURDATE", time());
  $action->lay->set("tweek", $ctime);
  $ress = GetHttpVars("ress", $action->user->fid);
  $action->lay->set("ress", $ress);

  $firstday = $ctime - ((strftime("%u",$ctime)-1) * (3600*24));
  $lastday = $firstday + (6*3600*24);

  $f_day = strftime("%d", $firstday);
  $l_day = strftime("%d", $lastday);

  // Search for delegation....
  // -------------------------
  $action->lay->set("mforuser", false);
  $action->lay->set("owner", $action->user->lastname." ".$action->user->firstname);
  $tdusers = array();
  $dcal = myDelegation();
  if (count($dcal)>0) {
    $action->lay->set("mforuser", true);
    $tdusers[] = array( "fid" => $action->user->fid, 
			"name" => ucwords(strtolower($action->user->lastname." ".$action->user->firstname)), 
			"selected" => ($ownerid==$ress ? "selected" : ""));
    foreach ($dcal as $k => $v) {
      if ($v!="") {
	$dcaluser = getTDoc($dbaccess, $v["agd_oid"]);
	$tdusers[] = array( "fid" => $dcaluser["id"], 
			    "name" => ucwords(strtolower($dcaluser["title"])),
			    "selected" => ($ress==$dcaluser["id"] ? "selected" : ""));
      }
    }
  }
  $action->lay->setBlockData("foruser", $tdusers);
    

  // prepare header & footer informations
  // ------------------------------------
  $ltext = "";
  $f_tday = ucfirst(strftime("%A %d", $firstday));
  $l_tday = ucfirst(strftime("%A %d", $lastday));
  $f_month = ucfirst(strftime("%B", $firstday));
  $l_month = ucfirst(strftime("%B", $lastday));
  $f_nmonth = ucfirst(strftime("%m", $firstday));
  $l_nmonth = ucfirst(strftime("%m", $lastday));
  $f_year = strftime("%Y", $firstday);
  $l_year = strftime("%Y", $lastday);
  if (strcmp($f_month,$l_month)!=0) {
    if ($f_year!=$l_year)  $ltext = $f_tday." ".$f_month." ".$f_year." - ".$l_tday." ".$l_month." ".$l_year;
    else $ltext = $f_tday." ".$f_month." - ".$l_tday." ".$l_month.", ".$l_year;
  } else {
    $ltext = $f_tday." - ".$l_tday.", ".$f_month." ".$l_year;
  }
  
  $action->lay->set("week", strftime("%V",$firstday));
  $action->lay->set("month", $ltext);
  $action->lay->set("printdate", strftime("%A %d %B %Y %H:%M", time()));

  // next and previous week navigation
  $action->lay->set("curweek", time());
  $action->lay->set("nextweek", $firstday + (24*3600*7));
  $action->lay->set("lnextweek", strftime("%V",$firstday + (24*3600*7)));
  $action->lay->set("prevweek", $firstday - (24*3600*7));
  $action->lay->set("lprevweek", strftime("%V",$firstday - (24*3600*7)));
  

  // search events
  $catg = wGetCategories();
  $evlay = new Layout("WGCAL/Layout/wgcal_textweek_event.xml", $action );
  $daysev = array();
  setHttpVar("ress",$ress);
  $d1 = "".$f_year."-".$f_nmonth."-".$f_day." 00:00:00";
  $d2 = "".$l_year."-".$l_nmonth."-".$l_day." 23:59:59";
  $tevents = wGetEvents($d1, $d2);
  foreach ($tevents as $k => $v) {

     $startday = gmstrftime("%u", $v["start"]) - 1;
     $endday = gmstrftime("%u", $v["end"]) - 1;
     for ($iday=intval($startday); $iday<=intval($endday); $iday++) {
      $lstart = substr($v["evt_begdate"],11,5);
      $end = ($v["evfc_realenddate"] == "" ? $v["evt_enddate"] : $v["evfc_realenddate"]);
      $lend = substr($end,11,5);
      if ($lstart==$lend && $lend=="00:00") {
	$hours = "("._("no hour").")";
	$p = 0;
      } else if ($lstart=="00:00" && $lend=="23:59") {
	$hours = "("._("all the day _ short").")";
	$p = 1;
      } else {
	if (intval($startday)==intval($endday)) {
	  $hours = $lstart.$hsep.$lend;
	  $p = 4;
	} else {
	  if ($iday==intval($startday)) {
	    $p = 4;
	    $hours = $lstart."$hsep..."; //" 24:00";
	  } else if ($iday==intval($endday)) {
	    $hours = "...$hsep".$lend; // "00:00 ".$lend;
	    $p = 2;
	  } else {
	    $p = 1;
 	    $hours = "("._("all the day _ short").")";
	  }
	}
      }
      $evlay->setBlockData("icons", null);
      if ($v["icons"]!="") {
	$it = explode(",", $v["icons"]);
	if (count($it)>0) {
	  $itt = array();
	  foreach ($it as $ki => $vi) $itt[]["iconsrc"] = str_replace("'","",$vi);
 	  $evlay->setBlockData("icons", $itt);
	}
      }
      $evlay->set("hours", $hours);
      $evlay->set("title", $v["evt_title"]);
      $evlay->set("vCategorie", false);
      if ($v["evt_code"]>0) {
	$catg = wGetCategories();
	$evlay->set("Categorie", $catg[$v["evt_code"]]["label"]);
	$evlay->set("vCategorie", true);
      }
      $evlay->set("vInvite", false);
      if ($action->user->fid!=$v["evt_idcreator"]) {
	$dt = getTDoc($dbaccess, $v["evt_idcreator"]);
	$evlay->set("Invite", $dt["title"]);
	$evlay->set("vInvite", true);
      }
      
      $evlay->set("vLieu", false);
      $evlay->set("vNote", false);
    
      $pdoc = getTDoc($dbaccess, $v["evt_idinitiator"]);
      if ($pdoc["calev_location"]!="") {
	$evlay->set("Lieu", $pdoc["calev_location"]);
	$evlay->set("vLieu", true);
      }
      
      if ($pdoc["calev_evnote"]!="") {
	$evlay->set("note", $pdoc["calev_evnote"]);
	$evlay->set("vNote", true);
      }
      
      $daysev[$iday][] = array( "p" => $p, "hours" => $hours, "eventsdesc" => $evlay->gen() );
     }
  }
      
  // set week view
  $dayl = array( "monday", "tuesday", "wenesday", "thursday", "friday", "saturday", "sunday" );
  for ($day=0; $day<7; $day++) {
    // sort by date....
    $action->lay->set($dayl[$day]."_num", strftime("%d", $firstday + ($day*3600*24)));
    $action->lay->set($dayl[$day]."_month", strftime("%B", $firstday + ($day*3600*24)));
    if (count($daysev[$day])>0) {
      usort($daysev[$day], cmpRv);
      $action->lay->setBlockData("b_".$dayl[$day], $daysev[$day]);
      $action->lay->set("s_".$dayl[$day], true);
    } else{
      $action->lay->set("s_".$dayl[$day], false);
    }
  }

}

function cmpRv($e1, $e2) {
  if ($e1["p"]==$e2["p"] && $e2["p"]>3) return strcmp($e1["hours"], $e2["hours"]);
  return $e1["p"] > $e2["p"];
}
?>