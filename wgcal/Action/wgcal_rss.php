<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_rss.php,v 1.1 2006/10/23 16:20:58 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("WGCAL/Lib.WGCal.php");
include_once('WHAT/Lib.Common.php');

function wgcal_rss(&$action) { 

  global $_SERVER;

  header('Content-type: text/xml; charset=utf-8');
  $action->lay->setEncoding("utf-8");
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  $explode = true;
  $filter = array();
  $base  = getParam("CORE_BASEURL");
  $server = getParam("CORE_ABSURL"); 

  $start = time();
  $end = $start+(3600*24*365);
  $dd[0] = strftime("%Y-%m-%d 00:00:00", $start);
  $dd[1] = strftime("%Y-%m-%d 23:59:59", $end);

  setHttpVar("ress", $action->user->fid);

  $action->lay->set("username", $action->user->firstname." ". $action->user->lastname);
  $action->lay->set("base", setText(($base)));
  $action->lay->set("link", setText(($base."app=WGCAL&action=WGCAL_MAIN")));
  $action->lay->set("pDate", date("r", time()));
  $action->lay->set("dates", "du ".strftime("%A %d %B %Y",$start)." - ".strftime("%A %d %B %Y",$end));
  $action->lay->set("image", $action->getImageUrl("wgcal.png"));

  $evt = wGetEvents($dd[0], $dd[1], $explode, $filter); 
  if (count($evt) > 0) {

    $td = array();
    $edoc = array();
    $hsep = ":";
    foreach ($evt as $ke=> $ve) {

      if (!isset($edoc[$ve["id"]])) $edoc[$ve["id"]] = getDocObject($dbaccess, $ve);
      if ($edoc[$ve["id"]]->myState==3) continue;


      if (!isset($rdoc[$ve["evt_idinitiator"]])) $edoc[$ve["evt_idinitiator"]] = new_Doc($dbaccess, $ve["evt_idinitiator"]);

      $end = ($ve["evfc_realenddate"] == "" ? $ve["evt_enddate"] : $ve["evfc_realenddate"]);
      $dstart = substr($ve["evt_begdate"], 0, 2);
      $m_start = substr($ve["evt_begdate"], 3, 2);
      $y_start = substr($ve["evt_begdate"], 6, 4);
      $hstart = substr($ve["evt_begdate"],11,5);
      $dend = substr($end, 0, 2);
      $m_end = substr($end, 3, 2);
      $y_end = substr($end, 6, 4);
      $hend = substr($end,11,5);

      $dsl = mktime( 0, 0, 0, $m_start, $dstart, $y_start ); 
      $del = mktime( 0, 0, 0, $m_end, $dend, $y_end ); 
      $nb = round(($del - $dsl) / (60*60*24)) + 1; 
      for ($iday=0; $iday<$nb; $iday++) {
	
	if ($hstart==$hend && $hend=="00:00") {
	  $hours = "("._("no hour").")";
	  $p = 0;
	} else if ($hstart=="00:00" && $hend=="23:59") {
	  $hours = "("._("all the day _ short").")";
	  $p = 1;
	} else {
	  if ($dend==$dstart) {
	    $hours = $hstart."$hsep".$hend;
	    $p = 4;
	  } else { 
            if (($iday+$dstart)==$dstart) {
	      $hours = $hstart."$hsep..."; //" 24:00";
	      $p = 3;
            } else if (($iday+$dstart)==$dend) {
	      $hours = "...$hsep".$hend; // "00:00 ".$lend;
	      $p = 2;
	    } else {
	      $hours = "("._("all the day _ short").")";
	      $p = 1;
	    }
	  }
	}

	$cday = strftime("%d/%m",$dsl+($iday*3600*24)); 
	$cd = strftime("%Y%m%d",$dsl+($iday*3600*24)); 

	$meeting = false;
	$mlist = "";
	$latt = $edoc[$ve["id"]]->getTValue("evfc_listattid");
	if (count($latt) > 1) {
	  $meeting = true;
	  foreach ($latt as $k => $v)   $mlist .= ($mlist==""?"":", ").$v;
	}

	$td[] = array( 
		      "cd"    => $cd,
		      "p"     => $p,
		      "id"    => $ve["id"],
// 		      "title" => setText("[".$cday."] ".$hours." :: ".$edoc[$ve["id"]]->getTitleInfo()),
 		      "title" => setText("".$cday." ".$hours." | ".$edoc[$ve["id"]]->getTitleInfo()),
		      "owner" => setText($ve["evt_creator"]),
		      "pdate"  => date("r", FrenchDateToUnixTs($ve["cdate"])),
		      "description" => setText($ve["evt_desc"]." ".$ve["evfc_location"]),
		      "link" => setText($server.$base."app=FDL&action=IMPCARD&id=".$ve["evt_idinitiator"]),
		      "base"  => setText(urlencode($base)),
		      "guid"  => setText($server.$base."app=FDL&action=IMPCARD&id=".$ve["evt_idinitiator"]),
		      "note" => $ve["evt_desc"],
		      "hnote" => ($ve["evf_desc"]!=""?true:false),
		      "hatt" => $meeting,
		      "attendees" => $mlist,
		      "location" => $ve["evfc_location"],
		      "hloc" => ($ve["evfc_location"]!=""?true:false),
		      "hcat" => false,
		      );
	
      }
    }
    uasort($td, "tabevSort");
    $action->lay->setBlockData("ITEMS", $td);
  } else {
    $action->lay->setBlockData("ITEMS", null);
  }    
}

function tabevSort($a, $b) {
  if ($a["cd"]==$b["cd"]) {
    if ($a["p"]<4 || $b["p"]<4) return $a["p"] - $b["p"];
    return strcmp($a["hour"], $b["hour"]);
  } 
  return $a["cd"] - $b["cd"];
}

function setText($t) {
  return utf8_encode(xmlentities($t));
}
function xmlentities($string) {
   return str_replace ( array ( '&', '"', "'", '<', '>', ',' ), 
			array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '&apos;' ), 
			$string );
   return $string;
}

?>
