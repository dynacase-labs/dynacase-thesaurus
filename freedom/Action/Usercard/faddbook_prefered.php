<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: faddbook_prefered.php,v 1.1 2005/09/28 15:36:56 marc Exp $
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

  $cu = array();
  foreach ($tc as $k => $v) {
    if ($v=="") continue;
    $cc = getTDoc($dbaccess, $v);
    $cu[] = array( "id" => $cc["id"], 
		   "icon" => Doc::GetIcon($cc["icon"]),
		   "title" => ucwords(strtolower($cc["title"])) );
  }
  $action->lay->setBlockData("Contacts", $cu);
}
?>
