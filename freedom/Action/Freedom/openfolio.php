<?php
/**
 * Open port folio document
 *
 * @author Anakeen 2000 
 * @version $Id: openfolio.php,v 1.5 2004/09/09 12:57:43 eric Exp $
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

  $folio = new Doc($dbaccess,$docid);
  $action->lay->Set("dirid", $folio->initid);
  $action->lay->Set("docid", $docid);
  $action->lay->Set("title", $folio->title);
  


}
?>
