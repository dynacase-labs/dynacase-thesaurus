<?php

function specRefresh() {
  // $err= $this->recomputeNarrower();
  $this->retrieveLangLabel();
  }
function postModify() {
  $err= $this->recomputeRelations();

  $this->setValue("thc_level",$this->getLevel());
  $this->setValue("thc_title",$this->getLangTitle());
  $this->modify();
  }


function getSpecTitle() {
  return $this->getLangTitle();  
}

/**
 * recompute narrower of father
 */
function recomputeNarrower() {
  include_once("FDL/Class.SearchDoc.php");
  $s=new SearchDoc($this->dbaccess, "THCONCEPT");
  $s->addFilter("thc_thesaurus=".$this->getValue("thc_thesaurus"));
  $s->addFilter("thc_broader=".$this->id); // $s->addFilter("thc_broader ~ '\\\\y$id\\\\y'); // if many
  $t=$s->search();
  $tid=array();
  foreach ($t as $k=>$v) {
    $tid[]=$v["initid"];
  }
  $this->setValue("thc_narrower",$tid);
  $err=$this->modify();
  return $err;
}

function recomputeRelations() {
  $oldtg=$this->getOldValue("thc_broader");
  $tg=$this->getValue("thc_broader");
  if ($oldtg != $tg) {
    $d=new_doc($this->dbaccess,$oldtg);
    if ($d->isAlive()) $d->recomputeNarrower(); // update old
    $d=new_doc($this->dbaccess,$tg);
    if ($d->isAlive()) $d->recomputeNarrower(); // update new    
  }
}


function refreshFromURI() {
  include_once("THESAURUS/Lib.Thesaurus.php");
  $broaduri=$this->getTValue("thc_uribroader");
  $broad=$this->getTValue("thc_broader");

  foreach ($broaduri as $k=>$v) {
    if (! $broad[$k]) {
      $broad[$k]=getConceptIdFromURI($this->dbaccess,$v);
    }
  }
  $this->setValue("thc_broader",$broad);
}


function retrieveLangLabel() {
  include_once("THESAURUS/Lib.Thesaurus.php");
  $langs=getLangConcepts($this->dbaccess,$this->initid);

  foreach ($langs as $k=>$v) {
    $tlang[]=$v["thcl_lang"];
    $tlangid[]=$v["initid"];
    $tlanglabel[]=$v["thc_preflabel"];
  }
  $this->setValue("thc_lang",$tlang);
  $this->setValue("thc_idlang",$tlangid);
  $this->setValue("thc_langlabel",$tlanglabel);
}

function getParentConcept() {
  $gen=$this->getValue("thc_broader");
  if ($gen) {
    $d=new_doc($this->dbaccess, $gen);
    if ($d->isAlive()) return $d;
  }
  return false;
}

function getLevel() {
  $level=0;
  $father=$this->getParentConcept();
  while ($father) {
    $level++;
    $father=$father->getParentConcept();
    if ($level > 100) break;
  }
  return $level;
}
/**
 * return recursive narrower
 */
function getRNarrowers() {
  $pid=array();
  $nrs=$this->getTValue("thc_narrower");
  $nrsdoc=getDocsFromIds($this->dbaccess,$nrs,1);

  foreach ($nrsdoc as $k=>$nr) {    
    $nrs=array_merge($nrs, $this->_getRNarrowers(Doc::_val2array($nr['thc_narrower'])));
    
  }
  return $nrs;
}
/**
 * return recursive narrower
 */
private function _getRNarrowers($nrs) {
  $_nrs=$nrs;
 

  foreach ($nrs as $k=>$nr) {
    $t=getTDoc($this->dbaccess,$nr);
    $_nrs1=Doc::_val2array($t['thc_narrower']);
    $_nrs2=$this->_getRNarrowers($_nrs1);
    $_nrs=array_merge($_nrs,$_nrs1,$_nrs2);
  }
  return $_nrs;
}

/**
 * return localized label
 * @param string $lang languqge : simple notation like en,fr,ru,es
 */
function getLabelLang($lang=false) {
  if ($lang===false) $lang=strtolower(strtok(getParam("CORE_LANG"),'_'));
  $tlang=$this->getTValue("thc_lang");
  $tll=$this->getTValue("thc_langlabel");

  $kgood=-1;

  foreach ($tlang as $k=>$v) {
    if ($tlang[$k] == $lang) {
      $kgood=$k;
      break;
    }
  }

  return (isset($tll[$kgood]))?$tll[$kgood]:$tll[0];
}

/**
 * return localized title
 */
function getLangTitle($lang=false) {
  return $this->getValue("thc_label").' '.$this->getLabelLang($lang);
}
?>