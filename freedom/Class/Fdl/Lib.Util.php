<?php
/**
 * Utilities functions for freedom
 *
 * @author Anakeen 2004
 * @version $Id: Lib.Util.php,v 1.8 2005/01/27 10:21:36 marc Exp $
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

function StringDateToJD($sdate) {
  $jd=FrenchDateToJD($sdate);
  if ($jd === false)  $jd=Iso8601ToJD($sdate);
  return $jd;
}

function FrenchDateToJD($fdate) { 
if (preg_match("/^(\d\d)\/(\d\d)\/(\d\d\d\d)\s?(\d\d)?:?(\d\d)?:?(\d\d)?\s?(\w+)?$/", $fdate,$reg)) {   
   return cal2jd("CE",  $reg[3], $reg[2], $reg[1], $reg[4],$reg[5] , 0 );
  }
 return false;
}

function FrenchDateToUnixTs($fdate) {
  if (preg_match("/^(\d\d)\/(\d\d)\/(\d\d\d\d)\s?(\d\d)?:?(\d\d)?:?(\d\d)?\s?(\w+)?$/", $fdate,$r)) {   
    $ds = mktime($r[4], $r[5], $r[6], $r[2], $r[1], $r[3]);
    $dt = strftime("%s", $ds);
  } else {
    $dt = -1;
  }
  return $dt;
}

function Iso8601ToJD($isodate) {
 if (preg_match("/^(\d\d\d\d)-(\d\d)-(\d\d)\s?(\d\d)?:?(\d\d)?:?(\d\d)?\s?(\w+)?$/",$isodate ,$reg)) {   
   return cal2jd("CE",  $reg[1], $reg[2], $reg[3], $reg[4],$reg[5] , 0 );
  }
 return false;
}


function cal2jd( $era, $y, $m, $d, $h, $mn, $s ) {
  if (($y>1969) && ($y<2038)) {
    $nd=unixtojd(mktime($h,$mn,$s,$m,$d,$y));
    $nm=(($h*60+$mn)-720)/1440;
    $nd+=round($nm,3);
    return $nd;
  } else {	
    
    if( $y == 0 ) {
      AddWarningMsg("There is no year 0 in the Julian system!");
      return "invalid";
    }
    if( $y == 1582 && $m == 10 && $d > 4 && $d < 15 && $era != "BCE" ) {
      AddWarningMsg("The dates 5 through 14 October, 1582, do not exist in the Gregorian system!");
      return "invalid";
    }

    if( $era == "BCE" ) $y = -$y + 1;
	if( $m > 2 ) {
		$jy = $y;
		$jm = $m + 1;
	} else {
		$jy = $y - 1;
		$jm = $m + 13;
	}

	$intgr = floor( floor(365.25*$jy) + floor(30.6001*$jm) + $d + 1720995 );

	//check for switch to Gregorian calendar
	$gregcal = 15 + 31*( 10 + 12*1582 );
	if( $d + 31*($m + 12*$y) >= $gregcal ) {
		$ja = floor(0.01*$jy);
		$intgr += 2 - $ja + floor(0.25*$ja);
	}

	//correct for half-day offset
	$dayfrac = $h/24.0 - 0.5;
	if( $dayfrac < 0.0 ) {
		$dayfrac += 1.0;
		$intgr--;
	}

	//now set the fraction of a day
	$frac = $dayfrac + ($mn + $s/60.0)/60.0/24.0;

    //round to nearest second
    $jd0 = ($intgr + $frac)*100000;
    $jd  = floor($jd0);
    if( $jd0 - $jd > 0.5 ) $jd++;
    return $jd/100000;
    
  }
  return "Date Error";
}

/**
 * return the day of the week (1 id Monday, 7 is Sunday)
 * @param float julian date
 * @return int
 */
function jdWeekDay($jd) {
    //weekday
    
  $t  = doubleval($jd) + 0.5;
  $wd = floor( ($t/7 - floor($t/7))*7 + 0.000000000317 );   //add 0.01 sec for truncation error correction
  return $wd+1;
}

function jd2cal( $jd,$dformat='' ) {


  //
  // get the date from the Julian day number
  //
   $intgr   = floor($jd);
   $frac    = $jd - $intgr;
   $gregjd  = 2299160.5;
  if( $jd >= $gregjd ) {				//Gregorian calendar correction
     $tmp = floor( ( ($intgr - 1867216.0) - 0.25 ) / 36524.25 );
    $j1 = $intgr + 1 + $tmp - floor(0.25*$tmp);
  } else
    $j1 = $intgr;

  //correction for half day offset
  $df = $frac + 0.5;
  if( $df >= 1.0 ) {
    $df -= 1.0;
    $j1++;
  }

  $j2 = $j1 + 1524.0;
  $j3 = floor( 6680.0 + ( ($j2 - 2439870.0) - 122.1 )/365.25 );
  $j4 = floor($j3*365.25);
  $j5 = floor( ($j2 - $j4)/30.6001 );

  $d = floor($j2 - $j4 - floor($j5*30.6001));
  $m = floor($j5 - 1.0);
  if( $m > 12 ) $m -= 12;
  $y = floor($j3 - 4715.0);
  if( $m > 2 )   $y--;
  if( $y <= 0 )  $y--;

  //
  // get time of day from day fraction
  //
  $hr  = floor($df * 24.0);
  $mn  = floor(($df*24.0 - $hr)*60.0);
  $f  = (($df*24.0 - $hr)*60.0 - $mn)*60.0;
  $sc  = floor($f);
  $f -= $sc;
  if( $f > 0.5 ) $sc++;
  if( $sc == 60 ) {
    $sc = 0;
    $mn++;
  }
  if( $mn == 60 )  {
    $mn = 0;
    $hr++;
  }
  if( $hr == 24 )  {
    $hr = 0;
    $d++;            //this could cause a bug, but probably will never happen in practice
  }

  if( $y < 0 ) {
    $y = -$y;
    $ce=' BCE';
    // form.era[1].checked = true;
  } else {
    $ce='';
    //   form.era[0].checked = true;
  }
  switch ($dformat) {
  case 'M':
    $retiso8601=$m;
    break;
  case 'Y':
    $retiso8601=$y;
    break;
  case 'd':
    $retiso8601=$d;
    break;
  case 'French':
    $retiso8601=sprintf("%02d/%02d/%04s",$d,$m,$y);
    break;
  default:
    $retiso8601=sprintf("%04d-%02d-%02s %02d:%02d%s",
			$y,$m,$d,$hr,$mn,$ce);
  }
  return $retiso8601;
}
  



?>