<?php
/**
 * Edition functions utilities
 *
 * @author Anakeen 2000 
 * @version $Id: editutil.php,v 1.62 2004/01/16 09:11:47 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


// ---------------------------------------------------------------
// $Id: editutil.php,v 1.62 2004/01/16 09:11:47 eric Exp $
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
    $input .= "</td><td>"; 
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
      $input .="<input $oc class=\"fullresize\" accept=\"image/*\" size=15 type=\"file\" name=\"_UPL".$attrin."\"";
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
      $input .="<input $oc class=\"fullresize\" size=15 type=\"file\" name=\"_UPL".$attrin."\" value=\"".chop(htmlentities($value))."\"";
      $input .= " id=\"".$attridk."\" "; 
      if (($visibility == "R")||($visibility == "S")) $input .=$idisabled;
      $input .= " > "; 
      break;
		      
      //같같같같같같같같같같같같같같같같같같같같
    case "longtext": 
      $expid="exp".$attridk;
      $input="<textarea $oc wrap=\"virtual\" onclick=\"this.rows=9;document.getElementById('$expid').style.display='';\"  class=\"fullresize\" rows=2 name=\"".
	$attrin."\" ";
      $input .= " id=\"".$attridk."\" "; 
      if (($visibility == "R")||($visibility == "S")) $input .=$idisabled;
      $input .= " >".
	str_replace(array("[","$"),array("&#091;","&#036;"),htmlentities(stripslashes(str_replace("<BR>","\n",$value)))).
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
      $ddoc = createDoc($doc->dbaccess, $doc->fromid==0?$doc->id:$doc->fromid,false);
      $tad = $ddoc->attributes->getArrayElements($attrid);


      $nbcolattr=0; // number of column
      while (list($k, $v) = each($ta)) {
	if ($v->mvisibility=="R") {
	  $v->mvisibility="H"; // don't see read attribute
	  $ta[$k]->mvisibility="H";
	}
	$visible = ($v->mvisibility!="H");
	$talabel[] = array("alabel"=>(!$visible)?"":$v->labelText,
			   "ahw"=>(!$visible)?"0px":"auto",
			   "ahvis"=>(!$visible)?"hidden":"visible");
	$tilabel[] = array("ilabel"=>getHtmlInput($doc,$v,$ddoc->getValue($tad[$k]->id),-1),
			   "ihw"=>($visible)?"0px":"auto",
			   "ihvis"=>(!$visible)?"hidden":"visible");
	$tvattr[]=array("bvalue" => "bvalue_$k",
			"attrid" => $v->id);
	
	if ($visible) $nbcolattr++;
	$tval[$k]=$doc->getTValue($k);
	$nbitem=count($tval[$k]);
	$tivalue=array();
	for ($i=0;$i<$nbitem;$i++) {
	  $tivalue[]=array("ivalue"=>$tval[$k][$i]);
	}
	$lay->setBlockData("bvalue_$k",$tivalue);
      }
      
      if ($action->read("navigator") == "EXPLORER") {
	// compute col width explicitly
	if ($nbcolattr> 0) {
	  $aw=sprintf("%d%%",100/$nbcolattr);

	  foreach ($talabel as $ka => $va) {
	    if ($va["ahw"]=="auto") {
	      $talabel[$ka]["ahw"]=$aw;
	      $tilabel[$ka]["ihw"]=$aw;
	    }
	  }
	}
      }

      $lay->setBlockData("TATTR",$talabel);
      $lay->setBlockData("IATTR",$tilabel);
      $lay->setBlockData("VATTR",$tvattr);
      $lay->set("attrid",$attrid);
      $lay->set("caption",$oattr->labelText);
     
      $lay->set("footspan",count($ta)*2);

      reset($tval);
      $nbitem= count(current($tval));
      $tvattr = array();
      
      for ($k=0;$k<$nbitem;$k++) {
	$tvattr[]=array("bevalue" => "bevalue_$k");
	reset($ta);
	$tivalue=array();
	$ika=0;
	while (list($ka, $va) = each($ta)) {
	  
	  
	  $tivalue[]=array("eivalue"=>getHtmlInput($doc,$va,$tval[$ka][$k],$k),
			   "vhw"=>($va->mvisibility=="H")?"0pt":$talabel[$ika]["ahw"]);
	  $ika++;
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
	$input.="<input id=\"ix_$attridk\" type=\"button\" value=\"&times;\"".
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
	$input .= "</select> "; 
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
      $input="<input size=10  type=\"text\"   name=\"".$attrin."\" value=\"".chop(htmlentities($value))."\"";
      $input .= " id=\"".$attridk."\" "; 

      if (($visibility == "R")||($visibility == "S")) {
	$input .= $idisabled; 	
      } else  if ($doc->usefor != 'D') $input .=" disabled "; // always but default

      if ($doc->usefor != 'D') $input .= " onblur=\"control_date(event,this)\" ";
      $input .= " >&nbsp;"; 
      if (!(($visibility == "R")||($visibility == "S"))) {
	$input.="<input id=\"ic_$attridk\" type=\"button\" value=\"&#133;\"".
	  " title=\""._("date picker")."\" onclick=\"show_calendar(event,'".$attridk."')\"".
	  ">";
	$input.="<input type=\"button\" value=\"&diams;\"".
	  " title=\""._("manual date")."\" onclick=\"focus_date(event,'$attridk')\"".
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
      $input="<input $oc class=\"fullresize\" type=\"password\" name=\"".$attrin."\" value=\""."\"";
      $input .= " id=\"".$attridk."\" "; 


      if (($visibility == "R")||($visibility == "S")) $input .= $idisabled;
		      
      $input .= " > "; 
      break;
      //같같같같같같같같같같같같같같같같같같같같
    default : 
    
      if (($oattr->repeat)&&(!$oattr->inArray())) { // textlist
	 $input="<textarea $oc class=\"fullresize\" rows=2 name=\"".
	   $attrin."\" ";
	 $input .= " id=\"".$attridk."\" "; 
	 if (($visibility == "R")||($visibility == "S")) $input .=$idisabled;
	 $input .= " >\n".
	   htmlentities(stripslashes(str_replace("<BR>","\n",$value))).
	   "</textarea>";
      } else {
      $input="<input $oc class=\"fullresize\" type=\"text\" name=\"".$attrin."\" value=\"".str_replace(array("[","$"),array("&#091;","&#036;"),chop(htmlentities(stripslashes($value))))."\"";
     
      $input .= " id=\"".$attridk."\" "; 


      if (($visibility == "R")||($visibility == "S")) $input .= $idisabled;
		      
      $input .= " > ";  
      }
      break;
		      
    }
  
  if ($oattr->type != "array") {
    if  ($visibility != "S") {
      if (($oattr->phpfunc != "") && ($oattr->phpfile  != "") && ($oattr->type != "enum") && ($oattr->type != "enumlist") ) {
	$phpfunc=$oattr->phpfunc;
	// capture title
	$ititle=_("choose inputs");
	
	if ($phpfunc[0] == "[") {
	  if (ereg('\[(.*)\](.*)', $phpfunc, $reg)) {   
	    $phpfunc=$reg[2];
	    $ititle=addslashes($reg[1]);
	  }
	}
	$input.="</td><td width=\"100px\">";
	if (ereg("list",$attrtype, $reg)) $ctype="multiple";
	else $ctype="single";
	$input.="<input id=\"ic_$attridk\" type=\"button\" value=\"&#133;\"".
	  " title=\"".$ititle."\"".
	  " onclick=\"sendEnumChoice(event,".$docid.
	  ",this,'$attridk','$ctype')\">";

	// clear button
	if (ereg("(.*)\((.*)\)\:(.*)", $phpfunc, $reg)) {
      
	  $argids = split(",",$reg[3]);  // output args
	  $arg = array();
	  while (list($k, $v) = each($argids)) {
	    if (strlen($v) > 1) $arg[$k]= strtolower(chop($v));
	  }
	  if (count($arg) > 0) {
	    $jarg="'".implode("','",$arg)."'";
	    $input.="<input id=\"ix_$attridk\" type=\"button\" value=\"&times;\"".
	      " title=\""._("clear inputs")."\"".
	      " onclick=\"clearInputs([$jarg],'$index','$attridk')\">";
	  }
	} 
      }  else if ($oattr->type == "date") {
	$input.="<input id=\"ix_$attridk\" type=\"button\" value=\"&times;\"".
	  " title=\""._("clear inputs")."\"".
	  " onclick=\"clearInputs(['$attrid'],'$index')\">";
	$input.="</td><td>";
      }else if ($oattr->type == "color") {
	$input.="<input id=\"ix_$attridk\" type=\"button\" value=\"&times;\"".
	  " title=\""._("clear inputs")."\"".
	  " onclick=\"clearInputs(['$attrid'],'$index')\">";  
	$input.="</td><td>";    
      }else if ($oattr->type == "time") {
	$input.="<input id=\"ix_$attridk\" type=\"button\" value=\"&times;\"".
	  " title=\""._("clear inputs")."\"".
	  " onclick=\"clearTime('$attridk')\">";   
	$input.="</td><td>";   
      } else {
	$input.="</td><td>";   
      }
		
      if ($oattr->elink != "") {

	if (substr($oattr->elink,0,3)=="JS:") {
	  // javascript action
	  $url= elinkEncode($doc,substr($oattr->elink,3),$index,$ititle,$isymbol);

	  $jsfunc=$url;
	  
	} else {
	  $url= elinkEncode($doc,$oattr->elink,$index,$ititle,$isymbol);

	  $target= $attrid;
	  /* --- for idoc ---
	if (ereg('\[(.*)\](.*)', $oattr->elink, $reg)) {
	// special case wit javascript inputs

	  $oattr->elink=$reg[2];
	  $tabFunction=explode(":",$reg[1]);

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
	--- end for idoc */

	  $jsfunc="subwindowm(300,500,'$target','$url');";
	}
     
	$input.="<input type=\"button\" value=\"$isymbol\"".
	  " title=\"".$ititle."\"".
	  " onclick=\"$jsfunc;";
	if ($function) {
	  $input.="$string_function\">";
	}
	else{
	  $input.="\">";
	}


      }
      if (GetHttpVars("viewconstraint")=="Y") { // set in modcard
	if ($oattr->phpconstraint != "") {
	  $res=$doc->verifyConstraint($oattr->id,$index);
	  if (($res["err"]=="") && (count($res["sug"])==0)) $color='mediumaquamarine';
	  if (($res["err"]=="") && (count($res["sug"])>0)) $color='orange';
	  if (($res["err"]!="")) $color='tomato';

	  $input.="<input style=\"background-color:$color;\"type=\"button\" id=\"co_$attridk\" value=\"C\"".
	    " onclick=\"vconstraint(this,".$doc->fromid.",'$attrid');\">";
	}
      }
    } else {
      $input.="</td><td>";
    }
  }

  return $input;
  
  
  
  
}

function elinkEncode(&$doc, $link,$index,&$ititle,&$isymbol) {
  // -----------------------------------
    
  $ititle=_("add inputs");
  $isymbol='+';
    
  $urllink="";
  if ($link[0] == "[") {
    if (ereg('\[(.*)\|(.*)\](.*)', $link, $reg)) {   
      $link=$reg[3];
      $ititle=$reg[1];
      $isymbol=$reg[2];
    }
  }



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
	case "F" :
	  $urllink.=$doc->fromid;
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
	if (!$attr) {
	  global $action;
	  $action->exitError(sprintf(_("elinkEncode::attribute not found %s"),$sattrid));
	}
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
