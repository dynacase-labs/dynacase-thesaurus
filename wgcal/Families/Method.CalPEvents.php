<?php

var $eventAttBeginDate = "CALEV_START";
var $eventAttEndDate   = "CALEV_END";
var $eventAttDesc      = "CALEV_TITLE";
var $eventAttCode      = "RV";
var $eventFamily       = "EVENT_FROM_CAL";

function postModify() {
  $err = $this->setEvent(); 
  if ($err!="") print_r2($err);
}

function getEventOwner() {
  $uo = new Doc($this->dbaccess, $this->getValue("CALEV_OWNERID"));
  return $uo->getValue("us_whatid");
}

function getEventRessources() {
  return $this->getTValue("CALEV_ATTID", array());
}

function  setEventSpec(&$e) {
  include_once('WGCAL/WGCAL_external.php');
  $e->setValue("EVT_TITLE", $this->getValue("CALEV_EVTITLE"));
  $e->setValue("EVT_IDCREATOR", $this->getValue("CALEV_OWNERID"));
  $e->setValue("EVT_CREATOR", $this->getValue("CALEV_OWNER"));
  $e->setValue("EVFC_VISIBILITY", $this->getValue("CALEV_VISIBILITY"));
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
  $e->deleteValue("EVFC_EXCLUDEDATE");
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
  $nattid = array(); $nattst = array(); $iatt = 0;
  $rejattid = array();  $iratt = 0;
  foreach ($tattid as $ka => $va) {
    $nattid[$iatt] = $va;
    $nattst[$iatt] =  $tattst[$ka];
    $iatt++;
    if ($tattst[$ka] == EVST_REJECT) {
      $rejattid[$iratt] = $va;
      $iratt++;
    }
  }
  if (count($nattid)==0) $e->deleteValue("EVFC_LISTATTID");
  else {
    $e->setValue("EVFC_LISTATTID", $nattid);
    $e->setValue("EVFC_LISTATTST", $nattst);  
  }
 if (count($rejattid)==0)  $e->deleteValue("EVFC_REJECTATTID");  
 else $e->setValue("EVFC_REJECTATTID", $rejattid);  

  $e->setValue("EVFC_CALENDARID", $this->getValue("CALEV_EVCALENDARID"));

  // Propagate RV profil to events
  //$e->setProfil($this->dprofid );
}


function mailrv() {
  $this->lay->set("rvid", $this->id);

  $uo = new Doc($dbaccess, $this->getValue("CALEV_OWNERID"));
  $this->lay->set("rvowner", $uo->title);

  $this->lay->set("rvtitle", GetHttpVars("msg", ""));
//   $this->lay->set("rvtitle", $this->getValue("CALEV_EVTITLE"));
  $this->lay->set("dstart", $this->getValue("CALEV_START"));
  $this->lay->set("dend", $this->getValue("CALEV_END"));

}

