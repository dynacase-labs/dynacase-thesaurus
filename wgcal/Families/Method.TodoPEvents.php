<?php

var $eventAttBeginDate = "TODO_DATE";
var $eventAttEndDate   = "TODO_DATE";
var $eventAttDesc      = "TODO_NOTE";
var $eventAttCode      = "TODO";
var $eventFamily       = "EVENT_TODO";

function postModify() {
  $err = $this->setEvent(); 
  if ($err!="") print_r2($err);
}


function  setEventSpec(&$e) {
  $sdate = strftime("%d/%m/%Y %H:%M %Z",time());
  $e->setValue("EVT_BEGDATE", $sdate);
}

function getEventRessources() {
  return array( $this->getValue("TODO_IDOWNER"));
}
?>
