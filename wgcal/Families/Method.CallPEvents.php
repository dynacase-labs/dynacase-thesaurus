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


function  setEventSpec(&$e) {
  $e->setValue("EVT_TITLE", $this->getValue("CALL_CONTACT")." : ".substr($this->getValue("CALL_LABEL"),0,20)."...");
  $edate = StringDateToJD($e->getValue("EVT_BEGDATE")) + ($this->getValue("CALL_DURATION", 60) * (1.0 / (24*60))); 
  $e->setValue("EVT_ENDDATE", jd2cal($edate, 'FrenchLong'));
}

function getEventRessources() {
  return array( $this->getValue("CALL_IDCONTACT"), 
		$this->getValue("CALL_IDOWNER"));
}
?>

