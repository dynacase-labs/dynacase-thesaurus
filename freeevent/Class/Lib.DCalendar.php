<?php
/**
 * Uilities function for freeevent
 *
 * @author Anakeen 2005
 * @version $Id: Lib.DCalendar.php,v 1.2 2005/01/18 08:45:48 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEEVENT
 */
 /**
 */
function  cmpabsx($a, $b) {

   if ($a["absx"] == $b["absx"]) return 0;
   return (intval($a["absx"]) < intval($b["absx"])) ? -1 : 1;
}
function  cmpevtm1($a, $b) {

   if ($a["m1"] == $b["m1"]) return 0;
   return (intval($a["m1"]) < intval($b["m1"])) ? -1 : 1;
}

?>