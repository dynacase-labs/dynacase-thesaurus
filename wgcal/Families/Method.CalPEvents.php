<?php

var $eventAttBeginDate = "CALEV_START";
var $eventAttEndDate   = "CALEV_END";
var $eventAttDesc      = "CALEV_TITLE";
var $eventAttCode      = "RV";
var $eventFamily       = "EVENT_FROM_CAL";

function postModify() {
  $err = $this->setEvent(); //modification de l'événement à chaque modification du producteur
  print_r2($this);
  if ($err!="") print_r2($err);
}


function getEventRessources() {
  return $this->getTValue("CALEV_ATTID", array());
}