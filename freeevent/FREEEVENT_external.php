<?php
/**
 * Functions use for input help in edition for calendars
 *
 * @author Anakeen 2005
 * @version $Id: FREEEVENT_external.php,v 1.4 2005/01/18 08:45:48 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEEVENT
 */
 /**
 */
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