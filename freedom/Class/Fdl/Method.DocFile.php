
// ---------------------------------------------------------------
// $Id: Method.DocFile.php,v 1.1 2002/11/14 10:43:22 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Method.DocFile.php,v $
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



  
  
var $defaultview= "FDL:VIEWFILECARD";
		     

// -----------------------------------
function viewfilecard($target="_self",$ulink=true,$abstract=false) {
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
  $this->modify();
}