<?php
/**
 * Freedom Address Book
 *
 * @author Anakeen 2000
 * @version $Id: faddbook_speedsearch.php,v 1.6 2005/10/03 07:36:14 marc Exp $
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

  $searchuser = GetHttpVars("vsuser", 1);
  $action->lay->set("vsuser", $searchuser);
  $action->lay->set("USEL", ($searchuser==1?true:false));
  $searchsoc = GetHttpVars("ssoc", 0);
  $action->lay->set("ssoc", $searchsoc);
  $action->lay->set("SOCSEL", ($searchsoc==1?true:false));

  $sfam = GetHttpVars("dfam", $action->getParam("DEFAULT_FAMILY"));
  $action->lay->set("dfam", $sfam);
  $fam = new_Doc($dbaccess, $sfam);
  $action->lay->set("iconuser", $fam->getIcon());
  $famsoc = new_Doc($dbaccess, "SOCIETY");
  $action->lay->set("iconsociety", $famsoc->getIcon());
  $action->lay->set("Result", false);
  $action->lay->set("bCount", false);
  $action->lay->set("Count", "-");


  $searchfam = array();
  if ($searchuser==1) $searchfam[] = "USER";
  if ($searchsoc==1) $searchfam[] = "SOCIETY";

  if (count($searchfam)==0 || $vtext=="") return;

  $filter = array();
  $opf = ( $ws!=1 ? "^" : "" );
  $filter[] = "(title ~* '$opf".$vtext."')";
  $cu = array();
  foreach ($searchfam as $ks => $vs) {
    $rq = getChildDoc($dbaccess, 0, 0, 25, $filter, $action->user->id, "LIST", $vs, true, "title");
    foreach ($rq as $k => $v) {
      $pzabstract = (isset($v->faddbook_resume)?$v->faddbook_resume:$v->defaultabstract);
      $pzcard = (isset($v->faddbook_card)?$v->faddbook_card:$v->defaultview);
      $cu[] = array( "id" => $v->id, "title" => $v->title, "fabzone" => $pzcard, "resume" => $v->viewdoc($pzabstract));
    }
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
