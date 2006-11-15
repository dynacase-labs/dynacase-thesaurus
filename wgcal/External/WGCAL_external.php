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
 ** Return event states in attribute value format
 */
define(EVST_NEW, 0);
define(EVST_READ, 1);
define(EVST_ACCEPT, 2);
define(EVST_REJECT, 3);
define(EVST_TBC, 4);
global $EventStateDescr;
$EventStateDescr = array( EVST_NEW => array( _("new"), "red" ),
			  EVST_READ => array( _("read"), "orange" ),
			  EVST_ACCEPT => array( _("accept"), "#0dff00" ),
			  EVST_REJECT => array( _("reject"), "black" ),
			  EVST_TBC => array( _("to be confirmed"), "red" ) );


function WGCalGetState($fmt="A") {
  global $EventStateDescr;
  static $evstate = false;
  if ($evstate===false) {
    foreach ($EventStateDescr as $k => $v ) $evstate[] = $v[0];
    $evstate = array2attrval($evstate, $fmt);
  }
  return $evstate;
}

function WGCalGetLabelState($state) {
  global $EventStateDescr;
  if ($state==-1) return "";
  if ($state>=count($EventStateDescr)) return _('unknown');
  else return $EventStateDescr[$state][0];
}

function WGCalGetColorState($state, $def="transparent") {
  global $EventStateDescr;
  if ($state==-1) return $def;
  if ($state>=count($EventStateDescr)) return "lightgrey";
  else return $EventStateDescr[$state][1];
  return "red"; // pas normal
}

/*
 ** Return event visibilities in attribute value format
 */
function CAL_getEventVisibilities($dbaccess, $fmt="A") {
  $evvis = array ( _("public"), _("confidential"), _('my groups'), _("private"), );
  return array2attrval($evvis, $fmt);
}

/*
 ** Return user calendars
 */
function WGCalGetMyCalendars($dbaccess) {
  global $action;
  $tcals = array();
  $tcals[] = array( -1, _("My public calendar"));
  $cals = array();
  $cals = GetChildDoc($dbaccess, 0, 0, "ALL", array("owner = ".$action->parent->user->fid ),
		      $action->parent->user->fid, "TABLE", getIdFromName($dbaccess,"SCALENDAR"));
  foreach ($cals as $k => $v) {
    $tcals[] = array( $v["id"], $v["ba_title"]);
  }
  return $tcals;
}

/*
 ** Return contacts : user, iuser and ...
 */
function CAL_getContacts($dbaccess, $filter) {

  $famcontact = array ( "USER", "IUSER" );
  foreach ($famcontact as $kf => $vf) $f[] = getFamIdFromName($dbaccess, $vf);

  $dtmp = new_Doc($dbaccess);

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
  
function  WGCalUParam($pname, $def="", $uid=-1) {
  global $action;
  $uid = ($uid==-1 ? $action->user->id : $uid);
  $r = $action->parent->param->getUParam($pname, $uid, $action->parent->GetIdFromName("WGCAL"));
  return ($r=="" ? $def : $r);
}

function CalListAlarmInterval() {
  return array(
	       "0" => _("No alarm"),
	       "5" => _("Five minutes"),
	       "15" => _("Quarter of hour"),
	       "30" => _("Half of hour"),
	       "60" => _("One hour"),
	       "120" => _("Two hours"),
	       "720" => _("Half day"),
	       "1440" => _("One day"),
	       "2880" => _("Two days"),
	       );
}


?>
