<?php
/**
 * Image document
 *
 * @author Anakeen 2000 
 * @version $Id: Method.DocImg.php,v 1.7 2004/08/05 09:47:20 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



  
  
var $defaultview= "FDL:VIEWIMGCARD";
	


var $cviews=array("FDL:VIEWIMGCARD:T");

// -----------------------------------
function viewimgcard($target="_self",$ulink=true,$abstract=false) {
  // -----------------------------------


  $nbimg=0;// number of image

  $this->viewattr($target,$ulink,$abstract);
  $this->viewprop($target,$ulink,$abstract);

  $listattr = $this->GetNormalAttributes();

  $tableimage=array();
  $vf = newFreeVaultFile($this->dbaccess);

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