<?php
/**
 * Generate Layout to edit array (table)
 *
 * @author Anakeen 2005
 * @version $Id: editarray.php,v 1.2 2005/06/28 08:37:46 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");

include_once("FDL/freedom_util.php");
include_once("FDL/editutil.php");



// Compute value to be inserted in a specific layout
// -----------------------------------
function editarray(&$action) {
  // -----------------------------------

  // GetAllParameters
  $docid = GetHttpVars("id",0);
  $classid = GetHttpVars("classid");
  $arrayid = strtolower(GetHttpVars("arrayid"));
  $vid = GetHttpVars("vid"); // special controlled view

  // Set the globals elements

  $dbaccess = $action->GetParam("FREEDOM_DB");

  if ($docid == 0) $doc = createDoc($dbaccess, $classid);
  else $doc = new_Doc($dbaccess, $docid);

  if (($vid != "") && ($doc->cvid > 0)) {
    // special controlled view
    $cvdoc= new_Doc($dbaccess, $doc->cvid);
    $tview = $cvdoc->getView($vid);
      if ($tview)  $doc->setMask($tview["CV_MSKID"]);
  }
  

  $oattr=$doc->getAttribute($arrayid);    
  getLayArray($action->lay,$doc,$oattr);
  
}


?>
