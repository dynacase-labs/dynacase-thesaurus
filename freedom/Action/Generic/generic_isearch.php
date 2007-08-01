<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: generic_isearch.php,v 1.11 2007/08/01 09:28:11 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: generic_isearch.php,v 1.11 2007/08/01 09:28:11 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_isearch.php,v $
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


include_once("FDL/Class.DocSearch.php");
include_once("FDL/freedom_util.php");  
include_once("GENERIC/generic_util.php");  


include_once("FDL/Class.DocRel.php");



// -----------------------------------
function generic_isearch(&$action) {
  // -----------------------------------
   

  // Get all the params      
  $docid=GetHttpVars("id"); // id doc to search
  $famid=GetHttpVars("famid",0); // restriction of search
  $viewone=GetHttpVars("viewone"); // 
  
  $dbaccess = $action->GetParam("FREEDOM_DB");

  if (($famid !== 0) && (! is_numeric($famid))) {
    $famid=getFamIdFromName($dbaccess,$famid);  
  }
  if ($docid == "") $action->exitError(_("related search aborted : no parameter found"));


  $doc = new_Doc($dbaccess, $docid);

  $sdoc = createTmpDoc($dbaccess,2); //new Folder
  $sdoc->title = sprintf(_("related documents of %s"),$doc->title );

  $sdoc->Add();  
  $idocid=$doc->initid;
  $rdoc=new DocRel($dbaccess,$idocid);
  $rdoc->sinitid=$idocid;
  $trel=$rdoc->getIRelations();
  $tids=array();
  foreach ($trel as $k=>$v) {
      $tids[$v["sinitid"]]=$v["sinitid"];      
  }
  $sdoc->QuickInsertMSDocId($tids);
 
  redirect($action,"FREEDOM","FREEDOM_VIEW&viewone=$viewone&dirid=".$sdoc->id);
  // redirect($action,GetHttpVars("app"),"GENERIC_LIST&dirid=".$sdoc->id."&famid=$famid&catg=0");
  
  
}


?>