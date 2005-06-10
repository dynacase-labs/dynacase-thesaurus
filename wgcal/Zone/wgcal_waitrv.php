<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_waitrv.php,v 1.1 2005/06/10 05:52:22 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

include_once("FDL/Class.Doc.php");
include_once('FDL/Lib.Dir.php');
include_once("WGCAL/Lib.WGCal.php");
include_once("osync/Lib.WgcalSync.php");
include_once("EXTERNALS/WGCAL_external.php");

function wgcal_waitrv(&$action) {
  $rvtextl = 20;
  $wrv = array();
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $today = date2db(time()-(24*3600*7), false)." 00:00:00";
  $filter[] = "(calev_start > '".$today."' ) AND (calev_attid ~* '".$action->user->fid."')";
  $rdoc = GetChildDoc($dbaccess, 0, 0, "ALL", $filter, 
		      $action->user->id, "TABLE", getIdFromName($dbaccess,"CALEVENT"));
  $doc = new Doc($action->GetParam("FREEDOM_DB"));
  foreach ($rdoc as $k => $v)  {
    $state = -1;
    $attid = Doc::_val2array($v["calev_attid"]);
    $attst = Doc::_val2array($v["calev_attstate"]);
   foreach ($attid as $ka => $va) {
      if ($va==$action->user->fid && ($attst[$ka]==EVST_NEW||$attst[$ka]==EVST_READ||$attst[$ka]==EVST_TBC)) $state = $attst[$ka]; 
    }
    if ($state != -1) {
      $wrv[$irv] = $v;
      $label = WGCalGetLabelState($state); 
      $wrv[$irv]["wrvfontstyle"] = ""; 
      $wrv[$irv]["wrvcolor"] = WGCalGetColorState($state); 
      //if (strlen($v["calev_evtitle"])>$rvtextl) $wrv[$irv]["calev_evtitle"] = addslashes(substr($v["calev_evtitle"],0,$rvtextl)."...");
      $wrv[$irv]["calev_evtitle"] = addslashes($v["calev_evtitle"]);
      $wrv[$irv]["wrvfulldescr"] = "[".$label."] " 
	. substr($v["calev_start"],0,16)." : ".$v["calev_evtitle"]." (".$v["calev_owner"].")";
      $wrv[$irv]["wrvicon"] = $doc->GetIcon($v["icon"]);
      $wrv[$irv]["calev_start"] = substr($v["calev_start"],0,5);
      $irv++;
    }
  }
  $action->lay->set("WAITRV", (count($wrv)>0 ? true : false));
  $action->lay->set("RVCOUNT", count($wrv));
  $action->lay->setBlockData("RVLIST", $wrv);
//   print_r2($wrv);
  return;
}

?>