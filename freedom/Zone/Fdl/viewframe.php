<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: viewframe.php,v 1.15 2003/11/17 10:38:23 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: viewframe.php,v 1.15 2003/11/17 10:38:23 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/viewframe.php,v $
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



// Compute frame values to be inserted in a specific layout
// -----------------------------------
function viewframe(&$action) {
  // -----------------------------------
    
    // GetAllParameters
      $docid = GetHttpVars("id");
  $frameid = strtolower(GetHttpVars("frameid"));
  $abstract = (GetHttpVars("abstract",'N') == "Y");// view doc abstract attributes
  $target = GetHttpVars("target","_self");
  $ulink = (GetHttpVars("ulink",'2') ); // add url link
    
  if ($ulink == "N") $ulink = false;
    
    // Set the globals elements
      
      
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  $action->lay->Set("cursor",$ulink?"crosshair":"inherit");

  $doc = new Doc($dbaccess, $docid);
  
  
  $listattr = $doc->GetNormalAttributes(); // get frame attribute also
    
    
    
    
    
  $tval = array();
  while (list($k,$v) = each($listattr)) {
    
    
    if ($v->fieldSet->id != $frameid) continue;


    $action->lay->set("flabel",$v->fieldSet->labelText);

    $value = chop($doc->GetValue($v->id));
    
    $goodvalue=((($value != "") || ( $v->type=="array")) && 
		($v->mvisibility != "H") && ($v->mvisibility != "O") && (! $v->inArray()));  
    if ($goodvalue)   {	 
	$htmlvalue=$doc->GetHtmlValue($v,$value,$target,$ulink);
    } else $htmlvalue="";
    if ($htmlvalue == "") continue;
    if ($v->mvisibility == "O") continue;
    if ($v->inArray() ) continue;
    //------------------------------
      // Set the table value elements
	if ($v->mvisibility != "H")	{
   	
	  // don't see  non abstract if not
	    if (( !$abstract) || ($v->isInAbstract)) {
	      $tval[$k]["wvalue"]=($v->type=="array")?"1%":"30%";  // width
	      $tval[$k]["ndisplay"]=($v->type=="array")?"none":"";  // display alabel ?
	     
	      $tval[$k]["alabel"]=  $v->labelText;;
	      $tval[$k]["avalue"]=  $htmlvalue;
	    }
	  
	}
    
    
    
  }
  
  //dont'see frame label is no one value
  if (count($tval) > 0) { 
   
    $action->lay->setBlockData("FIELDSET",array(array("zou")));
    $action->lay->setBlockData("FVALUES",$tval);
  }
  
  
  
  
  
  
  
}


?>
