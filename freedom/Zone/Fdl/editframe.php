<?php
/**
 * Generate Layout to edit frame (fieldset)
 *
 * @author Anakeen 2000 
 * @version $Id: editframe.php,v 1.20 2005/02/18 15:42:56 eric Exp $
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
function editframe(&$action) {
  // -----------------------------------

  // GetAllParameters
  $docid = GetHttpVars("id",0);
  $classid = GetHttpVars("classid");
  $frameid = strtolower(GetHttpVars("frameid"));
  $vid = GetHttpVars("vid"); // special controlled view

  // Set the globals elements


  $dbaccess = $action->GetParam("FREEDOM_DB");

  if ($docid == 0) $doc = createDoc($dbaccess, $classid);
  else $doc = new Doc($dbaccess, $docid);

  if (($vid != "") && ($doc->cvid > 0)) {
    // special controlled view
    $cvdoc= new Doc($dbaccess, $doc->cvid);
    $tview = $cvdoc->getView($vid);
      if ($tview)  $doc->setMask($tview["CV_MSKID"]);
  }
  
  $listattr = $doc->GetNormalAttributes();
    
    

    
  $thval = array();
  $tval = array();
  while (list($k,$v) = each($listattr)) {


    if (($v->fieldSet->id != $frameid) ) continue;
    if ($v->inArray() ) continue;
    if ($v->mvisibility == "I" ) continue;// not editable

    $action->lay->set("flabel",$v->fieldSet->labelText);
    $action->lay->set("frameid",$v->fieldSet->id);

    //------------------------------
    // Set the table value elements
    $value = chop($doc->GetValue($v->id));
    if ($docid == 0) {
      $value=$doc->GetValueMethod($value); // execute method for default values
    }
    if ( ($v->mvisibility == "H") || 
	 ($v->mvisibility == "R") ) {

      $thval[$k]["avalue"]=  getHtmlInput($doc,
					  $v, 
					  $value);

      // special case for hidden values
    } else {	
      $tval[$k]["alabel"]=  $v->labelText;
      if ($v->needed ) $tval[$k]["labelclass"]="FREEDOMLabelNeeded";
      else $tval[$k]["labelclass"]="FREEDOMLabel";
      $tval[$k]["avalue"]=  getHtmlInput($doc,
					 $v, 
					 $value);

      $tval[$k]["winput"]=($v->type=="array")?"1%":"30%";  // width
      $tval[$k]["NORMALROW"]="NORMALROW$k";		
      $tval[$k]["ARRAYROW"]="ARRAYROW$k";
      if ($v->type=="array") $action->lay->SetBlockData("ARRAYROW$k",array(array("zou")));
      else $action->lay->SetBlockData("NORMALROW$k",array(array("zou")));
      
    }
	
      
      
  }
  $action->lay->setBlockData("FVALUES",$tval);
  $action->lay->setBlockData("FHIDDENS",$thval);
  if (count($tval) > 0) {
    
    $action->lay->setBlockData("FRAME",array(array("bou")));

  }
    
  
}


?>
