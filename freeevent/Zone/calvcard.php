<?php

/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: calvcard.php,v 1.1 2005/03/18 09:21:38 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage
 */
 /**
 */
function calvcard(&$action) {
  include_once("FREEEVENT/calvresume.php");
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $evi = GetHttpVars("ev", -1);
  setHttpVar("ev", $evi);
  calvresume($action);

  $ev = new Doc($dbaccess, $evi);

  $action->lay->set("id", $ev->id);
  $action->lay->set("description", $ev->getValue("evt_descr"));
  $tress = array();
  $to = array(); $ito=0;
  if ($ev->getValue("evt_idres") != "") {
    $tress = $ev->getTValue("evt_idres");
    foreach ($tress as $k => $v) {
      $rd = new Doc($dbaccess, $v);
      $to[$ito++]["rtitle"] = $rd->title;
    }
  }
  $action->lay->set("sdatehour", $ev->getValue("evt_begdate"));
  $action->lay->set("edatehour", $ev->getValue("evt_enddate"));
  $action->lay->set("RESSOURCES", count($to)>0 );
  $action->lay->setBlockData("RESSLIST", $to);
  return;
}

?>
