<?php

function db_sec($dbtime) { return substr($dbtime,17,2); }
function db_min($dbtime) { return substr($dbtime,14,2); }
function db_hou($dbtime) { return substr($dbtime,11,2); }
function db_day($dbtime) { return substr($dbtime,0,2); }
function db_mon($dbtime) { return substr($dbtime,3,2); }
function db_yea($dbtime) { return substr($dbtime,6,4); }

function dbdate2ts($dbtime) {
  return mktime(db_hou($dbtime), db_min($dbtime) , db_sec($dbtime), db_mon($dbtime), db_day($dbtime), db_yea($dbtime));
}

function ts2dbdate($d) {
  return date("d/m/Y H:i:s", $d);
}


function mStrftime($ts) {
  $locz = array("C", "fr_FR");
  $fmt = "%A (%a %u) %d (%j %eième) %B (%b %m) week %V/%W %Y %Hh%Mm%Ss %Z"; 
  echo "  -- mStrftime() : ".strftime($fmt,$ts)."     <br>\n";
  foreach ($locz as $kz => $z)  {
    $x = setlocale(LC_TIME, "$z");
    echo "  -- mStrftime($x) : ".strftime($fmt,$ts)."     <br>\n";
  }
}

echo "<html><head></head><body>";

/// jj/mm/yyyy hh:mm:ss CEST
$d = array( "01/01/2000 00:00:00 CEST", 
	    "14/07/2005 12:00:00 CEST",
	    "01/01/2000 23:59:59 CEST");
foreach ($d as $k => $v) {
  $ts = dbdate2ts($v);
  echo "Psql date = [".$v."] ts = ".$ts." rets = ".ts2dbdate($ts)."     <br>\n";
  mStrftime($ts);
 echo '
<script type="text/javascript">
   var thedate = new Date('.($ts*1000).');
   document.write(\'<div style="background:red">['.$v.'::'.$ts.' => date \'+thedate.toString()+\'::\'+(thedate.getTime()/1000)+\'</div>\');
</script>';
 echo "</body></html>";

}
?>
