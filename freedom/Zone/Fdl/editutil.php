<?php

// ---------------------------------------------------------------
// $Id: editutil.php,v 1.29 2003/05/15 13:48:08 eric Exp $
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
function getHtmlInput(&$doc, &$oattr, $value, $index="") {
  global $action;


  $docid=intval($doc->id);
  if ($docid== 0) intval($docid=$doc->fromid);
  $attrtype=$oattr->type;
  $attrid=$oattr->id;
  $attrin='_'.$oattr->id; // for js name => for return values from client
  $attridk=$oattr->id.$index;
  if ($oattr->inArray()) {
    if ($index == -1) $attrin.='[-1]';
    else $attrin.='[]';
  }
  $visibility=$oattr->mvisibility;

 
  $idisabled = " disabled readonly title=\""._("read only")."\" ";
  $input="";
		
  if ($value == "") {
    $value = GetHttpVars($attrid); 
  }

  if ($visibility == "H") {
    $input="<input  type=\"hidden\" name=\"".$attrin."\" value=\"".chop(htmlentities(stripslashes($value)))."\"";    
    $input .= " id=\"".$attridk."\" "; 		      
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
	  $vid=$reg[2];
	  $fname = "<A target=\"$attrid\" href=\"".
	    GetParam("CORE_BASEURL").
	    "app=FDL&action=EXPORTFILE&vid=$vid&docid=$docid&attrid=$attrid&index=$index\" title=\"{$info->name}\">";
	  // put image
	  
	  $fname.="<IMG align=\"absbottom\" width=\"30\" SRC=\"";
	  $fname .= GetParam("CORE_BASEURL").
	    "app=FDL&action=EXPORTFILE&vid=$vid&docid=".$docid."&attrid=".$attrid."&index=$index";
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
      $input .="<input name=\"".$attrin."\" type=\"hidden\" value=\"".$value."\">";
      $input .="<input $oc class=\"autoresize\" accept=\"image/*\" size=15 type=\"file\" name=\"_UPL".$attrin."\"";
      $input .= " id=\"".$attridk."\" "; 
      if (($visibility == "R")||($visibility == "S")) $input .=$idisabled;
      $input .= " > "; 
      break;
		      
      //같같같같같같같같같같같같같같같같같같같같
    case "file": 
      if (ereg ("(.*)\|(.*)", $value, $reg)) {
			  
	$dbaccess = $action->GetParam("FREEDOM_DB");
	$vf = new VaultFile($dbaccess, "FREEDOM");
	if ($vf -> Show ($reg[2], $info) == "") {
	  $vid=$reg[2];
	  $fname = "<A target=\"$attrid\" href=\"".
	    $action->GetParam("CORE_BASEURL").
	    "app=FDL&action=EXPORTFILE&vid=$vid&docid=$docid&attrid=$attrid&index=$index\">";
	  $fname .= $info->name;
	  $fname .= "</A>";
	}
	else $fname=_("error in filename");
      }
      else $fname=_("no filename");
		      
      $input = "<span class=\"FREEDOMText\">".$fname."</span><BR>";
		      
      // input 
      $input .="<input name=\"".$attrin."\" type=\"hidden\" value=\"".$value."\">";
      $input .="<input $oc class=\"autoresize\" size=15 type=\"file\" name=\"_UPL".$attrin."\" value=\"".chop(htmlentities($value))."\"";
      $input .= " id=\"".$attridk."\" "; 
      if (($visibility == "R")||($visibility == "S")) $input .=$idisabled;
      $input .= " > "; 
      break;
		      
      //같같같같같같같같같같같같같같같같같같같같
    case "longtext": 
      $expid="exp".$attrid;
      $input="<textarea $oc wrap=\"virtual\" onclick=\"this.rows=9;document.getElementById('$expid').style.display='';\"  class=\"autoresize\" rows=2 name=\"".
	$attrin."\" ";
      $input .= " id=\"".$attridk."\" "; 
      if (($visibility == "R")||($visibility == "S")) $input .=$idisabled;
      $input .= " >".
	htmlentities(stripslashes($value)).
	"</textarea>".
	"<input id=\"$expid\" style=\"display:none\" type=\"button\" onclick=\"document.getElementById('$attrid').rows=2;this.style.display='none'\" value=\"&Delta;\">";
    
      break;
      //같같같같같같같같같같같같같같같같같같같같
    case "textlist": 

      $input="<textarea $oc class=\"autoresize\" rows=2 name=\"".
	$attrin."\" ";
      $input .= " id=\"".$attridk."\" "; 
      if (($visibility == "R")||($visibility == "S")) $input .=$idisabled;
      $input .= " >\n".
	htmlentities(stripslashes(str_replace("<BR>","\n",$value))).
	"</textarea>";
      break;
      //같같같같같같같같같같같같같같같같같같같같
    case "array": 

      $lay = new Layout("FDL/Layout/editarray.xml", $action);
      $ta = $doc->attributes->getArrayElements($attrid);
      $talabel=array();
      $tilabel=array();
      $tvattr = array();

      // get default values
      $ddoc = createDoc($doc->dbaccess, $doc->fromid);
      $tad = $ddoc->attributes->getArrayElements($attrid);


      while (list($k, $v) = each($ta)) {
	$talabel[] = array("alabel"=>($v->visibility=="H")?"":$v->labelText);
	$tilabel[] = array("ilabel"=>getHtmlInput($doc,$v,$ddoc->getValue($tad[$k]->id),-1));
	$tvattr[]=array("bvalue" => "bvalue_$k",
			"attrid" => $v->id);
	
	$tval[$k]=explode("\n",$doc->getValue($k));
	$nbitem=count($tval[$k]);
	$tivalue=array();
	for ($i=0;$i<$nbitem;$i++) {
	  $tivalue[]=array("ivalue"=>$tval[$k][$i]);
	}
	$lay->setBlockData("bvalue_$k",$tivalue);
      }
      $lay->setBlockData("TATTR",$talabel);
      $lay->setBlockData("IATTR",$tilabel);
      $lay->setBlockData("VATTR",$tvattr);
      $lay->set("attrid",$attrid);
      $lay->set("caption",$oattr->labelText);
     
      $lay->set("footspan",count($ta));

      reset($tval);
      $nbitem= count(current($tval));
      $tvattr = array();
      for ($k=0;$k<$nbitem;$k++) {
	$tvattr[]=array("bevalue" => "bevalue_$k");
	reset($ta);
	$tivalue=array();
	while (list($ka, $va) = each($ta)) {
	  

	  $tivalue[]=array("eivalue"=>getHtmlInput($doc,$va,$tval[$ka][$k],$k));
	}
	$lay->setBlockData("bevalue_$k",$tivalue);
      }
      $lay->setBlockData("EATTR",$tvattr);
      

    
		      
      $input =$lay->gen(); 
      break;
		      
      //같같같같같같같같같같같같같같같같같같같같
			
    case "enumlist": 
      $input="<select size=3 multiple name=\"".$attrin."[]\""; 
      
      $input .= " id=\"".$attridk."\" "; 
      if (($visibility == "R")||($visibility == "S")) $input .=$idisabled;
      $input.= ">";

      reset($oattr->enumlabel);
      $tvalues = explode("\n",$value);

      while (list($k, $v) = each($oattr->enumlabel)) {
	if (in_array($k, $tvalues)) $selected = "selected";
	else $selected="";
	$input.="<option $selected value=\"$k\">$v</option>"; 

      }
      $input.="<option  style=\"display:none\"  value=\" \"></option>"; 
     
      $input .= "</select> "; 
      $input.="<input type=\"button\" value=\"&times;\"".
	" title=\""._("clear inputs")."\"".
	" onclick=\"unselectInput('$attrid')\">";
    
      break;      
     
    case "enum": 
      $input="<select $multiple name=\"".$attrin."\""; 
      $input .= " id=\"".$attridk."\" "; 
      if (($visibility == "R")||($visibility == "S")) $input .= $idisabled;
      $input.= ">";

      reset($oattr->enum);

      while (list($k, $v) = each($oattr->enumlabel)) {

	if ($k == $value) $selected = "selected";
	else $selected="";
	$input.="<option $selected value=\"$k\">$v</option>"; 
      }
     
    
      break;      
		      

		      
      //같같같같같같같같같같같같같같같같같같같같
			
    case "date": 
      $input="<input size=10 type=\"text\"  name=\"".$attrin."\" value=\"".chop(htmlentities($value))."\"";
      $input .= " id=\"".$attridk."\" "; 

      if (($visibility == "R")||($visibility == "S")) $input .= $idisabled; 
      else  if ($doc->usefor != 'D') $input .=" disabled "; // always but default

      $input .= " >&nbsp;"; 
      if (!(($visibility == "R")||($visibility == "S"))) {
	$input.="<input type=\"button\" value=\"&#133;\"".
	  " title=\""._("date picker")."\" onclick=\"show_calendar(event,'".$attrid."')\"".
	  ">";
      }
      break;      
		      
      //같같같같같같같같같같같같같같같같같같같같
    case "password" : 
      // don't see the value
      $input="<input $oc class=\"autoresize\" type=\"password\" name=\"".$attrin."\" value=\""."\"";
      $input .= " id=\"".$attridk."\" "; 


      if (($visibility == "R")||($visibility == "S")) $input .= $idisabled;
		      
      $input .= " > "; 
      break;
      //같같같같같같같같같같같같같같같같같같같같
    default : 
    
      $input="<input $oc class=\"autoresize\" type=\"text\" name=\"".$attrin."\" value=\"".chop(htmlentities(stripslashes($value)))."\"";
    
      $input .= " id=\"".$attridk."\" "; 


      if (($visibility == "R")||($visibility == "S")) $input .= $idisabled;
		      
      $input .= " > "; 
      break;
		      
    }
  if  ($visibility != "S") {
    if (($oattr->phpfunc != "") && ($oattr->type != "enum") && ($oattr->type != "enumlist") ) {
      if (ereg("list",$attrtype, $reg)) $ctype="multiple";
      else $ctype="single";
      $input.="<input type=\"button\" value=\"&#133;\"".
	" title=\""._("choose inputs")."\"".
	" onclick=\"sendmodifydoc(event,".$docid.
	",'".$attrid."','$ctype','$index')\">";

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
	    " onclick=\"clearInputs([$jarg],'$index')\">";
	}
      } 
    } 	else if ($oattr->type == "date") {
      $input.="<input type=\"button\" value=\"&times;\"".
	" title=\""._("clear inputs")."\"".
	" onclick=\"clearInputs(['$attrid'],'$index')\">";
      
    }
		
    if ($oattr->elink != "") {
      $url= elinkEncode($oattr->elink);
      $input.="<input type=\"button\" value=\"+\"".
	" title=\""._("add inputs")."\"".
	" onclick=\"subwindowm(300,500,'$attrid','$url')\">";
    }
  }

  return $input;
  
  
  
  
}

function elinkEncode( $link) {
  // -----------------------------------
    
   
    
  $urllink="";
  for ($i=0; $i < strlen($link); $i++) {
    switch ($link[$i]) {
      
    case '%' :
   
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
      break;

    case "{" :
      $i++;

	  
      $sattrid="";
      while ($link[$i] != '}' ) {
	$sattrid.= $link[$i];
	$i++;
      }
      //	  print "attr=$sattrid";
	  

      $ovalue = GetParam($sattrid,
			 getFamIdFromName(GetParam("FREEDOM_DB"),$sattrid));

      $urllink.=$ovalue;
	  
	  
	
      break;

    default:
      $urllink.=$link[$i];
    }
  }
    
  return ($urllink);
    
}
?>
