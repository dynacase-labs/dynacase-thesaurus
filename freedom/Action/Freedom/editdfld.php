<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: editdfld.php,v 1.6 2005/06/28 08:37:46 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: editdfld.php,v 1.6 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/editdfld.php,v $
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


function editdfld(&$action) {
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);
  $current = (GetHttpVars("current","N")=="Y");
  


  $action->lay->Set("docid",$docid);

  $action->lay->Set("TITLE",_("change default folder"));
 


  $doc= new_Doc($dbaccess,$docid);

  $action->lay->Set("doctitle",$doc->title);
  $sqlfilters=array();
  if ($current) {
    $fldid=$doc->cfldid;
    $action->lay->Set("TITLE",_("change current folder"));
    $action->lay->Set("current","Y");

    $tclassdoc = getChildDoc($dbaccess,$doc->dfldid,"0","ALL",$sqlfilters, $action->user->id, "TABLE",5);
    $tclassdoc = array_merge($tclassdoc,getChildDoc($dbaccess,$doc->dfldid,"0","ALL",$sqlfilters, $action->user->id, "TABLE",2));
  } else {
    $fldid=$doc->dfldid;
    $action->lay->Set("TITLE",_("change default folder"));
    $action->lay->Set("current","N");
    $sqlfilters[]="doctype='D'";
    $tclassdoc = getChildDoc($dbaccess,0,"0","ALL",$sqlfilters, $action->user->id, "TABLE",2);
  }

  $selectclass=array();
  if (is_array($tclassdoc)) {
    while (list($k,$pdoc)= each ($tclassdoc)) {
     
      $selectclass[$k]["idpdoc"]=$pdoc["id"];
      $selectclass[$k]["profname"]=$pdoc["title"];
	
      $selectclass[$k]["selected"]=($pdoc["id"]==$fldid)?"selected":"";
      
    }
  }


  $action->lay->Set("autodisabled",$current||($fldid>0)?"disabled":"");
  
  $action->lay->SetBlockData("SELECTFLD", $selectclass);
	  
      
    
}




?>
