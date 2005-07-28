<?php
/**
 * Search document
 *
 * @author Anakeen 2000 
 * @version $Id: search.php,v 1.24 2005/07/28 16:47:51 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: search.php,v 1.24 2005/07/28 16:47:51 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/search.php,v $
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



include_once("FDL/Lib.Dir.php");

include_once("FDL/freedom_util.php");  



include_once("FDL/modcard.php");


/**
 * Search document 
 * @param Action &$action current action
 * @global keyword Http var : word to search in any values
 * @global famid Http var : restrict to this family identioficator
 * @global viewone Http var : (Y|N) if Y direct view document detail if only one returned
 */
function search(&$action) {
  // -----------------------------------

  $docid = GetHttpVars("id",0);
  $classid=GetHttpVars("classid",0);
  $keyword=GetHttpVars("_se_key",GetHttpVars("keyword")); // keyword to search
  $viewone=GetHttpVars("viewone"); // direct view if only one Y|N
  
  $dbaccess = $action->GetParam("FREEDOM_DB");

  if ($classid == 0) {
    if ($docid > 0) {
      $doc = new_Doc($dbaccess, $docid);
      $classid=$doc->fromid;
    }
    else {
      $classid=5;
      //      $action->exitError(_("kind of search is not defined"));
    }
  }
  // new doc
  $ndoc = createDoc($dbaccess, $classid);
  if (! $ndoc) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document"),$classid));
    
  if ($keyword != "") $ndoc->title=_("new search ").$keyword;
  else {
    $famid=GetHttpVars("_se_famid");
    if ($famid>0) {
      $fam=$ndoc->getTitle($famid);
      $ndoc->title=sprintf(_("search %s"),$fam);
    } else {
      $ndoc->title=sprintf(_("search result"));
    }
  }
  $ndoc->doctype='T';
  $ndoc->setValue("se_key",$keyword);
  $ndoc->setValue("se_latest","yes");
  $ndoc->setValue("se_famid",GetHttpVars("famid"));
  $err = $ndoc-> Add();
 
  if ($err != "")  $action->ExitError($err);

  SetHttpVar("id", $ndoc->id);
  $err = modcard($action, $ndocid); // ndocid change if new doc
    
  
  redirect($action,GetHttpVars("app"),"FREEDOM_VIEW&viewone=$viewone&dirid=".$ndoc->id,
	   $action->GetParam("CORE_STANDURL"));
  
  
}


?>