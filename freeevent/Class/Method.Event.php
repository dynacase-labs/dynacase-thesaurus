<?php
/**
 * Event Class
 *
 * @author Anakeen 2005
 * @version $Id: Method.Event.php,v 1.6 2005/01/18 08:45:48 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEEVENT
 */
 /**
 */




/**
 * return all atomic event found in period between $d1 and $d2 for this event
 * by default it is itself.
 * this method must be change by derived class when events can be repeat.
 * @param date $d1 begin date in iso8601 format YYYY-MM-DD HH:MM
 * @param date $d2 end date in iso8601 format
 * @return array array of event. These events returned are not objects but only a array of variables.
 */
function explodeEvt($d1,$d2) {
  return array(get_object_vars($this));
}
function explodeEvtTest($d1,$d2) {
  $t1[]=get_object_vars($this);
  $this->setValue("evt_begdate","10/12/2003");
  $this->evt_enddate="20/12/2003";
  $t1[]=get_object_vars($this);
  return $t1;;
}
  
?>