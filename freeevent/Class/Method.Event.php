<?php



/**
 * Produce events 
 * Add or Update events
 */
function setEvent() {
  
}
/**
 * identificator of the attribute which containt the begin date for event
 * @var string
 */
var $eventAttBeginDate;
/**
 * identificator of the attribute which containt the end date for event
 * @var string
 */
var $eventEndBeginDate;
/**
 * identificators of the attribute which containt the document id of the ressource
 * @var array
 */
var $eventRessources=array();


/**
 * Default calendar view
 */
var $eventCalView = "FREEEVENT:EVENTCALVIEW";
/**
 * Default planner view
 */
var $eventPlanView = "FREEEVENT:EVENTPLANVIEW";

/**
 * get the begin date for the event
 * @return timestamp the date
 */
function getEventBeginDate() {
  return $this->getValue($this->eventAttBeginDate);
}
/**
 * get the end date for the event
 * @return timestamp the date
 */
function getEventEndDate() {
  return $this->getValue($this->eventAttEndDate);
}

/**
 * get the owner the event
 * @return int what id user
 */
function getEventOwner() {
  return $this->owner;
}

/**
 * get the ressources
 * @return array of ressources
 */
function getEventRessources() {
  return $this->eventRessources;
}



function postModify() {
  $this->setEvent();
}

function eventplanview() {
  $this->eventViewCommonAttr();
  $this->lay->Set("ID", $this->GetValue("ID"));
  $this->lay->Set("COLOR", "red");
}

function eventCalView() {
  $this->eventViewCommonAttr();
}

function eventViewCommonAttr() {
  
  $this->lay->Set("TITLE", $this->GetValue("EVT_TITLE"));
  $dfmt = $this->eventGetDateTimeString("FCAL_DFMT_1", $this->GetValue("EVT_BEGDATE"));
  $this->lay->Set("BEGIN_DATE", $dfmt);
  $dfmt = $this->eventGetDateTimeString("FCAL_DFMT_1", $this->GetValue("EVT_ENDDATE"));
  $this->lay->Set("END_DATE", $dfmt);
  $this->lay->Set("OWNER",  $this->GetValue("EVT_CREATOR"));
  $r = $this->GetTValue("EVT_RES");
  if (count($r)>0) {
    $t = array();
    foreach($r as $k=>$v) {
      if ($v!="") $t[]["R"] = $v;
    }
  } else {
    $t = NULL;
  }
  $this->lay->SetBlockData("RESSOURCES", $t);
}

function eventGetDateTimeString($fmt="",$date) {
  global $action;
  $df = $date;
  if ($fmt!="") {
    $f = $action->GetParam($fmt, "%x %X");
    $df = strftime($f, $this->eventDateToTS($date));  
  }
  return $df;
}

function eventDateToTS($d) {
  $day = substr($d,0,2);
  $mon = substr($d,3,2);
  $yea = substr($d,6,4);
  $hou = substr($d,11,2);
  $min = substr($d,14,2);
  $sec = substr($d,17,2);
  return mktime($hou, $min, $sec, $mon, $day, $yea);
}

  

  
?>