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
function WGCalGetRessourceFamilies($dbaccess) {
  global $action;
  $filter = array ( "doctype='C'", "atags='R'");
  $rdocs = array();
  $rdocs = getChildDoc($dbaccess, 0, 0, 100, $filter, 
			$action->user->id, "TABLE");
  return $rdocs;
}


/*
 ** Return event states in attribute value format
 */
global $EventStateDescr;
$EventStateDescr = array( array( N_('new'), "red" ),
				 array( N_('read'), "orange" ),
				 array( N_('accept'), "#0dff00" ),
				 array( N_('reject'), "black" ),
				 array( N_('to be confirmed'), "yellow" ) );

function CAL_getEventStates($dbaccess, $fmt="A") {
  return WGCalGetState($dbaccess, $fmt);
}

function WGCalGetState($dbaccess, $fmt="A") {
  global $EventStateDescr;
  foreach ($EventStateDescr as $k => $v ) $evstate[] = $v[0];
  return array2attrval($evstate, $fmt);
}

function WGCalGetLabelState($state) {
  global $EventStateDescr;
  if ($state>=count($EventStateDescr)) return N_('unknown');
  else return $EventStateDescr[$state][0];
}

function WGCalGetColorState($state) {
  global $EventStateDescr;
  if ($state>=count($EventStateDescr)) return "lightgrey";
  else return $EventStateDescr[$state][1];
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
    $rdoc = getChildDoc($dbaccess, 0, 0, 100, $filter, 
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
  $fam = WGCalGetRessourceFamilies($dbaccess);
  $doc = new Doc($dbaccess);
  $filter = array( );
  if ($filterTitle!="") $filter[] = "title ~* '".$filterTitle."'";
  foreach ($fam as $kf => $vf) { 
    if ($vf == "" ) continue;
    $rdoc = getChildDoc($dbaccess, 0, 0, 100, $filter, 
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
  $fam = WGCalGetRessourceFamilies($dbaccess);
  $doc = new Doc($dbaccess);
  $filter = array( );
  if ($filterTitle!="") $filter[] = "title ~* '".$filterTitle."'";
  foreach ($fam as $kf => $vf) { 
    if ($vf == "" ) continue;
    $rdoc = getChildDoc($dbaccess, 0, 0, 100, $filter, 
			$action->user->id, "TABLE", $vf);
    foreach ($rdoc as $k => $v) {
      $r[] = array( $v["title"], $v["id"], $v["title"]);
    }
  }
  return $r;
}



?>

