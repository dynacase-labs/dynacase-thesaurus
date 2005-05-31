<?php

function rendezvous_read(&$action) {
  $docid = GetHttpVars("id");
  $action->lay->set("id", $docid);
  return;
}
?>