<?php

var $eventAttBeginDate = "CALEV_START";
var $eventAttEndDate   = "CALEV_END";
var $eventAttDesc      = "CALEV_TITLE";
var $eventAttCode      = "RV";
var $eventRessources   = array("CALEV_ATTID");
var $eventFamily       = "CALEVENT";

function postModify() {
  print_r2($this);
   $this->setEvent(); //modification de l'événement à chaque modification du producteur
}


function explodeEvt($d1, $d2) {
  include_once("FDL/Lib.Util.php");
}