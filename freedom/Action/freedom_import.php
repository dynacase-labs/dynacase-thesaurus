<?php
// ---------------------------------------------------------------
// $Id: freedom_import.php,v 1.1 2001/11/09 09:41:14 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Attic/freedom_import.php,v $
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
// $Log: freedom_import.php,v $
// Revision 1.1  2001/11/09 09:41:14  eric
// gestion documentaire
//
// Revision 1.3  2001/09/10 16:51:45  eric
// ajout accessibilté objet
//
// Revision 1.2  2001/06/22 09:46:12  eric
// support attribut multimédia
//
// Revision 1.1  2001/06/19 16:13:20  eric
// importation de fichier
//
//
// ---------------------------------------------------------------
include_once("FREEDOM/Class.Doc.php");
include_once("FREEDOM/Class.DocAttr.php");
include_once("FREEDOM/Class.DocValue.php");
include_once("FREEDOM/Class.FreedomLdap.php");















// -----------------------------------
function freedom_import(&$action) {
  // -----------------------------------
  global $HTTP_POST_FILES;

  // Get all the params      
  $id=GetHttpVars("id");
  $dbaccess = $action->GetParam("FREEDOM_DB");

  // Set Css
  $cssfile=$action->GetLayoutFile("freedom.css");
  $csslay = new Layout($cssfile,$action);
  $action->parent->AddCssCode($csslay->gen());

  // ------------------------------
  // construction of selection category
  $bdfreedom = new Doc($dbaccess);
  $categories = $bdfreedom->GetCategories();


  $tablecatg= array();
  while(list($k,$v) = each($categories) )
    {

      if ($categories[$k] != "")
	{
	  $tablecatg[$k]["catgvalue"]=$categories[$k];
	  $tablecatg[$k]["catgtextvalue"]=$categories[$k];
	}

    }

  $action->lay->SetBlockData("SELECTOPTION",$tablecatg); 

  $action->lay->Set("CR","");
  if (isset($HTTP_POST_FILES["tsvfile"]))    
    {
      // importation 
      
      $conv_type= GetHttpVars("conv_type"); 
      include_once("FREEDOM/Class.FreedomImport".$conv_type.".php");

      $class = "FreedomImport".$conv_type;
      $vcard_import = new $class();
      $vcard_import-> Open($HTTP_POST_FILES["tsvfile"]["tmp_name"]);
      
      $oldap=new FreedomLdap($action);
	      $policy = GetHttpVars("policy"); 
      $tvalue=array();
      $infocr="<ol>";
      while ( $vcard_import-> Read($tvalue))
	{
	  if (count($tvalue) > 0)
	    {
	      // Add new freedom card
	      $bdfreedom = new Doc($dbaccess);
	      $bdfreedom->owner = $action->user->id;
	      $bdfreedom-> Add();
	      $docid = $bdfreedom-> id;
	      
	      $bdvalue = new DocValue($dbaccess);
	      $bdvalue -> docid = $docid;

	      // Add values
	      $bdfreedomattr = new DocAttr($dbaccess);

	      while(list($k,$v) = each($tvalue)) 
		{
		  $bdvalue->attrid = $k;
		  $bdvalue->value = $v;
		  $bdvalue ->Modify();
		}
	      // update title
	      $ofreedom = new Doc($dbaccess, $docid);
	      $ofreedom->visibility= GetHttpVars("visibility"); 
	      $ofreedom->category = GetHttpVars("category"); 

	      $ofreedom->title =  GetTitle($dbaccess,$docid);

	      // duplicate policy
	      switch ($policy)
		{
		case "add":
		  $idsamefreedom=0;
		  break;
		case "update":

		  
		  $idsamefreedom=$ofreedom-> GetFreedomFromTitle($ofreedom->title);
		  if ($idsamefreedom > 0)
		    {
		      $osamefreedom = new Doc($dbaccess, $idsamefreedom);
		      if ( ($action->HasPermission("ADMIN")) ||
			   ($osamefreedom->owner == $action->user->id) ||
			   ($osamefreedom->visibility == "W"))
			{		
			  //delete POSGRES osamefreedom
			  $osamefreedom-> Delete();

			  // delete LDAP entry			  
			  $oldap=new FreedomLdap($action);
			  $oldap-> Delete($idsamefreedom);
			  $idsamefreedom=0;
			}
			   
		    }
		  break;
		case "keep":
		  $idsamefreedom=$ofreedom-> GetFreedomFromTitle($ofreedom->title);
		  break;
		}


	      if ($idsamefreedom == 0)
		{
		  // change card properties
		  $ofreedom-> Modify();
		  if ($ofreedom->visibility != "N")
		    $oldap->update($docid);
		  $infocr .= "<li>".$ofreedom->title."</li>";
		}

	    }

	}
      $vcard_import-> Close();
      $infocr .= "</ol>";
      $action->lay->Set("CR",$infocr);
    }
}


?>
