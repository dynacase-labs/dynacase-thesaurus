<?php



/**
 * Produce events 
 * Add or Update events
 */
function setEvent() {
  return $this->pEventDefault();
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
 * identificators of the attribute which containt the document id of the ressource
 * @var array
 */
var $eventRessources=array();
/**
 * name of the family event
 * @var string
 */
var $eventFamily="EVENT";


function pEventDefault() {
  $evt=createDoc($this->dbaccess,$this->eventFamily);
  if ($evt) {
    include_once("FDL/Lib.Dir.php");
    $filter[]="evt_idinitiator=".$this->initid;
    $filter[]="evt_transft='pEventDefault'";
    
    $tevt = getChildDoc($this->dbaccess, 0 ,0,1, $filter,1, "TABLE",$this->eventFamily);
    if (count($tevt) > 0) {
      $evt=new Doc($this->dbaccess,$tevt[0]["id"]);
    }
    
  }
  if ($evt->isAlive()) {
    if (($evt->getValue("evt_begdate") != $this->getEventBeginDate()) ||
	($evt->getValue("evt_enddate") != $this->getEventEndDate())) {
      $evt->AddComment(sprintf(_("change period from [%s %s] to [%s %s]"),
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

  $evt->setValue("evt_idcreator",$this->getEventOwner());
  $evt->setValue("evt_transft", 'pEventDefault');
  $evt->setValue("evt_itransft",'mEventDefault');
  $evt->setValue("evt_idinitiator",$this->initid);
  $evt->setValue("evt_title",$this->getEventTitle());
  $evt->setValue("evt_idres",$this->getEventRessources());
  if (!$evt->isAlive())    {
    $err=$evt->Add();
  } else {
  }
  $err=$evt->refresh();
  if ($err=="") $err=$evt->modify();
  return $err;
  
}

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
 * @return int freedom id user
 */
function getEventOwner() {
  $u=new User("",$this->owner);
  return $u->fid;
}
/**
 * get the owner the event
 * @return int freedom id user
 */
function getEventTitle() {
  return $this->title;
}

/**
 * get the ressources
 * @return array of ressources
 */
function getEventRessources() {
  $tr=array();
  foreach ($this->eventRessources as $rid) {
    $tr[]=$this->getValue($rid);
  }
  return $tr;
}




?>