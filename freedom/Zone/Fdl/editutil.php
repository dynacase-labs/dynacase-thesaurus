<?php

// ---------------------------------------------------------------
// $Id: editutil.php,v 1.5 2002/08/06 16:52:34 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/editutil.php,v $
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

//
// ---------------------------------------------------------------
include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");
include_once("VAULT/Class.VaultFile.php");


// -----------------------------------
function getHtmlInput(&$action, $docid, $attrid, $attrtype, $visibility, $value) {
	
  $input="";
		
  if ($value == "") {
    $value = GetHttpVars($attrid); 
  }

  if ($visibility == "H") {
    $input="<input  type=\"hidden\" name=\"_".$attrid."\" value=\"".chop(htmlentities(stripslashes($value)))."\"";    
    $input .= " id=\"".$attrid."\" "; 		      
    $input .= " > "; 
    return $input;
  }

  // output change with type
  switch ($attrtype)
    {
		      
      //같같같같같같같같같같같같같같같같같같같같
    case "image": 
      $input="<IMG align=\"absbottom\" width=\"30\" SRC=\"";
    if ($value != "")				  {
			
      $efile = $action->GetParam("CORE_BASEURL").
	 "app=".$action->parent->name."&action=EXPORTFILE&docid=".$docid."&attrid=".$attrid;
      $input .=$efile;
    }
    else	  // if no image force default image
      $input .= 
	$action-> GetParam("FREEDOM_DEFAULT_IMAGE");		
    $input .= "\">";
		      
    // input 
    $input .="<input class=\"autoresize\" accept=\"image\" size=15 type=\"file\" name=\"_".$attrid."\" value=\"".chop(htmlentities($value))."\"";
    $input .= " id=\"".$attrid."\" "; 
    if ($visibility == "R") $input .=" disabled ";
    $input .= " > "; 
    break;
		      
    //같같같같같같같같같같같같같같같같같같같같
    case "file": 
      if (ereg ("(.*)\|(.*)", $value, $reg)) {
			  
	$dbaccess = $action->GetParam("FREEDOM_DB");
	$vf = new VaultFile($dbaccess, "FREEDOM");
	if ($vf -> Show ($reg[2], $info) == "") $fname = $info->name;
	else $fname=_("error in filename");
      }
      else $fname=_("no filename");
		      
      $input = "<span class=\"FREEDOMText\">".$fname."</span><BR>";
		      
      // input 
      $input .="<input class=\"autoresize\" size=15 type=\"file\" name=\"_".$attrid."\" value=\"".chop(htmlentities($value))."\"";
      $input .= " id=\"".$attrid."\" "; 
      if ($visibility == "R") $input .=" disabled ";
      $input .= " > "; 
      break;
		      
      //같같같같같같같같같같같같같같같같같같같같
    case "longtext": 
      $input="<textarea wrap=\"virtual\" onclick=\"this.rows=10\" class=\"autoresize\" rows=2 name=\"_".
	 $attrid."\" ";
    $input .= " id=\"".$attrid."\" "; 
    if ($visibility == "R") $input .=" disabled ";
    $input .= " >".
       chop(htmlentities(stripslashes($value))).
       "</textarea>";
    break;
    //같같같같같같같같같같같같같같같같같같같같
    case "textlist": 
      $input="<textarea class=\"autoresize\" rows=2 name=\"_".
	 $attrid."\" ";
    $input .= " id=\"".$attrid."\" "; 
    if ($visibility == "R") $input .=" disabled ";
    $input .= " >".
       chop(htmlentities(stripslashes(str_replace("<BR>","\n",$value)))).
       "</textarea>";
    break;
		      
		      
    //같같같같같같같같같같같같같같같같같같같같
			
    case "enum": 
      $input="<input class=\"autoresize\" type=\"text\"  name=\"_".$attrid."\" value=\"".chop(htmlentities($value))."\"";
    $input .= " id=\"".$attrid."\" "; 
    if ($visibility == "R") $input .=" disabled ";
    $input .= " > "; 
    $input.="<input type=\"button\" value=\"".
       _("...")."\" onClick=\"sendmodifydoc(event,".$docid.
       ",'".$attrid."','single')\">";
    break;      
		      
    //같같같같같같같같같같같같같같같같같같같같
			
    case "enumlist": 
      $input="<textarea class=\"autoresize\" rows=2 name=\"_".
	 $attrid."\" ";
    $input .= " id=\"".$attrid."\" "; 
    if ($visibility == "R") $input .=" disabled ";
    $input .= " >".
       chop(htmlentities(stripslashes(str_replace("<BR>","\n",$value)))).
       "</textarea> ";
    $input.="<input type=\"button\" value=\"".
       _("...")."\" onClick=\"sendmodifydoc(event,".$docid.
       ",'".$attrid."','multiple')\">";
    break;
		      
    //같같같같같같같같같같같같같같같같같같같같
			
    case "date": 
      $input="<input class=\"autoresize\" type=\"text\"  name=\"_".$attrid."\" value=\"".chop(htmlentities($value))."\"";
    $input .= " id=\"".$attrid."\" "; 
    if ($visibility == "R") $input .=" disabled ";
    $input .= " >&nbsp;"; 
    $input.="<input type=\"button\" value=\"".
       _("...")."\" ".
       "onClick=\"show_calendar(event,'".$attrid."')\"".
       ">";
    break;      
		      
    //같같같같같같같같같같같같같같같같같같같같
    case "password" : 
      // don't see the value
      $input="<input  class=\"autoresize\" type=\"password\" name=\"_".$attrid."\" value=\""."\"";
    $input .= " id=\"".$attrid."\" "; 


    if ($visibility == "R") $input .=" disabled ";
		      
    $input .= " > "; 
    break;
    //같같같같같같같같같같같같같같같같같같같같
    default : 
    
      $input="<input class=\"autoresize\" type=\"text\" name=\"_".$attrid."\" value=\"".chop(htmlentities(stripslashes($value)))."\"";
    
    $input .= " id=\"".$attrid."\" "; 


    if ($visibility == "R") $input .=" disabled ";
		      
    $input .= " > "; 
    break;
		      
    }
		
		
  return $input;
  
  
  
  
}
?>
