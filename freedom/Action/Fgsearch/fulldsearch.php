<?php
/**
 * Full Text Search document
 *
 * @author Anakeen 2007
 * @version $Id: fulldsearch.php,v 1.1 2007/10/17 04:47:32 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

include_once("FREEDOM/fullsearch.php");  
include_once("FDL/modcard.php");  


/**
 * Fulltext Search document 
 * @param Action &$action current action
 * @global keyword Http var : word to search in any values
 * @global famid Http var : restrict to this family identioficator
 * @global start Http var : page number 
 * @global dirid Http var : search identificator
 */
function fulldsearch(&$action) {

  $famid=GetHttpVars("famid",0);


  $dbaccess = $action->GetParam("FREEDOM_DB");


  if ($famid>0) {
    $fdoc=new_doc($dbaccess,$famid);
    if (! $fdoc->isAffected()) $action->exitError(sprintf(_("Family %s not exist"),$famid));
    $search=createTmpDoc($dbaccess,16);
    $search->setValue("se_famid",$famid);
    $search->setValue("se_latest","yes");

    setPostVars($search);

    $err=$search->Add();

    if ($err!="") $action->exitError($err);
    $search->SpecRefresh();

    $action->lay = new Layout(getLayoutFile("FREEDOM","fullsearch.xml"),$action);
    setHttpVar("dirid",$search->id);
    fullsearch($action);
  }

}
?>