<?php
/**
 * Event Class
 *
 * @author Anakeen 2005
 * @version $Id: Method.Event.php,v 1.9 2005/11/04 11:25:18 marc Exp $
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
  $this->lay->set("id", $this->id);
  $this->lay->set("title", $this->parseContent($this->getValue("evt_title")));
  $this->lay->set("dmode", 0);
  $this->lay->set("time", FrenchDateToUnixTs($this->getValue("evt_begdate")));
  $dur = FrenchDateToUnixTs($this->getValue("evt_enddate")) - FrenchDateToUnixTs($this->getValue("evt_begdate"));
  $dur = ($dur<0 ? -$dur : $dur);
  $this->lay->set("duration", $dur);

  // Menus
  $urlbase = $action->getParam("CORE_SSTANDURL");
  $menus = array();
  $menus[] = array( "muse" => 1,
		    "mtype" => "ACTION",
		    "mlabel" => $this->parseContent("Afficher"),
		    "mtarget" => "evdisplay",
		    "micon" => "",
		    "maction" => $this->parseContent("$urlbase&sole=Y&app=FDL&action=FDL_CARD&zone=EVXML&view=F&id=".$this->id));
  $this->lay->setBlockData("MENUITEM", $menus);
  $this->lay->set("xmlResume", true);
}

function parseContent($ct) {
  return htmlentities($ct);  // ereg_replace( "&", "&amp;", $ct);
}

?>
