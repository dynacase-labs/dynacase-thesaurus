<?php
/**
 * Emailing
 *
 * @author Anakeen 2005
 * @version $Id: fdl_pubpreview.php,v 1.1 2005/05/03 16:55:22 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */




include_once("FDL/mailcard.php");
include_once("FDL/modcard.php");

/**
 * Preview of each document to be printed
 * @param Action &$action current action
 * @global docid Http var : folder id (generaly an action)
 */
function fdl_pubpreview(&$action) {

  // GetAllParameters

  $docid = GetHttpVars("id");
  $action->lay->set("dirid",$docid);
  
}


?>
