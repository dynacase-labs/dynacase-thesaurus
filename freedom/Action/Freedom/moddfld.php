<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: moddfld.php,v 1.6 2003/08/18 15:47:03 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: moddfld.php,v 1.6 2003/08/18 15:47:03 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/moddfld.php,v $
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
include_once("FDL/Class.Dir.php");
include_once("FDL/Class.DocAttr.php");
include_once("FDL/freedom_util.php");  



// -----------------------------------
function moddfld(&$action) {
  // -----------------------------------
    
    
    
  // Get all the params      
  $docid=GetHttpVars("docid");
  $current = (GetHttpVars("current","N")=="Y");
  $newfolder = (GetHttpVars("autofolder","N")=="Y");
  $fldid=  GetHttpVars("dfldid");
    
  if ( $docid == 0 ) $action->exitError(_("the document is not referenced: cannot apply defaut folder"));
    
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  
  // initialise object
  $doc = new Doc($dbaccess,$docid);

  // create folder if auto 
  if ($newfolder) {
    $dir = createDoc($dbaccess, getFamIdFromName($dbaccess,"DIR"));
    $err=$dir->Add();
    if ($err!="") $action->exitError($err);
    $dir->setValue("BA_TITLE",sprintf(_("root for %s"),$doc->title));
    $dir->setValue("BA_DESC",_("default folder"));
    $dir->setValue("FLD_ALLBUT","1");
    $dir->setValue("FLD_FAM",$doc->title."\n"._("folder")."\n"._("search"));
    $dir->setValue("FLD_FAMIDS",$doc->id."\n".getFamIdFromName($dbaccess,"DIR").
		   "\n".getFamIdFromName($dbaccess,"SEARCH"));
    $dir->Modify();
    $fldid=$dir->id;
  }

  if ($current) $doc->cfldid = $fldid; 
  else $doc->dfldid = $fldid; // new default folder
  
  
  // test object permission before modify values (no access control on values yet)
  $doc->lock(true); // enabled autolock
  $err=$doc-> CanUpdateDoc();
  if ($err != "") $action-> ExitError($err);
  
  $doc-> Modify();
  
  
  $doc->unlock(true); // disabled autolock
  
  
  
  redirect($action,"FDL","FDL_CARD&id=$docid",$action->GetParam("CORE_STANDURL"));
}




?>
