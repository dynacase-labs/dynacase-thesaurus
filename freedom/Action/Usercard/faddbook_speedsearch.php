<?php
/**
 * Freedom Address Book
 *
 * @author Anakeen 2000
 * @version $Id: faddbook_speedsearch.php,v 1.2 2005/09/29 16:29:12 marc Exp $
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

  $fam = new_Doc($dbaccess, "IUSER");
  $action->lay->set("icon", $fam->getIcon());

  if ($vtext=="") return;

  $filter = array();
  if ($ws!=1) $filter[] = "(title ~* '^".$vtext."') OR ( us_society ~* '^".$vtext."' )";
  $rq = getChildDoc($dbaccess, 0, 0, 25, $filter, $action->user->id, "LIST", "USER", true, "title");
  $cu = array();
  foreach ($rq as $k => $v) {
     $t = $v->viewdoc($v->viewDoc($v->faddbook_resume));
    $cu[] = array( "resume" => "pas d'erreur ?");
  }
  if (count($cu)>0) $action->lay->set("Result", true);
  $action->lay->setBlockData("Contacts", $cu);

}
?>
