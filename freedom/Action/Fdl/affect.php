<?php
/**
 * Functions to send document by email
 *
 * @author Anakeen 2000 
 * @version $Id: affect.php,v 1.1 2006/07/28 15:21:00 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("Class.MailAccount.php");


// -----------------------------------
function affect(&$action) {

  

  redirect($action,GetHttpVars("redirect_app","FDL"),
	   GetHttpVars("redirect_act","FDL_CARD&latest=Y&refreshfld=Y&id=".$doc->id),
	   $action->GetParam("CORE_STANDURL"));

}
?>