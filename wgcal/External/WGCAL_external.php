<?php
include_once('FDL/Lib.Dir.php');

function array2attrval($a, $fmt) {
  if ($fmt!="A") return $a;
  $s = "";
  foreach ($a as $k => $v) {
    if ($s != "" ) $s .= ",";
    $s .= $k."|".$v;
  }
  return $s;
}
  
/*
 ** List families used as ressources for calendars
 */
function CAL_getRessourceFamilies($dbaccess) {
  global $action;
  $rFamilies = array( "ROOMS", "IUSER", "IGROUP", "USER", "GROUP");
  $fami = array(); 
  foreach ($rFamilies as $kf => $vf) $fami[] = getFamIdFromName($dbaccess, $vf);
  return $fami;
}


/*
 ** Return event states in attribute value format
 */
function CAL_getEventStates($dbaccess, $fmt="A") {
  $evstate = array ( N_('new'),
		     N_('read'), 
		     N_('accept'), 
		     N_('reject'), 
		     N_('to be confirmed') );
  return array2attrval($evstate, $fmt);
}

/*
 ** Return event visibilities in attribute value format
 */
function CAL_getEventVisibilities($dbaccess, $fmt="A") {
  $evvis = array ( N_('public'), 
		   N_('private'), 
		   N_('my groups'));
  return array2attrval($evvis, $fmt);
}

/*
 ** Return user calendars
 */
function CAL_getCalendars($dbaccess, $user) {
  $cal = array( "mon agenda", "société", "scolaire");
  return $cal;
}

/*
 ** Return contacts : user, iuser and ...
 */
function CAL_getContacts($dbaccess, $filter) {

  $famcontact = array ( "USER", "IUSER" );
  foreach ($famcontact as $kf => $vf) $f[] = getFamIdFromName($dbaccess, $vf);

  $dtmp = new Doc($dbaccess);

  $afilter[] = array();
  if ($filter!="") $afilter[] = "title ~* '".$filter."'";
  foreach ($f as $kf => $vf) { 
    if ($vf == "" ) continue;
    $rdoc = getChildDoc($dbaccess, 0, 0, "ALL", $filter, 
			$action->user->id, "TABLE", $vf);
    foreach ($rdoc as $k => $v) {
      $contact = $v["title"];
      $contactid = $v["id"];
      $contactmail = $v["us_mail"];
     $contactphone = $v["us_phonel"];
      $contactpphone = $v["us_pphone"];
      $contactmobile = $v["us_mobile"];
      $r[] = array( $contact, $contactid, $contact, $contactmail, $contactphone, $contactpphone, $contactmobile);
    }
  }
  return $r;
}
  
/*
 ** Return ressources list
 */
function CAL_getRessources($dbaccess, $filterTitle) {
  global $action;
  $r = array();
  $fam = CAL_getRessourceFamilies($dbaccess);
  $doc = new Doc($dbaccess);
  $filter = array( );
  if ($filterTitle!="") $filter[] = "title ~* '".$filterTitle."'";
  foreach ($fam as $kf => $vf) { 
    if ($vf == "" ) continue;
    $rdoc = getChildDoc($dbaccess, 0, 0, "ALL", $filter, 
			$action->user->id, "TABLE", $vf);
    foreach ($rdoc as $k => $v) {
      $r[] = array( $v["title"], $v["id"], $v["title"]);
    }
  }
  return $r;
}

/*
 ** Return ressources list
 */
function CAL_getRessourcesOwner($dbaccess, $filterTitle) {
  global $action;
  $r = array();
  $fam = CAL_getRessourceFamilies($dbaccess);
  $doc = new Doc($dbaccess);
  $filter = array( );
  if ($filterTitle!="") $filter[] = "title ~* '".$filterTitle."'";
  foreach ($fam as $kf => $vf) { 
    if ($vf == "" ) continue;
    $rdoc = getChildDoc($dbaccess, 0, 0, "ALL", $filter, 
			$action->user->id, "TABLE", $vf);
    foreach ($rdoc as $k => $v) {
      $r[] = array( $v["title"], $v["id"], $v["title"]);
    }
  }
  return $r;
}



?>

