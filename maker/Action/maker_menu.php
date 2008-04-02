<?php
/**
 * Context menu view for maker application
 *
 * @author Anakeen 2008
 * @version $Id: maker_menu.php,v 1.1 2008/04/02 11:44:39 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage MAKER
 */
 /**
 */


include_once("FDL/popupdoc.php");
include_once("FDL/popupdocdetail.php");
function maker_menu(&$action) {
 
  $type = GetHttpVars("type");

  // -------------------- Menu menu ------------------

  $surl=$action->getParam("CORE_STANDURL");
  $tlink=array();
  if ($type=='file') {
  $tlink=array(
	       "new"=>array("descr"=>_("New project"),
			       "url"=>"$surl&app=MAKER&action=MAKER_EDITCREATEPROJECT",
			       "confirm"=>"false",
			       "tconfirm"=>"",
			       "target"=>"_self",
			       "visibility"=>POPUP_ACTIVE,
			       "submenu"=>"",
			       "barmenu"=>"false"));
  }




  popupdoc($action,$tlink,$tsubmenu);
}


?>