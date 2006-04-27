<?php

function fclearmsg(&$action) {

  $mtype = GetHttpVars("mtype", "I");

  $wm = $action->parent->ClearWarningMsg();

}
?>