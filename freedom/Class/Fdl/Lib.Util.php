<?php
/**
 * Utilities functions for freedom
 *
 * @author Anakeen 2004
 * @version $Id: Lib.Util.php,v 1.3 2004/11/26 14:18:50 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
include_once("Date.php");
function newFreeVaultFile($dbaccess) {
  include_once("VAULT/Class.VaultFile.php");
  return new VaultFile($dbaccess, strtoupper(getDbName($dbaccess)));
}
function getGen($dbaccess) {
  if (getDbName($dbaccess) != "freedom") return "GEN/".strtoupper(getDbName($dbaccess));
  return "GEN";
}



/**
 * convert French date to iso8601
 * @param string $fdate DD/MM/YYYY HH:MM:SS (CET)
 * @param string $wtz with timezone add time zone in the end if true
 * @return string date YYYY-MM-DD HH:MM:SS
 */
function toIso8601($fdate,$wtz=false) {
  $isoDate="";
  if (preg_match("/^(\d\d)\/(\d\d)\/(\d\d\d\d)\s(\d\d)?:?(\d\d)?:?(\d\d)?\s+?(\w+)?$/", $fdate,$reg)) {   
    $isoDate=sprintf("%04d-%02d-%02d %02d:%02d:%02d",
		     $reg[3],$reg[2],$reg[1],$reg[4],$reg[5],$reg[6]);
    if ($reg[8]!="") $tz=$reg[7];
  }
    // ISO 8601
  if ($wtz && $tz) $isoDate.=" ".$tz;

  return $isoDate;
}
/**
 * convert French date to iso8601
 * @param string $fdate DD/MM/YYYY HH:MM:SS
 * @return Date object date
 */
function toDate($fdate) {
  //"^([0-9]{2})/([0-9]{2})/([0-9]{4})[ ]?(([0-2][0-9]):([0-9]{2}):?([0-9]{0,2}))? ?([A-Z]{3,4})?$"
  if (preg_match("/^(\d\d)\/(\d\d)\/(\d\d\d\d)\s(\d\d)?:?(\d\d)?:?(\d\d)?\s+?(\w+)?$/", $fdate,$reg)) {   
    $isoDate=sprintf("%04d-%02d-%02d %02d:%02d:%02d",
		     $reg[3],$reg[2],$reg[1],$reg[4],$reg[5],$reg[6]);
    if ($reg[8]!="") $tz=$reg[7];
  }
    // ISO 8601
  $d=new Date($isoDate);
  if ($tz) $d->setTZbyID($tz);

  return $d;
}

function DatetoMinute($d) {
  return $d->minute+((Date_Calc::dateToDays($d->day,$d->month,$d->year)*24)+$d->hour)*60;
}
function MinutetoDate($n) {
  $m= fmod($n,1440);
  $d = floor($n/1440);

  return sprintf("%s, %02d:%02d",Date_Calc::daysToDate($d),
		 floor($m/60),fmod($m, 60));
}



?>