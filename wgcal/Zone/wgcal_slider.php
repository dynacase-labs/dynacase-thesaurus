<?php 

function wgcal_slider(&$action) {

  $vweek = GetHttpVars("vweek", 12); // viewed week
  $bweek = GetHttpVars("bweek", 3); // display 2 week before current
  $ddate = GetHttpVars("sliddate", time()); // Timestamp for current disply date
  $url = GetHttpVars("slidurl", ""); // Url for cell click action
  $title = GetHttpVars("slidtitle", ""); // Slidder title
 
  $c1 = "slid_oddm";
  $c2 = "slid_evenm";
  
  $wclass = "slid_week";
  $curwclass = "slid_curweek";

  $today = time();

  $strtoday = gmdate("Yz", $today);
  $strddate = gmdate("Yz", $ddate);
  $fdts = $ddate - ($bweek*7*24*3600);
  $weeks = array();
  $weeks[] = array( "stitle" => "<img src=\"".$action->getImageUrl("wm-cperiod.gif")."\">",
                    "title"  => "Semaine actuelle",
                    "class"  => $wclass,
                    "url"    => str_replace("%TS%", $today, $url),
		    "week"   => 0,
                    "showm" => false,
                    "cweek" => true,
                    );
  for ($iw=0; $iw<$vweek; $iw++) {
    $cw = gmdate("W",($fdts+($iw*6*24*3600)));
    $d1 = w_GetFirstDayOfWeek(($fdts+($iw*6*24*3600)));
    $d2 = $d1 + 6*24*3600;
    $days = array();
    $cwclass = $wclass;
    for ($id=0; $id<7; $id++) {
      $cm = gmdate("n", ($d1 + ($id*24*3600)));
      $days[] = array( "style" => ($strtoday==gmdate("Yz",($d1 + ($id*24*3600))) ? "slid_cday" : ($cm&1? $c1: $c2))) ;
      $cwclass .= ($strddate==gmdate("Yz",($d1 + ($id*24*3600))) ? " ".$curwclass : "" );
    }
    $action->lay->setBlockData("SLDAY$iw", $days);
    $weeks[] = array( "stitle" => "s$cw",
		      "title" => "Du ".gmstrftime("%d %B %Y",$d1)." au ".gmstrftime("%d %B %Y",$d2),
		      "class" => $cwclass,
		      "week" => $iw,
		      "url" => str_replace("%TS%", $d1, $url),
                      "showm" => true,
                      "cweek" => false,
		      );
    
  } 
  $action->lay->setBlockData("SLWEEK", $weeks);
  $action->lay->set("Swidth", floor(90/$vweek));
  $action->lay->set("Stitle", $title);

  return;
}

?>
