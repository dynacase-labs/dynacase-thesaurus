<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: usercard_importvcard.php,v 1.13 2003/08/18 15:47:03 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: usercard_importvcard.php,v 1.13 2003/08/18 15:47:03 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Usercard/usercard_importvcard.php,v $
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
include_once("FDL/Class.UsercardVcard.php");
include_once("GENERIC/generic_util.php");


// -----------------------------------
function usercard_importvcard(&$action) {
  // -----------------------------------
  global $HTTP_POST_FILES;

  // Get all the params      
  $id=GetHttpVars("id");
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $policy = GetHttpVars("policy","add"); 
  $category = GetHttpVars("category"); 
  $privacity = GetHttpVars("privacity","R"); 





  $action->lay->Set("CR","");
  $vcard_import = new UsercardVcard();

  if (isset($HTTP_POST_FILES["vcardfile"]))    
    {
      // importation 
      $vcardfile = $HTTP_POST_FILES["vcardfile"]["tmp_name"];
      
    } else {      
      $vcardfile = GetHttpVars("file"); 
    }
  if (! $vcard_import-> Open($vcardfile)) $action->exitError(_("no vcard file specified"));
  $dir = new Doc($dbaccess, getDefFld($action));

  $tvalue=array();

  $tabadd = array(); // memo each added person
  $tabdel = array(); // memo each deleted person
  while ( $vcard_import-> Read($tvalue))
    {
	 
      if (count($tvalue) > 0)
	{
	  // Add new contact card
	  $classid=getDefFam($action);
	  $doc = createDoc($dbaccess, $classid );

	  if (! $doc) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document"),$classid));
	      


	      
	  $doc->Add();



	  // set privacity
	      $doc->setvalue("US_PRIVCARD",$privacity);
	      
	  while(list($k,$v) = each($tvalue)) 
	    {
	      $doc->setvalue($k,$v);
	    }
	  $doc->Modify();
	  $dir->AddFile($doc->id);
	      

	  // add in each selected category
	      if (is_array($category)) {
		reset($category);
		
		while(list($k,$v) = each($category)) {
		  
		  $catg = new Doc($dbaccess, $v);
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


	    }

	}
      $vcard_import-> Close();


      $action->lay->SetBlockData("ADDED",$tabadd);
      $action->lay->SetBlockData("DELETED",$tabdel);
    
}


?>
