<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: editutil.php,v 1.49 2003/10/09 12:08:43 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


// ---------------------------------------------------------------
// $Id: editutil.php,v 1.49 2003/10/09 12:08:43 eric Exp $
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

 $idocfamid=$oattr->format;



  $attrid=$oattr->id;
  $attrin='_'.$oattr->id; // for js name => for return values from client
  $attridk=$oattr->id.$index;
  if ($oattr->inArray()) {
    if ($index == -1) $attrin.='[-1]';
    else $attrin.='[]';
  }
  if (isset($oattr->mvisibility)) $visibility=$oattr->mvisibility;
  else $visibility=$oattr->visibility;
  if ($visibility == "I") return ""; // not editable attribute
 
  $idisabled = " style=\"background-color:".getParam("CORE_BGCOLORALTERN")."\" disabled readonly title=\""._("read only")."\" ";
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
      $expid="exp".$attridk;
      $input="<textarea $oc wrap=\"virtual\" onclick=\"this.rows=9;document.getElementById('$expid').style.display='';\"  class=\"autoresize\" rows=2 name=\"".
	$attrin."\" ";
      $input .= " id=\"".$attridk."\" "; 
      if (($visibility == "R")||($visibility == "S")) $input .=$idisabled;
      $input .= " >".
	htmlentities(stripslashes(str_replace("<BR>","\n",$value))).
	"</textarea>".
	"<input id=\"$expid\" style=\"display:none\" type=\"button\" onclick=\"document.getElementById('$attridk').rows=2;this.style.display='none'\" value=\"&Delta;\">";

      
    
      break;
      //같같같같같같같같같같같같같같같같같같같같
    case "htmltext": 
      $expid="exp".$attrid;
      $input="<textarea $oc  style=\"width:100%\" rows=\"20\"   name=\"".
	$attrin."\" ";
      $input .= " id=\"".$attridk."\" "; 
      if (($visibility == "R")||($visibility == "S")) $input .=$idisabled;
      $input .= " >".
	htmlentities(stripslashes($value)).
	"</textarea>";


      //      $input .= "<input type=\"button\" onclick=\"var editor$attridk = new HTMLArea('$attridk');editor$attridk.generate();\" value=\"Y\"></input>";
      $input .= "<script >var editor$attridk = new HTMLArea('$attridk');setTimeout(\"editor$attridk.generate()\",500)</script>";
    
      break;
      //같같같같같같같같같같같같같같같같같같같같
    case "idoc":
      //  printf("ici");
      if (($oattr->repeat) && (!$oattr->inArray())){ // old idoclist type
   
	//print_r($oattr);

      
      $layout = new Layout("FREEDOM/Layout/idoclist.xml",$action);
      $layout->Set("name","_$attrid"."[]");
      $layout->Set("name_attr","_$attrid");
      $layout->Set("famid",$idocfamid);
      $layout->Set("listidoc","listidoc_$attrid");



      $value=explode("\n",$value);
      //printf(sizeof($value));

      $tabxml=array();
      while (list($i,$xmlencode) = each($value)) {

	if ($xmlencode!=""){
	  $tabxml[$i]["xml"]=$xmlencode;
	
	  $temp=base64_decode($xmlencode);
	  $entete="<?xml version=\"1.0\" encoding=\"ISO-8859-1\" standalone=\"yes\" ?>";
	  $xml=$entete;
	  $xml.=$temp;
	  
	  $title=recup_argument_from_xml($xml,"title");//in freedom_util.php
	  $id_arg=recup_argument_from_xml($xml,"id_doc");
	  //strlen($oattr->LabelText);
	  //$tabxml[$i]["id"]="_$attrid".$i;
	 
	  $tabxml[$i]["id"]= $id_arg;
	  //printf(settype($id_arg,"int"));
	  $number=str_replace("_$attrid","",$id_arg);//recupere le numero de l'argument
	  $tabxml[$i]["titre"]=$number." : ".$title;
	}
      }
      $layout->Set("idframe","iframe_$attrid");
      $layout->SetBlockData("OPTION",$tabxml);
      $input=$layout->gen();    
      }


      else{//idoc normal
	//	printf("la");
	if($value!=""){
	  $temp=base64_decode($value);
	  $entete="<?xml version=\"1.0\" encoding=\"ISO-8859-1\" standalone=\"yes\" ?>";
	  $xml=$entete;
	  $xml.=$temp; 
	  $title=recup_argument_from_xml($xml,"title");//in freedom_util.php
	}
	
	$input.="<INPUT id=\"_" .$attridk."\" TYPE=\"hidden\"  name=$attrin value=\"".$value." \">$title </input>";
	$input.="<iframe name='iframe_$attridk' id='iframe_$attridk' style='display:none' height=200 width='100%' marginwidth=0 marginheight=0></iframe>";
	
	/*  $input.="<input type=\"button\" value=\"+->\"".
      " title=\""._("add inputs")."\"".
      " onclick=\"special_edit('_$attridk','$idocfamid','idoc','_$attridk');\">";*/
	
	$input.="<input type=\"button\" value=\"+\"".
	  " title=\""._("add inputs")."\"".
	  " onclick=\"subwindowm(800,800,'_$attridk','[CORE_STANDURL]&app=FREEDOM&action=FREEDOM_IEDIT');editidoc('_$attridk','_$attridk','$idocfamid','idoc');\">";
	
	/* $input.="<input type=\"button\" value=\"view\"".
      " title=\"voir\"".
      " onclick=\"subwindowm(400,400,'_$attridk','[CORE_STANDURL]&app=FREEDOM&action=VIEWICARD');viewidoc('_$attridk','$idocfamid')\">";
	*/
	$input.="<input type=\"button\" value=\"view_in_frame\"".
	  " title=\"voir dans une frame\"".
	  " onclick=\"viewidoc_in_frame('iframe_$attridk','_$attridk','$idocfamid')\">";
	
	$input.="<input type=\"button\" value=\"close frame\"".
	  " title=\"fermer la frame\"".
	  " onclick=\"close_frame('iframe_$attridk')\">";
      }
      
      break;
      

      //같같같같같같같같같같같같같같같같같같같같
    case "array": 

      $lay = new Layout("FDL/Layout/editarray.xml", $action);
      $ta = $doc->attributes->getArrayElements($attrid);
      $talabel=array();
      $tilabel=array();
      $tvattr = array();

      // get default values
      $ddoc = createDoc($doc->dbaccess, $doc->fromid==0?$doc->id:$doc->fromid);
      $tad = $ddoc->attributes->getArrayElements($attrid);


      while (list($k, $v) = each($ta)) {
	if ($v->mvisibility=="R") {
	  $v->mvisibility="H"; // don't see read attribute
	  $ta[$k]->mvisibility="H";
	}
	$talabel[] = array("alabel"=>($v->mvisibility=="H")?"":$v->labelText);
	$tilabel[] = array("ilabel"=>getHtmlInput($doc,$v,$ddoc->getValue($tad[$k]->id),-1));
	$tvattr[]=array("bvalue" => "bvalue_$k",
			"attrid" => $v->id);
	
	$tval[$k]=$doc->getTValue($k);
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
      if (count($tvattr) > 0) $lay->setBlockData("EATTR",$tvattr);
      

    
		      
      $input =$lay->gen(); 
      break;
		      
      //같같같같같같같같같같같같같같같같같같같같
			
 
     
    case "enum": 
      if (($oattr->repeat)&&(!$oattr->inArray())) { // enumlist
	$input="<select size=3 multiple name=\"".$attrin."[]\""; 
      
	$input .= " id=\"".$attridk."\" "; 
	if (($visibility == "R")||($visibility == "S")) $input .=$idisabled;
	$input.= ">";

	$enuml = $oattr->getenumlabel();
	$tvalues = explode("\n",$value);

	while (list($k, $v) = each($enuml)) {
	  if (in_array($k, $tvalues)) $selected = "selected";
	  else $selected="";
	  $input.="<option $selected value=\"$k\">$v</option>"; 

	}
	$input.="<option  style=\"display:none\"  value=\" \"></option>"; 
     
	$input .= "</select> "; 
	$input.="<input type=\"button\" value=\"&times;\"".
	  " title=\""._("clear inputs")."\"".
	  " onclick=\"unselectInput('$attrid')\">";
      } else {
	$input="<select $multiple name=\"".$attrin."\""; 
	$input .= " id=\"".$attridk."\" "; 
	if (($visibility == "R")||($visibility == "S")) $input .= $idisabled;
	$input.= ">";

	$enuml = $oattr->getenumlabel();

	while (list($k, $v) = each($enuml)) {

	  if ($k == $value) $selected = "selected";
	  else $selected="";
	  $input.="<option $selected value=\"$k\">$v</option>"; 
	}
      }
    
      break;      
		      

		      
      //같같같같같같같같같같같같같같같같같같같같
			
    case "color": 
      $input="<input size=7  style=\"background-color:$value\" type=\"text\"  name=\"".$attrin."\" value=\"".chop(htmlentities($value))."\"";
      $input .= " id=\"".$attridk."\" "; 

      if (($visibility == "R")||($visibility == "S")) $input .= $idisabled; 
      else  if ($doc->usefor != 'D') $input .=" disabled "; // always but default

      $input .= " >&nbsp;"; 
      if (!(($visibility == "R")||($visibility == "S"))) {
	$input.="<input id=\"col$attridk\" type=\"button\" value=\"&#133;\"".
	  " title=\""._("color picker")."\" onclick=\"colorPick.select(document.getElementById('$attridk'),'$attridk')\"".
	  ">";
      }
      break;      
		      
      //같같같같같같같같같같같같같같같같같같같같
			
    case "date": 
      $input="<input size=10  type=\"text\"  name=\"".$attrin."\" value=\"".chop(htmlentities($value))."\"";
      $input .= " id=\"".$attridk."\" "; 

      if (($visibility == "R")||($visibility == "S")) $input .= $idisabled; 
      else  if ($doc->usefor != 'D') $input .=" disabled "; // always but default

      $input .= " >&nbsp;"; 
      if (!(($visibility == "R")||($visibility == "S"))) {
	$input.="<input type=\"button\" value=\"&#133;\"".
	  " title=\""._("date picker")."\" onclick=\"show_calendar(event,'".$attridk."')\"".
	  ">";
      }
      break;      
		      
      //같같같같같같같같같같같같같같같같같같같같
			
    case "time": 
      $isDisabled="";
      if (($visibility == "R")||($visibility == "S")) $isDisabled =$idisabled;
      list($hh,$mm,$ss) = explode(":",$value);
      $input ="<input $isDisabled size=2 maxlength=2 onchange=\"chtime('$attridk')\" type=\"text\"  value=\"".$hh."\" id=\"hh".$attridk."\">:";
     
      $input.="<input $isDisabled size=2 maxlength=2 onchange=\"chtime('$attridk')\" type=\"text\"  value=\"".$mm."\"id=\"mm".$attridk."\">";
      

      $input.="<input  type=\"hidden\"  name=\"".$attrin."\" id=\"".$attridk."\" value=\"".$value."\">";

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
    
      if (($oattr->repeat)&&(!$oattr->inArray())) { // textlist
	 $input="<textarea $oc class=\"autoresize\" rows=2 name=\"".
	   $attrin."\" ";
	 $input .= " id=\"".$attridk."\" "; 
	 if (($visibility == "R")||($visibility == "S")) $input .=$idisabled;
	 $input .= " >\n".
	   htmlentities(stripslashes(str_replace("<BR>","\n",$value))).
	   "</textarea>";
      } else {
      $input="<input $oc class=\"autoresize\" type=\"text\" name=\"".$attrin."\" value=\"".chop(htmlentities(stripslashes($value)))."\"";
    
      $input .= " id=\"".$attridk."\" "; 


      if (($visibility == "R")||($visibility == "S")) $input .= $idisabled;
		      
      $input .= " > "; 
      }
      break;
		      
    }
  if  ($visibility != "S") {
    if (($oattr->phpfunc != "") && ($oattr->phpfile  != "") && ($oattr->type != "enum") && ($oattr->type != "enumlist") ) {
      if (ereg("list",$attrtype, $reg)) $ctype="multiple";
      else $ctype="single";
      $input.="<input type=\"button\" value=\"&#133;\"".
	" title=\""._("choose inputs")."\"".
	" onclick=\"sendEnumChoice(event,".$docid.
	",this,'$ctype')\">";

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
      
    }else if ($oattr->type == "color") {
      $input.="<input type=\"button\" value=\"&times;\"".
	" title=\""._("clear inputs")."\"".
	" onclick=\"clearInputs(['$attrid'],'$index')\">";      
    }else if ($oattr->type == "time") {
      $input.="<input type=\"button\" value=\"&times;\"".
	" title=\""._("clear inputs")."\"".
	" onclick=\"clearTime('$attridk')\">";      
    }
		
    if ($oattr->elink != "") {
      if (ereg('\[(.*)\](.*)', $oattr->elink, $reg)) {
	//print_r($reg);      
	$oattr->elink=$reg[2];
	$tabFunction=explode(":",$reg[1]);
	//	print_r($tabFunction);

	if ( $tabFunction[0]!=""){
	  $target = $tabFunction[0];
	}
	else{
	  $target=$attrid;
	}
	$function=false;
	$i=1;
	while ( $tabFunction[$i]!=""){
	  $function=true;
	  ereg('(.*)\((.*)\)', $tabFunction[$i], $arg);
	  //print_r($arg);
	  $args[$i]=addslashes($arg[2]);
	  $tabFunction[$i]=$arg[1];
	  $string_function.="doing($tabFunction[$i],'$args[$i]');";
	  $i++;
	}
      }
      

    


      else {
	$target= $attrid;
      }
   
     
      $url= elinkEncode($doc,$oattr->elink,$index);
      $input.="<input type=\"button\" value=\"+\"".
	" title=\""._("add inputs")."\"".
	" onclick=\"subwindowm(300,500,'$target','$url');";
      if ($function) {
	$input.="$string_function\">";
      }
      else{
	$input.="\">";
      }


    }
  }

  return $input;
  
  
  
  
}

function elinkEncode(&$doc, $link,$index) {
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
	case "K" :
	  $urllink.=$index;  
	  break;
	case "I" :
	  $urllink.=$doc->id;
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

	$attr = $doc->getAttribute($sattrid);
	if ($attr->inArray())	$sattrid.=$index;
	//print "attr=$sattrid";
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
