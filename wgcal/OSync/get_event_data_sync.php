<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: get_event_data_sync.php,v 1.1 2005/04/18 15:39:30 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once("FDL/Lib.Dir.php");
include_once("FDL/freedom_util.php");
include_once("WGCAL/Lib.WGCal.php");
include_once("WGCAL/Lib.WgcalSync.php");
include_once("WGCAL/Class.WSyncDate.php");
include_once("WGCAL/Class.WSyncIds.php");

$ctx = WSyncAuthent();

$debug = (GetHttpVars("debug", 0)==1?true:false);
$fd = GetHttpVars("fd", 0);

if (!$debug) header ("Content-Type: text/plain");
else {
  echo '<html><head><style type="text/css">.out { color:white; border:1px solid grey; background:black } pre { color:red; border:1px solid grey; background:#efefef }</style><body>';
  echo "<h4>Debug mode on</h4>";
}

$ds = GetHttpVars("debut_sync_date", date2db(time(), true));
if ($ds=="") return;
$start_date = WSyncMSdate2Db($ds);

$db = WSyncGetDataDb($ctx);
$dbadm = WSyncGetAdminDb($ctx);


$famev = getIdFromName($db, "CALEVENT");
$user = $ctx->user->fid;

$filter = array();
$filter[] = "(calev_ownerid = $user) OR (calev_attid ~* '$user')";
if ($fd==0) $filter[] = "calev_start >= '".$start_date." 00:00:00'";

if ($debug)  {
 print "<pre>";
 echo "Db data is [$db]</br>";
 echo "Db admin is [$dbadmin]</br>";
 echo "User   : w:".$ctx->user->id."/f:".$user."<br>";
 echo "Filter : "; print_r2($filter);
 print "</pre>";
}

$trv = GetChildDoc($db, 0, 0, "ALL", $filter, $ctx->user->id, 
		  "TABLE", $famev, false, "calev_start", true);

if ($debug) print "<pre>";
  WSyncSend($debug, count($trv));
  WSyncSend($debug, "NOERROR");
if ($debug) print "</pre>";

foreach ($trv as $krv => $vrv) {
  if ($debug) print '<pre class="out">';
  WSyncSend($debug, $vrv["id"]);
  WSyncSend($debug, ($vrv["calev_ownerid"]==$user?"1":"0"));
  WSyncSend($debug, $vrv["calev_owner"]);
  WSyncSend($debug,  "<i>".utf8_encode($vrv["calev_evtitle"])."</i>");
  WSyncSend($debug, WSyncDbDate2Outlook($vrv["calev_start"], ($vrv["calev_timetype"]==1?false:true)));

  WSyncSend($debug, WSyncTs2Outlook($vrv["revdate"]));
  $dur = (dbdate2ts($vrv["calev_end"]) - dbdate2ts($vrv["calev_start"])) / 60;
  WSyncSend($debug, $dur);
  WSyncSend($debug, "0"); // Priority !!!
  WSyncSend($debug, ($vrv["calev_repeatmode"]==0?"E":"M"));
  WSyncSend($debug,($vrv["calev_visibility"]==0 && $vrv["calev_evcalendarid"]==-1?"P":"R"));
  WSyncSend($debug, "<!!DESCDEB>". utf8_encode($vrv["calev_note"]) . "<!!DESCFIN>");
  
  $ids = new WSyncIds($dbadm, array($user, $vrv["id"]));
  if (!$ids->isAffected() || !isset($ids->outlook_id) || $ids->outlook_id=="") WSyncSend($ctx, "SANS \n");
  else WSyncSend($debug, $ids->outlook_id);
  
  if ($vrv["calev_repeatmode"]>0) {
    switch ($vrv["calev_repeatmode"]) {
    case 2:  WSyncSend($debug, "weekly"); break;
    case 3:  WSyncSend($debug, "monthly"); break;
    case 4:  WSyncSend($debug, "yearly"); break;
    default:  WSyncSend($debug, "daily");
    }
    WSyncSend($debug, $vrv["calev_frequency"]);
    WSyncSend($debug,  "days");
    $td = explode("\n", $vrv["calev_excludedate"]);
      foreach ($td as $kd => $vd) WSyncSend($debug, $vd);
  }
  if ($debug) print "</pre>";
}
if ($debug)  echo "</body></html>";

?>
