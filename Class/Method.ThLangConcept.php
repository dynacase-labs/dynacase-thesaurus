<?php


function postModify() {
  $this->refreshConcept();
  }
function postDelete() {
  $this->refreshConcept();
  }

  /**
   * refresh parent tu recompute translation array
   */
function refreshConcept() {
  $thc=$this->getValue("thcl_thconcept");
  if ($thc) {
    $th=new_doc($this->dbaccess,$thc);
    if ($th->isAlive()) {
      $th->refresh();
    }
  }
  }

?>