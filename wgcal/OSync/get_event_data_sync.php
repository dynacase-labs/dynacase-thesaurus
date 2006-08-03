<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: get_event_data_sync.php,v 1.9 2006/08/03 09:17:17 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once("FDL/Lib.Dir.php");
include_once("FDL/Class.Doc.php");
include_once("FDL/freedom_util.php");
include_once("WGCAL/Lib.WGCal.php");
include_once("Lib.WgcalSync.php");
include_once("Class.WSyncDate.php");
include_once("Class.WSyncIds.php");
include_once("WGCAL/WGCAL_external.php");

$action = WSyncAuthent();

$SyncDebug = (GetHttpVars("debug", 0)==1?true:false);
$fd = GetHttpVars("fd", 0);

if (!$SyncDebug) header ("Content-Type: text/plain");
else {
  echo '<html><head><style type="text/css">.out { color:white; border:1px solid grey; background:black } pre { color:red; border:1px solid grey; background:#efefef }</style><body>';
  echo "<h4>Debug mode on</h4>";
}

$ds = GetHttpVars("debut_sync_date", w_datets2db(time(), true));
if ($ds=="") return;
$start_date = WSyncMSdate2Db($ds);

$db = WSyncGetDataDb();
$dbadm = WSyncGetAdminDb();


$famev = getIdFromName($db, "CALEVENT");
$user = $action->parent->user->fid;

$filter = array();
$filter[] = "(calev_ownerid = $user) OR (calev_attid ~* '$user')";
if ($fd==0) $filter[] = "calev_start >= '".$start_date." 00:00:00'";

if ($SyncDebug)  {
 print "<pre>";
 echo "Db data is [$db]</br>";
 echo "Db admin is [$dbadmin]</br>";
 echo "User   : w:".$action->parent->user->id."/f:".$user."<br>";
 echo "Filter : "; print_r2($filter);
 print "</pre>";
}

$trv = GetChildDoc($db, 0, 0, "ALL", $filter, -1, 
		  "TABLE", $famev, false, "calev_start", true);

if ($SyncDebug) print "<pre>";
  WSyncSend($SyncDebug, "RV Count", count($trv));
  WSyncSend($SyncDebug, "Op. Status", "NOERROR");
if ($SyncDebug) print "</pre>";

$irv = 0;
foreach ($trv as $krv => $vrv) {
  if ($SyncDebug) print '<pre class="out">';
  WSyncSend($SyncDebug, "RV[".$irv++."] id", $vrv["id"]);
  WSyncSend($SyncDebug, "Owner is connected", ($vrv["calev_ownerid"]==$user?"0":"1"));
  WSyncSend($SyncDebug, "User login", $action->parent->user->login);
  WSyncSend($SyncDebug, "Title", "<I>\n".utf8_encode($vrv["calev_evtitle"])."\n</I>");
//   WSyncSend($SyncDebug, "Start", WSyncDbDate2Outlook($vrv["calev_start"], ($vrv["calev_timetype"]==1?false:true)));
  WSyncSend($SyncDebug, "Start", WSyncDbDate2Outlook($vrv["calev_start"]));

  WSyncSend($SyncDebug, "Rev date", WSyncTs2Outlook($vrv["revdate"]));

  $dur = (dbdate2ts($vrv["calev_end"]) - dbdate2ts($vrv["calev_start"])) / 60;
  if ($vrv["calev_timetype"]==1 || $vrv["calev_timetype"]==2) $dur = 1440;

  WSyncSend($SyncDebug, "Duration", $dur);
  WSyncSend($SyncDebug, "Priority", "0"); // Priority !!!
  WSyncSend($SyncDebug, "Repeat mode", ($vrv["calev_repeatmode"]==0?"E":"M"));
  WSyncSend($SyncDebug, "Public(P)/Private(R)", ($vrv["calev_visibility"]==0?"P":"R"));

  $astate = Doc::_val2array($vrv["calev_attstate"]);
  $atitle = Doc::_val2array($vrv["calev_atttitle"]);
  $aid = Doc::_val2array($vrv["calev_attid"]);
  $agrp = Doc::_val2array($vrv["calev_attgroup"]);
  $attlist = "";
  foreach ($aid as $kai => $vai) {
//     echo "$vai ".$action->parent->user->fid." ".$agrp[$kai]." ".$atitle[$kai]."\n";
    if ($vai!=$action->parent->user->fid && $agrp[$kai]==-1) $attlist .= "   | ".$atitle[$kai]." (".WGCalGetLabelState($astate[$kai]).")\n";
  }
  if ($attlist!="") $attlist = _("Attendees list")." : \n".$attlist;
  WSyncSend($SyncDebug, "Description", "<!!DESCDEB>\n". utf8_encode($vrv["calev_evnote"]) . "\n".$attlist."<!!DESCFIN>");
  
  $ids = new WSyncIds($dbadm, array($user, $vrv["id"]));
  if (!$ids->isAffected() || !isset($ids->outlook_id) || $ids->outlook_id=="") WSyncSend($SyncDebug, "Outlooke id", "SANS ");
  else WSyncSend($SyncDebug, "Outlook id", $ids->outlook_id);
  
  if ($vrv["calev_repeatmode"]>0) {
    $untildate = "";
    $dayls = array( "n", "n", "n", "n", "n", "n", "n");
    switch ($vrv["calev_repeatmode"]) {
    case 2: // Weekly  
      WSyncSend($SyncDebug, "Repeat period", "weekly");
      for ($i=0; $i<6; $i++) {
        if (($vrv["calev_repeatweekday"] & pow(2,$i)) ==  pow(2,$i)) $dayls[($i==6?0:$i+1)] = "y";
      }
      break;
    case 3: // Monthly
      if ($vrv["calev_repeatmonth"]==1) $mrep = "monthlyByDay";
      else $mrep = "monthlyByDate";
      WSyncSend($SyncDebug, "Repeat period", $mrep); 
      break;
    case 4:  WSyncSend($SyncDebug, "Repeat period", "yearly"); break;
    default: WSyncSend($SyncDebug, "Repeat period", "daily");
    }
    if ($vrv["calev_repeatuntil"]>0 && $vrv["calev_repeatuntildate"]!="") $untildate = WSyncDbDate2Outlook($vrv["calev_repeatuntildate"], false);
    WSyncSend($SyncDebug, "Repeat until", $untildate);
    WSyncSend($SyncDebug, "Repeat frequency", ($vrv["calev_frequency"]==""?1:$vrv["calev_frequency"]));
    $dlt = "";
    foreach ($dayls as $kdl => $vdl) $dlt .= $vdl;
    WSyncSend($SyncDebug, "Repeat days", $dlt);
    $td = Doc::_val2array($vrv["calev_excludedate"]);
    $ied=0;
    $tied = array();
    if (count($td)>0) foreach ($td as $kd => $vd) {
      if ($vd!="") {
	$tied[$ied++] = substr($vd,0,10);
      }
    }
    WSyncSend($SyncDebug, "Repeat exlude days count", $ied);
    if ($ied>0) {
      foreach ($tied as $ked => $ved) {
	WSyncSend($SyncDebug, "Repeat exlude day [$ked]", $ved);
      }
    }
  }
  if ($SyncDebug) print "</pre>";
}
if ($SyncDebug)  echo "</body></html>";

?>
