<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: call_card.php,v 1.1 2005/04/01 11:47:20 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage
 */
 /**
 */
include_once("WGCAL/Lib.WGCal.php");
include_once("WGCAL/WGCAL_external.php");

function call_card(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $evi = GetHttpVars("ev", -1);
  $cev = GetHttpVars("cev", -1);
  $ev = GetCalEvent($dbaccess, $evi, $cev);
  if (!$ev) {
    $action->lay->set("OUT", "No event #$ev");
    return;
  }
  $rg = GetHttpVars("rg", -1);
  $mode  = GetHttpVars("m", "");

  $action->lay->set("id",    $ev->id);
  $action->lay->set("title", $ev->getValue("CALL_LABEL"));
  $action->lay->set("hour", substr($ev->getValue("CALL_DATE"),11,5));
  $action->lay->set("duration", $ev->getValue("CALL_DURATION")." mn");
  $action->lay->set("contact", $ev->getValue("CALL_CONTACT"));
  $action->lay->set("mail", $ev->getValue("CALL_CONTACTMAIL"));
  $action->lay->set("phone", $ev->getValue("CALL_CONTACTPHONE"));
  $action->lay->set("mobile", $ev->getValue("CALL_CONTACTMOB"));
  $action->lay->set("iconsrc", $ev->getIcon());
  $action->lay->set("bgcolor", "yellow");
}
?>
