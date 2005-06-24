<?php

/**
 * Looks for rendez-vous alert !
 *
 * @author Anakeen 2004
 * @version $Id: wgcal_alert.php,v 1.1 2005/06/24 14:40:49 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
include_once("FDL/Class.Doc.php");
include_once("FDL/Lib.Dir.php");
include_once("WGCAL/Lib.wTools.php");

ini_set("include_path", ".:/home/httpd/what:/home/httpd/what/WHAT:/usr/share/pear");
ini_set("max_execution_time", "36000");

define(INTERVAL, 600); // run every 600 seconds (10mn);

$dbfreedom=GetParam("FREEDOM_DB");

$idfamref = getIdFromName($dbfreedom, "CALEVENT");
$reid = getIdFromName($dbfreedom,"WG_AGENDA");

setHttpVar("idres","");
setHttpVar("idfamref", $idfamref);


$curtime = time();
$nextime = $curtime + INTERVAL - 1;

$d1 = ts2db($curtime); 
// $d2 = ts2db($nextime);
$d2 = "23:59 10/10/9999";

TRACE("idres=[] idfamref=[$idfamref] d1=[$d1] d2=[$d2]");

$filter = array ( "evfc_alartime > 0");
// Recherche de tout les events avec alarme qui vérifie a <= (H-x) < (a+i)
$dre = new Doc($dbfreedom, $reid);
//   print_r2($dre);
$tev = array();
$tev = $dre->getEvents($d1,$d2, true, $filter);

foreach ( $tev as $kv => $vv ) {
  print_r2($vv);
  echo "-----------------------------------";
}

function TRACE($t="xxxx") {
  echo "<".basename(__FILE__)."::".__LINE__."> ".$t." \n";
}
?>
