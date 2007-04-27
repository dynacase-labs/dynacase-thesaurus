<?php
/**
 * Event Class
 *
 * @author Anakeen 2005
 * @version $Id: Method.Event.php,v 1.17 2007/04/27 11:41:36 eric Exp $
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

var $XmlResume =  "FREEEVENT:XMLRESUME";
var $XmlHtmlContent =  "FREEEVENT:XMLHTMLCONTENT";
public static $sqlindex=array("evtini"=>array("unique"=>true,
					      "on"=>"evt_idinitiator,evt_transft"));
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

function XmlResume() {
  global $action;
  $this->lay->set("id", $this->id);
  $this->lay->set("pid", $this->getValue("evt_idinitiator"));
  $this->lay->set("revdate", $this->revdate);
  $this->lay->set("revstatus", ($this->doctype=='Z' ? 2 : 1));
  $this->lay->set("title", $this->getValue("evt_title"));
  $this->lay->set("displaymode", $this->displayMode());
  $this->lay->set("time", FrenchDateToUnixTs($this->getValue("evt_begdate")));
  $dur = FrenchDateToUnixTs($this->getValue("evt_enddate")) - FrenchDateToUnixTs($this->getValue("evt_begdate"));
  $dur = ($dur<0 ? -$dur : $dur);
  $this->lay->set("duration", $dur);
  
  $style = $this->displayStyles();
  $this->lay->setBlockData("style", $style);
  
  $this->lay->set("content", utf8_encode($this->viewdoc($this->XmlHtmlContent)));
  
  
  $this->lay->set("menuref", $this->fromid);
  $mref = $this->setMenuRef();
  if (count($mref)>0) 
    $this->lay->setBlockData("miUse", implode(",",$mref));
  else 
    $this->lay->setBlockData("miUse", "");
  $this->lay->set("setRefMenu", true);

  return;
}

function XmlHtmlContent() {
  $this->lay->set("evtitle", $this->getValue("evt_title"));
  $this->lay->set("evfamicon", $this->getIcon($this->getValue("evt_icon")));
  $this->lay->set("evstart", substr($this->getValue("evt_begdate"),0,16));
  $this->lay->set("evend", substr($this->getValue("evt_enddate"),0,16));
  return;
}


function displayMode() {
  return 1;
}

function displayStyles() {
  return array( array("sid" => "color", "sval" => "green"), array("sid" => "background-color", "sval" => "white"));
}

function setEventMenu() {
  return array();
}

function setMenuRef() {
  return array();
}

?>
