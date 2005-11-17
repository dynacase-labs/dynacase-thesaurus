<?php
/**
 * Event Class
 *
 * @author Anakeen 2005
 * @version $Id: Method.Event.php,v 1.11 2005/11/17 06:13:03 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEEVENT
 */
 /**
 */

var $calVResume     = "FREEEVENT:CALVRESUME";
var $calVCard       = "FREEEVENT:CALVCARD";
var $calVLongText   = "FREEEVENT:CALVLONGTEXT";
var $calVShortText  = "FREEEVENT:CALVSHORTTEXT";

var $calPopupMenu = array();

var $evXml =  "FREEEVENT:EVXML";

/**
 * return all atomic event found in period between $d1 and $d2 for this event
 * by default it is itself.
 * this method must be change by derived class when events can be repeat.
 * @param date $d1 begin date in iso8601 format YYYY-MM-DD HH:MM
 * @param date $d2 end date in iso8601 format
 * @return array array of event. These events returned are not objects but only a array of variables.
 */
function explodeEvt($d1,$d2) {
  return array(get_object_vars($this));
}
function explodeEvtTest($d1,$d2) {
  $t1[]=get_object_vars($this);
  $this->setValue("evt_begdate","10/12/2003");
  $this->evt_enddate="20/12/2003";
  $t1[]=get_object_vars($this);
  return $t1;;
}

function getEventIcon() {  
  $eicon=$this->getValue("EVT_ICON");
  if ($eicon=="")  return $this->getValue("EVT_FROMINITIATORICON");
  return "";
}

function evXml() {

  $view = GetHttpVars("vxml", "R");
  $this->lay->set("xmlResume", true);
  $this->lay->set("xmlCard", true);
  switch ($view) {
  case "C": $this->XmlCard(); break;
  default: $this->XmlResume();
  }
  
}

function XmlCard() {
  $this->lay->set("xmlCard", true);
}

function XmlResume() {
  global $action;
  $lastrev = GetHttpVars("lastrev", 0);
  if ($this->revdate>$lastrev) {
    $this->lay->set("id", $this->id);
    $this->lay->set("pid", $this->getValue("evt_idinitiator"));
    $this->lay->set("revdate", $this->revdate);
    $this->lay->set("revstatus", ($this->doctype=='Z' ? 2 : 1));
    $this->lay->set("title", $this->getValue("evt_title"));
//     $this->lay->set("title", htmlentities($this->getValue("evt_title")));
    $this->lay->set("displaymode", 1);
    $this->lay->set("time", FrenchDateToUnixTs($this->getValue("evt_begdate")));
    $dur = FrenchDateToUnixTs($this->getValue("evt_enddate")) - FrenchDateToUnixTs($this->getValue("evt_begdate"));
    $dur = ($dur<0 ? -$dur : $dur);
    $this->lay->set("duration", $dur);

    $bgcolor = "white";
    $color = "blue";
    $style = array( array("sid" => "background-color", "sval" => $bgcolor),
		    array("sid" => "color", "sval" => $color),
		    array("sid" => "border", "sval" => "1px dotted $color") );

    $this->lay->set("evtitle", htmlentities($this->getValue("evt_title")));
    $this->lay->set("evfamicon", $this->getIcon($this->getValue("evt_icon")));
    $this->lay->set("evstart", substr($this->getValue("evt_begdate"),0,16));
    $this->lay->set("evend", substr($this->getValue("evt_enddate"),0,16));
    //$this->lay->setBlockData("style", $style);
    $this->lay->setBlockData("style", array());

    $mref = GetHttpVars("mref");
    $this->lay->set("setRefMenu", false);
    $this->lay->setBlockData("miUse", array());
    $this->lay->set("menuRef", $mref);
    if ($mref!=="") {
      //     $this->lay->set("hasMenu", true);
      //     $this->lay->set("menuRef", $mref);
      //     $this->lay->setBlockData("miUse", array());
    }
    $this->lay->set("xmlResume", true);
  } else {
    $this->lay->set("xmlResume", false);
  }
  return;
}

?>
