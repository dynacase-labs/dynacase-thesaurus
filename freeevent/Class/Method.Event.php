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

?>