<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: generic_edit.php,v 1.21 2004/02/05 15:49:21 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


// ---------------------------------------------------------------
// $Id: generic_edit.php,v 1.21 2004/02/05 15:49:21 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_edit.php,v $
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

include_once("Class.QueryDb.php");
include_once("GENERIC/generic_util.php"); 

// -----------------------------------
function generic_edit(&$action) {
  // -----------------------------------

  // Get All Parameters
  $docid = GetHttpVars("id",0);        // document to edit
  $classid = GetHttpVars("classid",getDefFam($action)); // use when new doc or change class

  $dirid = GetHttpVars("dirid",0); // directory to place doc if new doc
  $usefor = GetHttpVars("usefor"); // default values for a document

  $vid = GetHttpVars("vid"); // special controlled view

  $action->lay->Set("vid", $vid);
  // Set the globals elements
  $dbaccess = $action->GetParam("FREEDOM_DB");
   


  if ($docid == 0)
    {
    if ($classid > 0) {
      $cdoc= new Doc($dbaccess,$classid);
      $action->lay->Set("TITLE", sprintf(_("creation %s"),$cdoc->title));
    } else {
      $action->lay->Set("TITLE",_("new card"));
    }
    if ($usefor=="D") $action->lay->Set("TITLE", _("default values"));
    if ($usefor=="Q") $action->lay->Set("TITLE", _("parameters values"));
    
      $action->lay->Set("editaction", $action->text("create"));
      $doc= createDoc($dbaccess,$classid);
      if (! $doc) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document"),$classid));
      if ($usefor!="") $doc->doctype='T';
    }
  else
    {    

      $doc= new Doc ($dbaccess,$docid);
     
      $err = $doc->lock(true); // autolock
      if ($err != "")   $action->ExitError($err);

      $classid = $doc->fromid;
      if (! $doc->isAlive()) $action->ExitError(_("document not referenced"));
  

      $action->lay->Set("TITLE", $doc->title);
      $action->lay->Set("editaction", $action->text("Validate"));
    }
    

 

  $action->lay->Set("iconsrc", $doc->geticon());
  
  if ($doc->fromid > 0) {
    $fdoc= $doc->getFamDoc();
    $action->lay->Set("FTITLE", $fdoc->title);
  } else {
    $action->lay->Set("FTITLE", _("no family"));
  }
  

  $action->lay->Set("id", $docid);
  $action->lay->Set("dirid", $dirid);

  // control view of special constraint button
  $action->lay->Set("boverdisplay", "none");
  
  if (GetHttpVars("viewconstraint")=="Y") {
    $action->lay->Set("bconsdisplay", "none");
    if ($action->user->id==1) $action->lay->Set("boverdisplay", ""); // only admin can do this
    
  } else {
    // verify if at least on attribute constraint
    
    $action->lay->Set("bconsdisplay", "none");
    $listattr = $doc->GetNormalAttributes();
    foreach ($listattr as $k => $v) {
      if ($v->phpconstraint != "")  {
	$action->lay->Set("bconsdisplay", "");
	break;
      }
  }
  }

 
  // information propagation
  $action->lay->Set("classid", $classid);
  $action->lay->Set("dirid", $dirid);
  $action->lay->Set("id", $docid);
    

}
?>
