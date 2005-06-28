<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_preview.php,v 1.8 2005/06/28 08:37:46 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: freedom_preview.php,v 1.8 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_preview.php,v $
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

include_once("FDL/duplicate.php");
include_once("FDL/modcard.php");


// -----------------------------------
// -----------------------------------
function freedom_preview(&$action) {
  // -----------------------------------
  
  $docid = GetHttpVars("id",0);
  $classid=GetHttpVars("classid",0);


  $dbaccess = $action->GetParam("FREEDOM_DB");

  if ($docid > 0) {
    $doc = new_Doc($dbaccess, $docid);

    $action->lay->Set("TITLE",$doc->title);
    $ndoc= duplicate($action, 0, $docid, true); // temporary document
    
    $ndoc->modify();
  } else {
    // new doc
    $ndoc = createDoc($dbaccess, $classid);
    if (! $ndoc) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document"),$classid));
    
    $ndoc->doctype='T';
    $err = $ndoc-> Add();
    if ($err != "")  $action->ExitError($err);
    
  }
  SetHttpVar("id", $ndoc->id);
  $err = modcard($action, $ndocid); // ndocid change if new doc

   
  $tdoc = new_Doc($dbaccess, $ndocid);
  $tdoc->modify();
  //if ($err != "")  $action-> ExitError($err);

}

?>
