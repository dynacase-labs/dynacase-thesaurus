<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Method.DocImg.php,v 1.4 2003/12/10 16:50:30 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: Method.DocImg.php,v 1.4 2003/12/10 16:50:30 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Method.DocImg.php,v $
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



  
  
var $defaultview= "FDL:VIEWIMGCARD";
	
var $views = array("photo" => array("kind" => "VCONS",
				    "text" => "photo only",
				    "zone" => "FDL:VIEWIMGCARD:T"),
		   "default" => array("kind" => "VCONS",
				      "text" => "default view",
				      "zone" => "FDL:VIEWBODYCARD"),
		   "editdef" => array("kind" => "VEDIT",
				       "text" => "default edit",
				       "zone" => "FDL:EDITBODYCARD"));

// -----------------------------------
function viewimgcard($target="_self",$ulink=true,$abstract=false) {
  // -----------------------------------


  $nbimg=0;// number of image


  $listattr = $this->GetNormalAttributes();


  $tableimage=array();
  $vf = new VaultFile($this->dbaccess, "FREEDOM");

  // view all (and only) images

  while (list($i,$attr) = each($listattr)) {

   
    $value = chop($this->GetValue($i));

    //------------------------------
    // Set the table value elements
      
    if (($value != "") && ($attr->visibility != "H"))	{
		

      // print values
      switch ($attr->type)   {
	      
      case "file": 
		  

      case "image": 
		  
	$tableimage[$nbimg]["imgsrc"]=$this->GetHtmlValue($attr,$value,$target,$ulink);
	if (ereg ("(.*)\|(.*)", $value, $reg)) {		 
	  // reg[1] is mime type
	  $tableimage[$nbimg]["type"]=$reg[1];
	  if ($vf -> Show ($reg[2], $info) == "") $fname = $info->name;
	  else $fname=_("no filename");
	  $tableimage[$nbimg]["name"]=$fname;
	}
	break;
		
		
	
		
      }	      
	    
    }
  
  }

  // Out


  $this->lay->SetBlockData("TABLEIMG",	 $tableimage);

}


function PostModify() {
  $this->SetValue("IMG_TITLE",$this->vault_filename("IMG_FILE"));
}
?>