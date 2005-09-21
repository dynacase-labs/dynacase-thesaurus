<?php

/**
 * Looks for rendez-vous alert !
 *
 * @author Anakeen 2004
 * @version $Id: wgcal_alert.php,v 1.4 2005/09/21 16:44:31 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
include_once("WGCAL/Lib.wTools.php");
function wgcal_alert(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $start = GetHttpVars("start", time());
  $end = $start + (5 * 24 * 3600);

  $ts = w_datets2db($start);
  $te = w_datets2db($end);
  setHttpVar("ress", "x");
  $events = wGetEvents($ts, $te, true, array("evfc_alarm=1 and evfc_alarmtime > '$ts' and evfc_alarmtime < '$te'"), "EVENT_FROM_CAL");
  
  if (count($events)==0) echo "Pas de résultats!";
  foreach ($events as $k=>$v) 
    {
      $trv = getTDoc($dbaccess, $v["IDP"]);
      echo $trv["title"]." (".$trv["id"].") ==> ".$v["evfc_alarmtime"]."<br>";
    }
}
?>
