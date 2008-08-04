<?php


function specRefresh() {
  // $err= $this->recomputeNarrower();
  }
function postModify() {
  $err= $this->recomputeRelations();
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
?>