<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Method.DocFile.php,v 1.6 2004/08/05 09:47:20 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */





  
  
var $defaultview= "FDL:VIEWFILECARD";
		     

// -----------------------------------
function viewfilecard($target="_self",$ulink=true,$abstract=false) {
  // -----------------------------------


  $nbimg=0;// number of image


  $this->viewattr($target,$ulink,$abstract);
  $listattr[] = $this->GetAttribute("FI_FILE");


  $tableimage=array();
  $vf = newFreeVaultFile($this->dbaccess);

  // view all (and only) images

  while (list($i,$attr) = each($listattr)) {

  
    $value = chop($this->GetValue($attr->id));

    //------------------------------
    // Set the table value elements
      
    if (($value != "") && ($attr->visibility != "H"))	{
		

      // print values
      switch ($attr->type)   {
	      
      case "file": 
		  
	$tableimage[$nbimg]["imgsrc"]=$this->GetHtmlValue($attr,$value,$target,$ulink);
      if (ereg ("(.*)\|(.*)", $value, $reg)) {		 
	// reg[1] is mime type
	$tableimage[$nbimg]["type"]=$reg[1];
	if ($vf -> Show ($reg[2], $info) == "") {
	  $fname = $info->name;
	  $tableimage[$nbimg]["size"]=round($info->size / 1024,2);
	}
	else $fname=_("no filename");

	$tableimage[$nbimg]["name"]=$fname;
      }

      break;
		
		
	
		
      }	      
	    
    }
  
  }

  // Out


  $this->lay->SetBlockData("TABLEFILE",	 $tableimage);

}


function PostModify() {
  $this->SetValue("FI_TITLE",$this->vault_filename("FI_FILE"));
}
?>