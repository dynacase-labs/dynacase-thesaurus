<?php
/**
 * Freedom Address Book
 *
 * @author Anakeen 2000
 * @version $Id: faddbook_speedsearch.php,v 1.5 2005/10/02 14:27:59 marc Exp $
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

  $sfam = GetHttpVars("dfam", $action->getParam("DEFAULT_FAMILY"));
  $action->lay->set("dfam", $sfam);
  $fam = new_Doc($dbaccess, $sfam);
  $action->lay->set("icon", $fam->getIcon());
  $action->lay->set("Result", false);
  $action->lay->set("bCount", false);
  $action->lay->set("Count", "-");

  if ($vtext=="") return;

  $filter = array();
//    if ($ws!=1) $filter[] = "(us_lname ~* '^".$vtext."') OR ( us_society ~* '^".$vtext."' )";
  if ($ws!=1) $filter[] = "(title ~* '^".$vtext."')";
  else $filter[] = "(title ~* '".$vtext."')";
  $rq = getChildDoc($dbaccess, 0, 0, 25, $filter, $action->user->id, "LIST", $sfam, true, "title");
  $cu = array();
  foreach ($rq as $k => $v) {
    $pzabstract = (isset($v->faddbook_resume)?$v->faddbook_resume:$v->defaultabstract);
    $pzcard = (isset($v->faddbook_card)?$v->faddbook_card:$v->defaultview);
    $cu[] = array( "id" => $v->id, "title" => $v->title, "fabzone" => $pzcard, "resume" => $v->viewdoc($pzabstract));
  }
  if (count($cu)>0) {
    $action->lay->set("Result", true);
    $action->lay->set("bCount", true);
    $action->lay->set("Count", count($cu));
  }
  usort($cu, "sortmya");
  $action->lay->setBlockData("Contacts", $cu);

}
function sortmya($a, $b) {
  return strcmp($a["title"], $b["title"]);
}
?>
