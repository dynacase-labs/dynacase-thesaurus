<?php

var $eventAttBeginDate = "CALEV_START";
var $eventAttEndDate   = "CALEV_END";
var $eventAttDesc      = "CALEV_TITLE";
var $eventAttCode      = "RV";
var $eventFamily       = "EVENT_FROM_CAL";

var $ZoneEvtAbstract =  "WGCAL:CALEV_ABSTRACT";
var $ZoneEvtCard =  "WGCAL:CALEV_CARD";


var $eventRVStatus     = "";

function postModify() {
  $err = $this->setEvent(); 
  if ($err!="") print_r2($err);
}


function getEventRessources() {
  return $this->getTValue("CALEV_ATTID", array());
}
