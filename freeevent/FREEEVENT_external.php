<?php

function getEventProducers($dbaccess) {
  
  $famid=-1;
  $filter[]="atags ~ 'P'";
  $tinter = getChildDoc($dbaccess, 0,0,100, $filter,$action->user->id,"TABLE",$famid);
  
  foreach($tinter as $k=>$v) {

      $tr[] = array($v["title"] ,
		    $v["id"],$v["title"]);
    
  }
  return $tr;  
}
?>