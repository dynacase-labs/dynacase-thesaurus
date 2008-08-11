<?php
/**
 * Execute search document from thesaurus
 *
 * @author Anakeen 2008
 * @version $Id: th_execsearch.php,v 1.1 2008/08/11 16:31:22 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage THESAURUS
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("THESAURUS/Lib.Thesaurus.php");
/**
 * Return document list
 * @param Action &$action current action
 * @global thid Http var : thesaurus document identificator to use
 * @global famid Http var : family document to search
 */
function th_execsearch(&$action) {
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $thid = GetHttpVars("thid");
  $fid = GetHttpVars("famid");


  phpinfo(INFO_VARIABLES);
  
}
?>