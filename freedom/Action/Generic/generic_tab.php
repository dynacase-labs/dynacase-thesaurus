<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: generic_tab.php,v 1.12 2003/08/18 15:47:03 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: generic_tab.php,v 1.12 2003/08/18 15:47:03 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_tab.php,v $
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
include_once("FDL/Lib.Dir.php");

include_once("FDL/freedom_util.php");  
include_once("GENERIC/generic_util.php");
include_once("GENERIC/generic_list.php");





// -----------------------------------
function generic_tab(&$action) {
  // -----------------------------------

  

  // Get all the params      
  $keyword=GetHttpVars("keyword"); // keyword to search
  $dirid=GetHttpVars("catg",0); // folder where search
  $tab=GetHttpVars("tab", 1); // tab index

  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  $famid = getDefFam($action);
  $fdoc = new DocFam($dbaccess,$famid);
  if ($dirid == 0) {
    if ($fdoc->cfldid > 0) {
      $dirid=$fdoc->cfldid;
    } else {
      $dirid=$fdoc->dfldid;
    }
  }

  // hightlight the selected part (ABC, DEF, ...)
  $tabletter=array("", "ABC","DEF", "GHI","JKL","MNO","PQRS","TUV","WXYZ");



  $dir = new Doc($dbaccess, $dirid);

  // control open
  if ($dir->defDoctype=='S') $aclctrl="execute";
  else $aclctrl="open";
  if (($err=$dir->Control($aclctrl)) != "") $action->exitError($err);


  $sdoc = createDoc($dbaccess,5); // new DocSearch


  $sdoc->doctype = 'T';// it is a temporary document (will be delete after)


  if ($dir->id == $fdoc->dfldid)   {
    $sdoc->title = sprintf(_("%s all "),$tabletter[$tab] );
    $sdirid=0; // search in all DB
  }  else {
    $sdoc->title = sprintf("%s %s ",$tabletter[$tab],$dir->title );
    $sdirid=$dir->id;
  }



 

  $sdoc->Add();


  $sqlfilter[]= "locked != -1";
   $sqlfilter[]= "doctype!='C'";
  $sqlfilter[] = "usefor = 'N'";

  if ($tabletter[$tab]!="") $sqlfilter[]="title ~* '^[".$tabletter[$tab]."].*'";



  $query = getSqlSearchDoc($dbaccess,$sdirid,$famid,$sqlfilter);


  $sdoc-> AddQuery($query);
  

  setHttpVar("tab", $tab);
  setHttpVar("dirid",$sdoc->id );
  setHttpVar("catg",$dirid );

  generic_list($action);
  //  redirect($action,GetHttpVars("app"),"GENERIC_LIST&tab=$tab&dirid=".$sdoc->id."&catg=$dirid");
  
  
}


?>