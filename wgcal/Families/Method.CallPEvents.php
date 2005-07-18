<?php

var $eventAttBeginDate = "CALL_DATE";
var $eventAttEndDate   = "CALL_DATE";
var $eventAttDesc      = "CALL_LABEL";
var $eventAttCode      = "CALL";
var $eventFamily       = "EVENT_CALL";

function postModify() {
  $err = $this->setEvent(); 
  if ($err!="") print_r2($err);
}

function getEventOwner() {
  return $this->getValue("CALL_OWNERID");
}

function  setEventSpec(&$e) {
  $edate = StringDateToJD($e->getValue("EVT_BEGDATE")) + ($this->getValue("CALL_DURATION", 60) * (1.0 / (24*60))); 
  $e->setValue("EVT_ENDDATE", jd2cal($edate, 'FrenchLong'));
}

function getEventRessources() {
  return array( $this->getValue("CALL_IDCONTACT"), 
		$this->getValue("CALL_IDOWNER"));
}
?>

