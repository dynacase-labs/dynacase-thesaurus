<?php
/**
 * Utilities functions for freedom
 *
 * @author Anakeen 2004
 * @version $Id: Lib.Util.php,v 1.2 2004/11/25 09:07:32 eric Exp $
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
  if (ereg("^([0-9]{2})/([0-9]{2})/([0-9]{4})[ ]?(([0-2][0-9]):([0-9]{2}):?([0-9]{0,2}))? ?([A-Z]{3,4})?$", $fdate,$reg)) {   
    $isoDate=sprintf("%04d-%02d-%02d %02d:%02d:%02d",
		     $reg[3],$reg[2],$reg[1],$reg[5],$reg[6],$reg[7]);
    if ($reg[8]!="") $tz=$reg[8];
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
  
  if (ereg("^([0-9]{2})/([0-9]{2})/([0-9]{4})[ ]?(([0-2][0-9]):([0-9]{2}):?([0-9]{0,2}))? ?([A-Z]{3,4})?$", $fdate,$reg)) {   
    $isoDate=sprintf("%04d-%02d-%02d %02d:%02d:%02d",
		     $reg[3],$reg[2],$reg[1],$reg[5],$reg[6],$reg[7]);
    if ($reg[8]!="") $tz=$reg[8];
  }
    // ISO 8601
  $d=new Date($isoDate);
  if ($tz) $d->setTZbyID($tz);

  return $d;
}

function toMinute($d) {
  return $d->minute+((Date_Calc::dateToDays($d->day,$d->month,$d->year)*24)+$d->hour)*60;
}
?>