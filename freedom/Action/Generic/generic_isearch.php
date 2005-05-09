<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: generic_isearch.php,v 1.7 2005/05/09 16:23:16 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: generic_isearch.php,v 1.7 2005/05/09 16:23:16 eric Exp $
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





// -----------------------------------
function generic_isearch(&$action) {
  // -----------------------------------
   

  // Get all the params      
  $docid=GetHttpVars("id"); // id doc to search
  $famid=GetHttpVars("famid",0); // restriction of search
  
  $dbaccess = $action->GetParam("FREEDOM_DB");

  if (($famid !== 0) && (! is_numeric($famid))) {
    $famid=getFamIdFromName($dbaccess,$famid);  
  }
  if ($docid == "") $action->exitError(_("related search aborted : no parameter found"));


  $doc = new Doc($dbaccess, $docid);
  $tdoc = $doc->getRevisions("TABLE");
  $tfil=array();
  while (list($k,$v) = each($tdoc)) {
    $tfil[]="values ~ '[£|\n]".$v["id"]."[£|\n]'";
  }
  

  $sdoc = createDoc($dbaccess,5); //new DocSearch($dbaccess);
  $sdoc->doctype = 'T';// it is a temporary document (will be delete after)
  $sdoc->title = sprintf(_("related search of %s"),$doc->title );

  $sdoc->Add();
  

  $sqlfilter[]= "locked != -1";
  //  $sqlfilter[]= "doctype ='F'";
  //  $sqlfilter[]= "usefor != 'D'";
  $sqlfilter[]= "(".implode(") OR (",$tfil).")";

  $query=getSqlSearchDoc($dbaccess, 
			 0,  
			 $famid, 
			 $sqlfilter);
  $sdoc-> AddQuery($query);
  redirect($action,"FREEDOM","FREEDOM_VIEW&dirid=".$sdoc->id);
  // redirect($action,GetHttpVars("app"),"GENERIC_LIST&dirid=".$sdoc->id."&famid=$famid&catg=0");
  
  
}


?>