<?php

/**
 * FDL Forum edition action
 *
 * @author Anakeen 2000 
 * @version $Id: fdl_forumaddentry.php,v 1.1 2007/10/11 15:46:17 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

function fdl_forumedit(&$action) {

  $docid  = GetHttpVars("docid", -1);
  $linkid = GetHttpVars("linkid", -1);
  $did    = GetHttpVars("did", -1);
  $text   = GetHttpVars("text", "");

  $dbaccess = GetParam("FREEDOM_DB");

  if ($docid>0 && getTDoc($dbaccess, $docid)) {





  }

}


?>
