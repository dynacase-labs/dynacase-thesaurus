<?php

function getEventProducers($dbaccess) {
  
  $famid=-1;
  $filter[]="atags ~ 'P'";
  $tinter = getChildDoc($dbaccess, 0,0,100, $filter,$action->user->id,"TABLE",$famid);
  
  $tr[] = array(_("all families events") ,
		" ",_("all families events"));
  foreach($tinter as $k=>$v) {

      $tr[] = array($v["title"] ,
		    $v["id"],$v["title"]);
    
  }
  return $tr;  
}
function getFamRessource($dbaccess) {
  
  $famid=-1;
  $filter[]="atags ~ 'R'";
  $tinter = getChildDoc($dbaccess, 0,0,100, $filter,$action->user->id,"TABLE",$famid);
  
  foreach($tinter as $k=>$v) {

      $tr[] = array($v["title"] ,
		    $v["id"],$v["title"]);
    
  }
  return $tr;  
}
function getRessource($dbaccess,$famres,$name="") {
  $filter[]="atags ~ 'R'";
  if ($name != "") {
    $filter[]="title ~* '".pg_escape_string($name)."'";
  }
  $tinter = getChildDoc($dbaccess, 0,0,100, $filter,$action->user->id,"TABLE",$famres);
  
  foreach($tinter as $k=>$v) {

      $tr[] = array($v["title"] ,
		    $v["initid"],$v["title"]);
    
  }
  return $tr;  
}
?>