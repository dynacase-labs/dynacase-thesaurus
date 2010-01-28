<?php

/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */



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