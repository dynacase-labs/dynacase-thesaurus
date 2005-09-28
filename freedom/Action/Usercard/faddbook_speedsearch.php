<?php
/**
 * Freedom Address Book
 *
 * @author Anakeen 2000
 * @version $Id: faddbook_speedsearch.php,v 1.1 2005/09/28 15:36:56 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
 /**
 */

include_once("FDL/freedom_util.php");
include_once("FDL/Lib.Dir.php");

function faddbook_speedsearch(&$action) 
{ 
  $dbaccess = $action->getParam("FREEDOM_DB");

  $vtext = GetHttpVars("vtext", "");
  if ($vtext=="") {
    $action->lay->set("vtext", _("search"));
    $action->lay->set("first", "true");
  } else {
    $action->lay->set("vtext", $vtext);
    $action->lay->set("first", "false");
  }
  $action->lay->set("Result", false);

  $fam = new_Doc($dbaccess, "IUSER");
  $action->lay->set("icon", $fam->getIcon());

  if ($vtext=="") return;

  $rq = getChildDoc($dbaccess, 0, 0, 25, array("(title ~* '^".$vtext."') OR ( us_society ~* '^".$vtext."' )"), $action->user->id, "TABLE", "USER", true, "title");
  $cu = array();
  foreach ($rq as $k => $v) {
    $cu[] = array( "id" => $v["id"], 
		   "icon" => Doc::GetIcon($v["icon"]),
		   "title" => ucwords(strtolower($v["title"])) );
  }
  if (count($cu)>0) $action->lay->set("Result", true);
  $action->lay->setBlockData("Contacts", $cu);

}
?>
