<?php
// ---------------------------------------------------------------
// $Id: viewbodycard.php,v 1.2 2002/07/25 16:41:38 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/Attic/viewbodycard.php,v $
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

include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");
include_once("FDL/Class.DocValue.php");

include_once("Class.TableLayout.php");
include_once("Class.QueryDb.php");
include_once("Class.QueryGen.php");
include_once("FDL/freedom_util.php");
include_once("VAULT/Class.VaultFile.php");

// -----------------------------------
// -----------------------------------
function viewbodycard(&$action) {
  // -----------------------------------

  // GetAllParameters
  $docid = GetHttpVars("id");
  $abstract = (GetHttpVars("abstract",'N') == "Y");// view doc abstract attributes
  $props = (GetHttpVars("props",'Y') == "Y"); // view doc properties
  $target = GetHttpVars("target","_self");
  $ulink = (GetHttpVars("ulink",'Y') == "Y"); // add url link


  // Set the globals elements

  $dbaccess = $action->GetParam("FREEDOM_DB");



  $action->lay->Set("cursor",$ulink?"crosshair":"inherit");
  

  $doc = new Doc($dbaccess, $docid);




  $kf=0; // number of files

  $frames= array();
  

  if ($props) {
    $action->lay->SetBlockData("PROP",array(array("boo"=>1)));
  }
    if ($abstract){
      // only 3 properties for abstract mode
      $listattr = $doc->GetAbstractAttributes();
    } else {
      $listattr = $doc->GetAttributes();
    
    }
    

  $nattr = count($listattr); // attributes list count


  $k=0; // number of frametext
  $v=0;// number of value in one frametext
  $nbimg=0;// number of image in one frametext
  $currentFrameId="";

  $changeframe=false; // is true when need change frame
  $tableframe=array();
  $tableimage=array();
  $vf = new VaultFile($dbaccess, "FREEDOM");
  for ($i=0; $i < $nattr + 1; $i++)
    {

      //------------------------------
      // Compute value elements
      if ($i < $nattr)
	{
	  
	  $value = chop($doc->GetValue($listattr[$i]->id));
	 

	  if ($value != "") // to define when change frame
	    {
	      if ( $currentFrameId != $listattr[$i]->frameid) {
		if ($currentFrameId != "") $changeframe=true;
	      }
	    }
	}


      //------------------------------
      // change frame if needed

      if (($i == $nattr) ||  // to generate final frametext
	  $changeframe)
	{
	  $changeframe=false;
	  if (($v+$nbimg) > 0) // one value detected
	    {
				      
	      $frames[$k]["frametext"]="[TEXT:".$doc->GetLabel($currentFrameId)."]";
	      $frames[$k]["rowspan"]=$v+1; // for images cell
	      $frames[$k]["TABLEVALUE"]="TABLEVALUE_$k";

	      $action->lay->SetBlockData($frames[$k]["TABLEVALUE"],
					 $tableframe);
	      $frames[$k]["IMAGES"]="IMAGES_$k";
	      $action->lay->SetBlockData($frames[$k]["IMAGES"],
					 $tableimage);
	      unset($tableframe);
	      unset($tableimage);
	      $tableframe=array();
	      $tableimage=array();
	      $k++;
	    }
	  $v=0;
	  $nbimg=0;
	}


      //------------------------------
      // Set the table value elements
      if ($i < $nattr)
	{
      
	  if (($value != "") && ($listattr[$i]->visibility != "H"))
	    {
		
	      $currentFrameId = $listattr[$i]->frameid;

	      // print values
	      switch ($listattr[$i]->type)
		{
	      
		case "image": 
		  
		  $tableimage[$nbimg]["imgsrc"]=$doc->GetHtmlValue($listattr[$i],$value,$target,$ulink);
		break;
		
		
		case "file": 
		  
		  $tableframe[$v]["value"]=$doc->GetHtmlValue($listattr[$i],$value,$target,$ulink);
		$tfile[$kf]["file"]=$listattr[$i]->labeltext;
		$tfile[$kf]["attrid"]=$listattr[$i]->id;
		$kf++;
		break;
		
		default : 
		  $tableframe[$v]["value"]=$doc->GetHtmlValue($listattr[$i],$value,$target,$ulink);
		break;
		
		}


	
	      // print name except image (printed otherthere)
	      if ($listattr[$i]->type != "image")
		{
		  $tableframe[$v]["name"]=$action->text($doc->GetLabel($listattr[$i]->id));
		  $v++;
		}
	      else
		{
		  $tableimage[$nbimg]["imgalt"]=$action->text($doc->GetLabel($listattr[$i]->id));
		  $nbimg++;
		}

	      
	    }
	}
  
    }

  // Out



  // unused menu items
  //$tmenuaccess[$kdiv]["vmenuitem9"]=0;

  $action->lay->SetBlockData("TABLEBODY",$frames);
  




}


?>
