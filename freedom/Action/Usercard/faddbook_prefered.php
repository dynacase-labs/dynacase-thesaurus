<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: faddbook_prefered.php,v 1.3 2005/10/02 12:34:29 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once("FDL/freedom_util.php");
include_once("FDL/Class.Doc.php");

function faddbook_prefered(&$action) {

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $dbaccess = $action->getParam("FREEDOM_DB");

  $cpref = $action->getParam("FADDBOOK_PREFERED", "");
  $tc = explode("|", $cpref);

  $sfam = $action->getParam("DEFAULT_FAMILY");
  $fam = new_Doc($dbaccess, $sfam);
  $action->lay->set("icon", $fam->getIcon());

  $cu = array();
  foreach ($tc as $k => $v) {
    if ($v=="") continue;
    $cc = new_Doc($dbaccess, $v);
    $cu[] = array( "id" => $cc->id, 
		   "resume" => $cc->viewDoc($cc->faddbook_resume),
		   "icon" => $cc->getIcon(),
		   "title" => ucwords(strtolower($cc->title)),
		   "fabzone" => $cc->faddbook_card,
		   "jstitle" => addslashes(ucwords(strtolower($cc->title))) );
  }
  usort($cu,  "sortmya");
  $action->lay->setBlockData("Contacts", $cu);
}
function sortmya($a, $b) {
  return strcmp($a["title"], $b["title"]);
}

?>
