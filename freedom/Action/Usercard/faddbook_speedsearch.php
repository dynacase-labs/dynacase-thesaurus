<?php
/**
 * Freedom Address Book
 *
 * @author Anakeen 2000
 * @version $Id: faddbook_speedsearch.php,v 1.4 2005/10/02 12:34:29 marc Exp $
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
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");

  $ws = (GetHttpVars("sallf", "")=="on"?1:0);
  $vtext = GetHttpVars("vtext", "");
  if ($vtext=="") {
    $action->lay->set("vtext", _("search"));
    $action->lay->set("first", "true");
  } else {
    $action->lay->set("vtext", $vtext);
    $action->lay->set("first", "false");
  }
  $action->lay->set("Result", false);

  $sfam = $action->getParam("DEFAULT_FAMILY");
  $fam = new_Doc($dbaccess, $sfam);
  $action->lay->set("icon", $fam->getIcon());

  if ($vtext=="") return;

  $filter = array();
//    if ($ws!=1) $filter[] = "(us_lname ~* '^".$vtext."') OR ( us_society ~* '^".$vtext."' )";
  if ($ws!=1) $filter[] = "(title ~* '^".$vtext."')";
  $rq = getChildDoc($dbaccess, 0, 0, 25, $filter, $action->user->id, "LIST", $sfam, true, "title");
  $cu = array();
  foreach ($rq as $k => $v) {
    $cu[] = array( "id" => $v->id, "fabzone" => $v->faddbook_card, "resume" => $v->viewdoc($v->faddbook_resume));
  }
  if (count($cu)>0) $action->lay->set("Result", true);
  $action->lay->setBlockData("Contacts", $cu);

}
?>
