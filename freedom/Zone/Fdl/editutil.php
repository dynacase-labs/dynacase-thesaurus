<?php

// ---------------------------------------------------------------
// $Id: editutil.php,v 1.13 2002/11/19 17:14:26 eric Exp $
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
function getHtmlInput(&$doc, &$oattr, $value) {
  global $action;

  $docid=intval($doc->id);
  if ($docid== 0) intval($docid=$doc->fromid);
  $attrtype=$oattr->type;
  $attrid=$oattr->id;
  $visibility=$oattr->visibility;

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

  $oc = " onchange=\"document.isChanged=true\" "; // use in "pleaseSave" js function

  // output change with type
  switch ($attrtype)
    {
		      
      //같같같같같같같같같같같같같같같같같같같같
    case "image": 
      if (ereg ("(.*)\|(.*)", $value, $reg)) {
			  
	$dbaccess = GetParam("FREEDOM_DB");
	$vf = new VaultFile($dbaccess, "FREEDOM");
	if ($vf -> Show ($reg[2], $info) == "") {
	  $fname = "<A target=\"$attrid\" href=\"".
	    GetParam("CORE_BASEURL").
	    "app=FDL&action=EXPORTFILE&docid=$docid&attrid=$attrid\" title=\"{$info->name}\">";
	  // put image
	  
	  $fname.="<IMG align=\"absbottom\" width=\"30\" SRC=\"";
	  $fname .= GetParam("CORE_BASEURL").
	 "app=FDL&action=EXPORTFILE&docid=".$docid."&attrid=".$attrid;
	  $fname .= "\">";

	  $fname .= "</A>";
	}
	else $fname=_("error in filename");
      }
      else {
	
	  
	$fname = $action->GetIcon("noimage.gif",_("no image"),30);
	 
	
      }

      $input =$fname;
   
		      
    // input 
    $input .="<input $oc class=\"autoresize\" accept=\"image/*\" size=15 type=\"file\" name=\"_".$attrid."\" value=\"".chop(htmlentities($value))."\"";
    $input .= " id=\"".$attrid."\" "; 
    if ($visibility == "R") $input .=" disabled ";
    $input .= " > "; 
    break;
		      
    //같같같같같같같같같같같같같같같같같같같같
    case "file": 
      if (ereg ("(.*)\|(.*)", $value, $reg)) {
			  
	$dbaccess = $action->GetParam("FREEDOM_DB");
	$vf = new VaultFile($dbaccess, "FREEDOM");
	if ($vf -> Show ($reg[2], $info) == "") {
	  $fname = "<A target=\"$attrid\" href=\"".
	    $action->GetParam("CORE_BASEURL").
	    "app=FDL&action=EXPORTFILE&docid=$docid&attrid=$attrid\">";
	  $fname .= $info->name;
	  $fname .= "</A>";
	}
	else $fname=_("error in filename");
      }
      else $fname=_("no filename");
		      
      $input = "<span class=\"FREEDOMText\">".$fname."</span><BR>";
		      
      // input 
      $input .="<input $oc class=\"autoresize\" size=15 type=\"file\" name=\"_".$attrid."\" value=\"".chop(htmlentities($value))."\"";
      $input .= " id=\"".$attrid."\" "; 
      if ($visibility == "R") $input .=" disabled ";
      $input .= " > "; 
      break;
		      
      //같같같같같같같같같같같같같같같같같같같같
    case "longtext": 
      $input="<textarea $oc wrap=\"virtual\" onclick=\"this.rows=10\" class=\"autoresize\" rows=2 name=\"_".
	 $attrid."\" ";
    $input .= " id=\"".$attrid."\" "; 
    if ($visibility == "R") $input .=" disabled ";
    $input .= " >".
       chop(htmlentities(stripslashes($value))).
       "</textarea>";
    
    break;
    //같같같같같같같같같같같같같같같같같같같같
    case "textlist": 
      $input="<textarea $oc class=\"autoresize\" rows=2 name=\"_".
	 $attrid."\" ";
    $input .= " id=\"".$attrid."\" "; 
    if ($visibility == "R") $input .=" disabled ";
    $input .= " >".
       chop(htmlentities(stripslashes(str_replace("<BR>","\n",$value)))).
       "</textarea>";
    break;
		      
		      
    //같같같같같같같같같같같같같같같같같같같같
			
    case "enum": 
      $input="<input $oc class=\"autoresize\" type=\"text\"  name=\"_".$attrid."\" value=\"".chop(htmlentities($value))."\"";
    $input .= " id=\"".$attrid."\" "; 
    if ($visibility == "R") $input .=" disabled ";
    $input .= " > "; 
    
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
    
    break;
		      
    //같같같같같같같같같같같같같같같같같같같같
			
    case "date": 
      $input="<input class=\"autoresize\" type=\"text\"  name=\"_".$attrid."\" value=\"".chop(htmlentities($value))."\"";
    $input .= " id=\"".$attrid."\" "; 
    $input .=" disabled "; // always
    $input .= " >&nbsp;"; 
    $input.="<input type=\"button\" value=\"&#133;\"".
       " title=\""._("date picker")."\" onclick=\"show_calendar(event,'".$attrid."')\"".
       ">";
    break;      
		      
    //같같같같같같같같같같같같같같같같같같같같
    case "password" : 
      // don't see the value
      $input="<input $oc class=\"autoresize\" type=\"password\" name=\"_".$attrid."\" value=\""."\"";
    $input .= " id=\"".$attrid."\" "; 


    if ($visibility == "R") $input .=" disabled ";
		      
    $input .= " > "; 
    break;
    //같같같같같같같같같같같같같같같같같같같같
    default : 
    
      $input="<input $oc class=\"autoresize\" type=\"text\" name=\"_".$attrid."\" value=\"".chop(htmlentities(stripslashes($value)))."\"";
    
    $input .= " id=\"".$attrid."\" "; 


    if ($visibility == "R") $input .=" disabled ";
		      
    $input .= " > "; 
    break;
		      
    }
	
  if ($oattr->phpfunc != "") {
    if (ereg("list",$attrtype, $reg)) $ctype="multiple";
    else $ctype="single";
    $input.="<input type=\"button\" value=\"&#133;\"".
	  " title=\""._("choose inputs")."\"".
       " onclick=\"sendmodifydoc(event,".$docid.
       ",'".$attrid."','$ctype')\">";

    // clear button
    if (ereg("(.*)\((.*)\)\:(.*)", $oattr->phpfunc, $reg)) {
      
      $argids = split(",",$reg[3]);  // output args
      $arg = array();
      while (list($k, $v) = each($argids)) {
	if (strlen($v) > 1) $arg[$k]= strtolower(chop($v));
      }
      if (count($arg) > 0) {
	$jarg="'".implode("','",$arg)."'";
	$input.="<input type=\"button\" value=\"&times;\"".
	  " title=\""._("clear inputs")."\"".
	   " onclick=\"clearInputs([$jarg])\">";
      }
    }
  } 	
		
  if ($oattr->elink != "") {
    $url= elinkEncode($oattr->elink);
    $input.="<input type=\"button\" value=\"+\"".
	  " title=\""._("add inputs")."\"".
       " onclick=\"subwindowm(300,500,'edit','$url')\">";
  }


  return $input;
  
  
  
  
}

  function elinkEncode( $link) {
    // -----------------------------------
    
   
    
    $urllink="";
    for ($i=0; $i < strlen($link); $i++) {
      if ($link[$i] != "%") $urllink.=$link[$i];
      else {
	$i++;
	if ($link[$i+1] == "%") { 
	  // special link
	    
	  switch ($link[$i]) {
	    case "B": // baseurl	  
	      $urllink.=GetParam("CORE_BASEURL");
	      
	      break;
	    case "S": // standurl	  
	      $urllink.=GetParam("CORE_STANDURL");
	      
	      break;
	    case "I": // id	  
	      $urllink.=$this->id;
	      
	      break;
	    default:
	      
	      break;
	    }
	  $i++; // skip end '%'
	} else {
	  
	  $sattrid="";
	  while ($link[$i] != "%" ) {
	    $sattrid.= $link[$i];
	    $i++;
	  }
	  //	  print "attr=$sattrid";
	  
	  $sattrid=strtolower($sattrid);

	  $urllink.= "'+document.getElementById('$sattrid').value+'";
	  
	  
	}
      }
    }
    
    return ($urllink);
    
  }
?>
