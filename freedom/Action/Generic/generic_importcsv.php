<?php
// ---------------------------------------------------------------
// $Id: generic_importcsv.php,v 1.6 2002/12/10 16:15:18 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_importcsv.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2002
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




include_once("FDL/Class.Dir.php");
include_once("FDL/import_file.php");
include_once("GENERIC/generic_util.php"); 


// -----------------------------------
function generic_importcsv(&$action) {
  // -----------------------------------
  global $HTTP_POST_FILES;

  // Get all the params      
  $id=GetHttpVars("id");
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $policy = GetHttpVars("policy","add"); 
  $category = GetHttpVars("category"); 







  if (isset($HTTP_POST_FILES["vcardfile"]))    
    {
      // importation 
      $vcardfile = $HTTP_POST_FILES["vcardfile"]["tmp_name"];
      
    } else {      
      $vcardfile = GetHttpVars("file"); 
    }

  $fdoc = fopen($vcardfile,"r");
  if (! $fdoc) $action->exitError(_("no csv import file specified"));
  $dir = new Dir($dbaccess, getDefFld($action));

  $tvalue=array();

  $tabadd = array(); // memo each added person
  $tabdel = array(); // memo each deleted person


  while ($data = fgetcsv ($fdoc, 1000, ";")) {
    {
	 
      $num = count ($data);
      if ($num < 1) continue;
      switch ($data[0]) {
	
      case "DOC":
	$doc =csvAddDoc($action,$dbaccess, $data);
	if (!$doc) continue;

	
	      
	      

	// add in each selected category
	if (is_array($category)) {
	  reset($category);
		
	  while(list($k,$v) = each($category)) {
		  
	    $catg = new Dir($dbaccess, $v);
	    $catg->AddFile($doc->id);
	  }
	}
	// duplicate policy
	  
	switch ($policy)
	  {
	  case "add":
	    $doc->PostModify();
	    $tabadd[] = array("id"=>$doc->id,
			      "title"=>$doc->title);
	    break;
	  case "update":


	    $doc->PostModify();
	    $tabadd[] = array("id"=>$doc->id,
			      "title"=>$doc->title);
	    $ldoc = $doc->GetDocWithSameTitle();
	    while(list($k,$v) = each($ldoc)) {
	      $err = $v->delete(); // delete all double (if has permission)
	      $tabdel[] = array("id"=>$v->id,
				"title"=>$v->title);
	    }	
	    break;
	  case "keep":
	    $ldoc = $doc->GetDocWithSameTitle();
	    if (count($ldoc) ==  0) {
	      $doc->PostModify();
	      $tabadd[] = array("id"=>$doc->id,
				"title"=>$doc->title);
	    } else {
	      // delete the new added doc
	      $doc->delete();
	    }
	    break;
	  }


	    

	break;  
      }
    }
  }
       

  fclose ($fdoc);

  $action->lay->SetBlockData("ADDED",$tabadd);
  $action->lay->SetBlockData("DELETED",$tabdel);
    
}


?>
