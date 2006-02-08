<?php


function wgcal_textweek(&$action) {

  include_once("WGCAL/Lib.wTools.php");
  include_once("WGCAL/Lib.Agenda.php");
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $action->lay->set("dayheight", "300");
  $action->lay->set("daywidth", "180");

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
  // sort by date....
  usort($tevents, cmpRv);
  
  foreach ($tevents as $k => $v) {

    $nday = strftime("%u", $v["START"]) - 1;
    $rv = new_Doc($dbaccess, $v["IDP"]);
    $lstart = substr($rv->getValue("calev_start"),11,5);
    $lend = substr($rv->getValue("calev_end"),11,5);
    switch($rv->getValue("calev_timetype",0)) {
    case 1: 
      $hours = "("._("no hour").")"; 
      break;
    case 2: 
      $hours = "("._("all the day").")"; 
      break;
    default:
      $hours = $lstart." - ".$lend;
    }

    
    $evlay->set("hours", $hours);
    $evlay->set("text", $rv->getValue("calev_evtitle"));

    $evlay->set("Categorie", $catg[$rv->getValue("calev_category")]["label"]);
    $evlay->set("vCategorie", ($rv->getValue("calev_category")>0?true:false));
    
    $evlay->set("Lieu", $rv->getValue("calev_location"));
    $evlay->set("vLieu", ($rv->getValue("calev_location")==""?false:true));

    $evlay->set("note", $rv->getValue("calev_evnote"));
    $evlay->set("vNote", ($rv->getValue("calev_evnote")==""?false:true));

    $evlay->set("vInvite", false);
    if ($rv->getValue("calev_ownerid") != $ress) {
      $evlay->set("Invite", $rv->getValue("calev_owner"));
      $evlay->set("vInvite", true);
    }

    $daysev[$nday][] = array( "hours" => $hours, 
			      "eventsdesc" => $evlay->gen() );
  }

  // set week view
  $dayl = array( "monday", "tuesday", "wenesday", "thursday", "friday", "saturday", "sunday" );
  for ($day=0; $day<7; $day++) {
    $action->lay->set($dayl[$day]."_num", strftime("%d", $firstday + ($day*3600*24)));
    $action->lay->setBlockData("b_".$dayl[$day], $daysev[$day]);
  }

}

function cmpRv($e1, $e2) {
  return $e1["START"] > $e2["START"];
}
?>