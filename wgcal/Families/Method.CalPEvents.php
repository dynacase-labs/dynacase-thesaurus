<?php

var $eventAttBeginDate = "CALEV_START";
var $eventAttEndDate   = "CALEV_END";
var $eventAttDesc      = "CALEV_TITLE";
var $eventAttCode      = "RV";
var $eventFamily       = "EVENT_FROM_CAL";

var $ZoneEvtAbstract =  "WGCAL:CALEV_ABSTRACT";
var $ZoneEvtCard =  "WGCAL:CALEV_CARD";

function postModify() {
  $err = $this->setEvent(); 
  if ($err!="") print_r2($err);
}


function getEventRessources() {
  return $this->getTValue("CALEV_ATTID", array());
}

function  setEventSpec(&$e) {
  $e->setValue("EVFC_REALENDDATE", $this->getValue("CALEV_END"));
  $e->setValue("EVFC_REPEATMODE", $this->getValue("CALEV_REPEATMODE"));
  if ($this->getValue("CALEV_REPEATMODE") > 0) {
    $e->setValue("evt_enddate", $this->getValue("evfc_repeatuntil")==0 ? jd2cal((5000001), 'FrenchLong') :  $this->getValue("evfc_repeatuntildate"));
  }
  $e->setValue("EVFC_REPEATWEEKDAY", $this->getValue("CALEV_REPEATWEEKDAY"));
  $e->setValue("EVFC_REPEATMONTH", $this->getValue("CALEV_REPEATMONTH"));
  $e->setValue("EVFC_REPEATUNTIL", $this->getValue("CALEV_REPEATUNTIL"));
  $e->setValue("EVFC_REPEATUNTILDATE", $this->getValue("CALEV_REPEATUNTILDATE"));
  $tv = $this->getTValue("CALEV_EXCLUDEDATE");
  if (count($tv)>0) {
    foreach ($tv as $kv => $vv) {
      $texc[] = $vv;
    }
    $e->setValue("EVFC_EXCLUDEDATE", $texc);
  }
  $e->setValue("EVFC_REPEATFREQ", $this->getValue("CALEV_FREQUENCY"));

  $tattid = $this->getTValue("CALEV_ATTID");
  $tattst = $this->getTValue("CALEV_ATTSTATE");
  $tattgp = $this->getTValue("CALEV_ATTGROUP");
  $nattid = array();
  $nattst = array();
  foreach ($tattid as $ka => $va) {
    $nattid[$va] = $va;
    $nattst[$va] =  $tattst[$ka];
  }
  $e->setValue("EVFC_ATTID", $nattid);
  $e->setValue("EVFC_ATTST", $nattst);  
}


function mailrv() {
  $this->lay->set("rvid", $this->id);

  $uo = new Doc($dbaccess, $this->getValue("CALEV_OWNERID"));
  $this->lay->set("rvowner", $uo->title);

  $this->lay->set("rvtitle", $this->getValue("CALEV_EVTITLE"));
  $this->lay->set("dstart", $this->getValue("CALEV_START"));
  $this->lay->set("dend", $this->getValue("CALEV_END"));

}
