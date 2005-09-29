<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: faddbook_prefered.php,v 1.2 2005/09/29 16:29:12 marc Exp $
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
    $cc = new_Doc($dbaccess, $v);
    $cu[] = array( "id" => $cc->id, 
		   "resume" => $cc->viewDoc($cc->faddbook_resume),
		   "icon" => $cc->getIcon(),
		   "title" => ucwords(strtolower($cc->title)),
		   "jstitle" => addslashes(ucwords(strtolower($cc->title))) );
  }
  $action->lay->setBlockData("Contacts", $cu);
}
?>
