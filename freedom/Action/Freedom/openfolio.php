<?php
/**
 * Open port folio document
 *
 * @author Anakeen 2000 
 * @version $Id: openfolio.php,v 1.6 2005/06/28 08:37:46 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */




include_once("FDL/Class.Doc.php");



// -----------------------------------
// -----------------------------------
function openfolio(&$action) {
// -----------------------------------
  // Set the globals elements

  $docid = GetHttpVars("id",0);        // document to edit
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $folio = new_Doc($dbaccess,$docid);
  $action->lay->Set("dirid", $folio->initid);
  $action->lay->Set("docid", $docid);
  $action->lay->Set("title", $folio->title);
  


}
?>
