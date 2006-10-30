<?php
/**
 * Produce events methods
 *
 * @author Anakeen 2005
 * @version $Id: Method.PEvents.php,v 1.18 2006/10/30 13:17:35 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEEVENT
 */
 /**
 */


/**
 * Produce events 
 * Add or Update events
 */
function setEvent() {
  return $this->pEventDefault();
}

/**
 * Use for derived event by the producer to set added attributes
 * @param Event &$e event object
 */
function setEventSpec(&$e) {;}

/**
 * Delete events 
 * Delete related events
 */
function deleteEvent() {
  return $this->dEventDefault();
}

function postDelete() {
  $this->deleteEvent();
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
var $eventAttEndDate;
/**
 * identificator of the attribute which containt the description of the  event
 * @var string
 */
var $eventAttDesc;
/**
 * identificator of the attribute which containt the code of the event
 * @var string
 */
var $eventAttCode;
/**
 * identificators of the attribute which containt the document id of the ressource
 * @var array
 */
var $eventRessources=array();
/**
 * name of the family event
 * @var string
 */
var $eventFamily="EVENT";


/**
 * produce event based on default methods
 * @access private
 * @return string error text (empty if no error)
 */
function pEventDefault() {

  $evt=$this->getDefaultEvent();
  //  if (($this->control("edit")=="")||(isset($this->withoutControl))) { // can modify only if can modify productor
  $evt->disableEditControl();
  if ($evt->isAlive()) {
    if (($evt->getValue("evt_begdate") != $this->getEventBeginDate()) ||
	($evt->getValue("evt_enddate") != $this->getEventEndDate())) {
      $evt->AddComment(sprintf(_("Change period from [%s %s] to [%s %s]"),
			       $evt->getValue("evt_begdate"),
			       $evt->getValue("evt_enddate"),
			       $this->getEventBeginDate(),
			       $this->getEventEndDate()));
    } else {
      $evt->AddComment(sprintf(_("Changes from document \"%s\" [%d]"),
			       $this->title,
			       $this->id));
    }
  }
  $evt->setValue("evt_begdate",$this->getEventBeginDate());
  $evt->setValue("evt_enddate",$this->getEventEndDate());
  $evt->setValue("evt_desc",$this->getEventDesc());
  $evt->setValue("evt_code",$this->getEventCode());

  $evt->setValue("evt_idcreator",$this->getEventOwner());
  $evt->setValue("evt_transft", 'pEventDefault');
  $evt->setValue("evt_itransft",'mEventDefault');
  $evt->setValue("evt_idinitiator",$this->initid);
  $evt->setValue("evt_title",$this->getEventTitle());
  $evt->setValue("evt_idres",$this->getEventRessources());

  $this->setEventSpec($evt);
  if (!$evt->isAlive())    {
    $err=$evt->Add();
  } 
  if ($err=="") $err=$evt->refresh();
  if ($err=="") {
    $err=$evt->modify();
  }
  $evt->enableEditControl();
  
  

  return $err;
  
}

/**
 * delete event based on default methods
 * @access private
 * @return string error text (empty if no error)
 */
function dEventDefault() {
  $evt=createDoc($this->dbaccess,$this->eventFamily,false);
  if ($evt) {
    include_once("FDL/Lib.Dir.php");
    $filter[]="evt_idinitiator=".$this->initid;
    $filter[]="evt_transft='pEventDefault'";
    // search if already created
    $tevt = getChildDoc($this->dbaccess, 0 ,0,1, $filter,1, "TABLE",$this->eventFamily);
    if (count($tevt) > 0) {
      $evt=new_Doc($this->dbaccess,$tevt[0]["id"]);
    }    
  }
  if ($evt->isAlive()) {
    $err=$evt->delete(false,false,true);
  }
  return $err;
}
/**
 * get the begin date for the event
 * @return timestamp the date in iso8601 format or native (French)
 */
function getEventBeginDate() {
  return $this->getValue($this->eventAttBeginDate);
}
/**
 * get the end date for the event
 * @return timestamp the date in iso8601 format or native (French)
 */
function getEventEndDate() {
  return $this->getValue($this->eventAttEndDate);
}

/**
 * get the owner of the event
 * @return int freedom id user
 */
function getEventOwner() {
  $u=new User("",$this->owner);
  return $u->fid;
}
/**
 * get the title of the event
 * @return string 
 */
function getEventTitle() {
  return $this->title;
}

/**
 * get the description of the event
 * @return string
 */
function getEventDesc() {
  return $this->getValue($this->eventAttDesc);
}
/**
 * get the category of the event
 * @return string
 */
function getEventCode() {
  return $this->getValue($this->eventAttCode);
}
/**
 * get the ressources
 * @return array array of ressources
 */
function getEventRessources() {
  $tr=array();
  foreach ($this->eventRessources as $rid) {
    $v=$this->getValue($rid);
    if ($v != "") $tr[]=$v;
  }
  return $tr;
}

/**
 * reinit static variable
 */
function complete() {
  $this->getDefaultEvent(true); // reset
}

/**
 * get the default event
 * @return Doc::Event the event object
 */
function getDefaultEvent($reset=false) {
  static $__evtid=0;

  if ($reset) {
    $__evtid=0;
    return true;
  }

  if ($__evtid == 0) {
    include_once("FDL/Lib.Dir.php");
    $filter[]="evt_idinitiator=".$this->initid;
    $filter[]="evt_transft='pEventDefault'";
    // search if already created
    $tevt = getChildDoc($this->dbaccess, 0 ,0,1, $filter,1, "TABLE",$this->eventFamily);
    if (count($tevt) > 0) $__evtid=$tevt[0]["id"];      
  }  
  
  if ($__evtid == 0) {
    $evt=createDoc($this->dbaccess,$this->eventFamily,false);
  } else {
    $evt=new_Doc($this->dbaccess,$__evtid);
  }
  return $evt;
}
?>
