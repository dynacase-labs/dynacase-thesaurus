<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: editframe.php,v 1.15 2003/08/18 15:47:04 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: editframe.php,v 1.15 2003/08/18 15:47:04 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/editframe.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2001
// O*O  Anakeen development team
//  O   dev@anakeen.com
// ---------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------

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
  

  // Set the globals elements


  $dbaccess = $action->GetParam("FREEDOM_DB");

  if ($docid == 0) $doc = createDoc($dbaccess, $classid);
  else $doc = new Doc($dbaccess, $docid);

  
  $listattr = $doc->GetNormalAttributes(true);
    
    

    
  $thval = array();
  $tval = array();
  while (list($k,$v) = each($listattr)) {


    if (($v->fieldSet->id != $frameid) ) continue;
    if ($v->inArray() ) continue;
    if ($v->mvisibility == "I" ) continue;// not editable

    $action->lay->set("flabel",$v->fieldSet->labelText);

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
      
    }
	
      
      
  }

  $action->lay->setBlockData("FVALUES",$tval);
  $action->lay->setBlockData("FHIDDENS",$thval);
  if (count($tval) > 0) {
    
    $action->lay->setBlockData("FRAME",array(array("bou")));

  }
    
  
}


?>
