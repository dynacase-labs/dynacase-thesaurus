<?php
// ---------------------------------------------------------------
// $Id: editwdoc.php,v 1.1 2003/02/25 09:53:09 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/editwdoc.php,v $
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


function editwdoc(&$action) {
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);
  $current = (GetHttpVars("current","N")=="Y");
  


  $action->lay->Set("docid",$docid);

 


  $doc= new Doc($dbaccess,$docid);

  $action->lay->Set("doctitle",$doc->title);
  $sqlfilters=array();
 
  $wid=$doc->wid;
  $sqlfilters[]="doctype='W'";
  $tclassdoc = getChildDoc($dbaccess,0,"0","ALL",$sqlfilters, $action->user->id, "TABLE",1);
  

  $selectclass=array();
  if (is_array($tclassdoc)) {
    while (list($k,$pdoc)= each ($tclassdoc)) {
     
      $selectclass[$k]["idpdoc"]=$pdoc["id"];
      $selectclass[$k]["profname"]=$pdoc["title"];
	
      $selectclass[$k]["selected"]=($pdoc["id"]==$wid)?"selected":"";
      
    }
  }



  
  $action->lay->SetBlockData("SELECTFLD", $selectclass);
	  
      
    
}




?>
