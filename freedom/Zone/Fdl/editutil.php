<?php
/**
 * Edition functions utilities
 *
 * @author Anakeen 2000 
 * @version $Id: editutil.php,v 1.78 2004/09/21 14:34:12 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */




//
// ---------------------------------------------------------------
include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");
include_once("VAULT/Class.VaultFile.php");


// -----------------------------------
function getHtmlInput(&$doc, &$oattr, $value, $index="",$jsevent="") {
  global $action;


  $docid=intval($doc->id);
  if ($docid== 0) intval($docid=$doc->fromid);
  $attrtype=$oattr->type;

 $idocfamid=$oattr->format;

 $alone=$oattr->isAlone; // set by method caller in special case to display alone


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

  $oc = "$jsevent onchange=\"document.isChanged=true\" "; // use in "pleaseSave" js function

  // output change with type
  switch ($attrtype)
    {
		      
      //같같같같같같같같같같같같같같같같같같같같
    case "image": 
      if (ereg ("(.*)\|(.*)", $value, $reg)) {
			  
	$dbaccess = GetParam("FREEDOM_DB");
	$vf = newFreeVaultFile($dbaccess);
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
	$vf = newFreeVaultFile($dbaccess);
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
      $input .="<input $oc class=\"\" size=15 type=\"file\" name=\"_UPL".$attrin."\" value=\"".chop(htmlentities($value))."\"";
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
      getLayArray($lay,$doc,$oattr);
		      
      $input =$lay->gen(); 
      break;
		      
      //같같같같같같같같같같같같같같같같같같같같
    case "doc": 

      $lay = new Layout("FDL/Layout/editadoc.xml", $action);
      getLayAdoc($lay,$doc,$oattr,$value,$attrin,$index);
		      
      if (($visibility == "R")||($visibility == "S")) $lay->set("disabled",$idisabled);
      else $lay->set("disabled","");
      $input =$lay->gen(); 
      break;		
      //같같같같같같같같같같같같같같같같같같같같
 
     
    case "enum": 
      if (($oattr->repeat)&&(!$oattr->inArray())) { // enumlist

	switch ($oattr->eformat) {
	case "vcheck":
	  $lay = new Layout("FDL/Layout/editenumlistvcheck.xml", $action);
	  break;
	case "hcheck":
	  $lay = new Layout("FDL/Layout/editenumlisthcheck.xml", $action);
	  break;
	
	default:
	  $lay = new Layout("FDL/Layout/editenumlist.xml", $action);
	}	
      } else {
	
	$enuml = $oattr->getenumlabel();
	$lunset=current($enuml);
	if ($value=="") $value=key($enuml);
	switch ($oattr->eformat) {
	case "vcheck":
	  $lay = new Layout("FDL/Layout/editenumvcheck.xml", $action);
	  break;
	case "hcheck":
	  $lay = new Layout("FDL/Layout/editenumhcheck.xml", $action);
	  break;
	case "bool":
	  $lay = new Layout("FDL/Layout/editenumbool.xml", $action);
	  
	  $lset=next($enuml);
	  if ($value==key($enuml))  $lay->set("checkedyesno","checked");
	  else $lay->set("checkedyesno","");
	  $lay->set("tyesno",sprintf(_("set for %s, unset for %s"),$lset,$lunset));
	  break;
	default:
	  $lay = new Layout("FDL/Layout/editenum.xml", $action);
	}

      }
    
      getLayOptions($lay,$doc,$oattr,$value,$attrin,$index);
      if (($visibility == "R")||($visibility == "S")) $lay->set("disabled",$idisabled);
      else $lay->set("disabled","");
      $input =$lay->gen(); 
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
      $lay = new Layout("FDL/Layout/editdate.xml", $action);
      getLayDate($lay,$doc,$oattr,$value,$attrin,$index);
		      
      $lay->set("disabled","");
      if (($visibility == "R")||($visibility == "S")) {
	$lay->set("disabled",$idisabled);

      } else  if ($doc->usefor != 'D') 	$lay->set("disabled","disabled");


      if (!(($visibility == "R")||($visibility == "S"))) {
	$lay->setBlockData("VIEWCALSEL",array(array("zou")));
      }
      if ($doc->usefor != 'D') 	$lay->setBlockData("CONTROLCAL",array(array("zou")));
      $input =$lay->gen(); 
      break;     
      //같같같같같같같같같같같같같같같같같같같같
			
    case "timestamp": 
      $lay = new Layout("FDL/Layout/edittimestamp.xml", $action);
      getLayDate($lay,$doc,$oattr,$value,$attrin,$index);
		      
      $lay->set("disabled","");
      if (($visibility == "R")||($visibility == "S")) {
	$lay->set("disabled",$idisabled);	
      } else  if ($doc->usefor != 'D') 	$lay->set("disabled","disabled");


      $input =$lay->gen(); 
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
	$hvalue=str_replace(array("[","$"),array("&#091;","&#036;"),chop(htmlentities(stripslashes($value))));

	if ($oattr->eformat != "") {
	  // input help with selector 
	  $lay = new Layout("FDL/Layout/edittextlist.xml", $action);
	  if (getLayTextOptions($lay,$doc,$oattr,$hvalue,$attrin,$index)) {
	    if (($visibility == "R")||($visibility == "S")) $lay->set("disabled",$idisabled);
	    else $lay->set("disabled","");
	    $lay->set("adisabled",$idisabled);
	    $input =$lay->gen(); 
	    $oattr->phpfunc=false; // disabled default input help
	  }  else {
	    $oattr->eformat = ""; // restore default display
	  }
	}
	if ($oattr->eformat == "") {
	  //Common representation
	  $input="<input $oc class=\"fullresize\" type=\"text\" name=\"".$attrin."\" value=\"".$hvalue."\"";     
	  $input .= " id=\"".$attridk."\" "; 
	  if (($visibility == "R")||($visibility == "S")) $input .= $idisabled;		      
	  $input .= " > "; 
	} 
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

	if ($alone) $ctype.="-alone";
	$input.="<input id=\"ic_$attridk\" type=\"button\" value=\"&#133;\"".
	  " title=\"".$ititle."\"".
	  " onclick=\"sendEnumChoice(event,".$docid.
	  ",this,'$attridk','$ctype')\">";

	// clear button
	
	if (ereg("(.*)\((.*)\)\:(.*)", $phpfunc, $reg)) {
	  if ($alone) {
	    $arg = array($oattr->id);
	  } else {
	    $argids = split(",",$reg[3]);  // output args
	    $arg = array();
	    while (list($k, $v) = each($argids)) {
	      if (strlen($v) > 1) $arg[$k]= strtolower(chop($v));
	    }
	  }
	  if (count($arg) > 0) {
	    $jarg="'".implode("','",$arg)."'";
	    $input.="<input id=\"ix_$attridk\" type=\"button\" value=\"&times;\"".
	      " title=\""._("clear inputs")."\"".
	      " onclick=\"clearInputs([$jarg],'$index','$attridk')\">";
	  }
	} 
      }  else if (($oattr->type == "date") || ($oattr->type == "timestamp")){
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

function getLayArray(&$lay,&$doc,&$oattr) {
  global $action;

  $attrid=$oattr->id;
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
      

    
}

/**
 * generate HTML for inline document (not virtual)
 *
 * @param Layout $lay template of html input
 * @param Doc $doc current document in edition
 * @param DocAttribute $oattr current attribute for input
 * @param string $value value of the attribute to display (generaly the value comes from current document)
 * @param string $aname input HTML name (generaly it is '_'+$oattr->id)
 * @param int $index current row number if it is in array ("" if it is not in array)
 */
function getLayAdoc(&$lay,&$doc, &$oattr,$value, $aname,$index) {
  $idocid=$oattr->format.$index;
  $lay->set("name",$aname);
  $lay->set("id",$oattr->id.$index);
  $lay->set("idocid",strtolower($idocid));
  $lay->set("value",$value);

}

/**
 * generate HTML for date attribute
 *
 * @param Layout $lay template of html input
 * @param Doc $doc current document in edition
 * @param DocAttribute $oattr current attribute for input
 * @param string $value value of the attribute to display (generaly the value comes from current document)
 * @param string $aname input HTML name (generaly it is '_'+$oattr->id)
 * @param int $index current row number if it is in array ("" if it is not in array)
 */
function getLayDate(&$lay,&$doc, &$oattr,$value, $aname,$index) {
  $idocid=$oattr->format.$index;
  $lay->set("name",$aname);
  $lay->set("id",$oattr->id.$index);
  $lay->set("idocid",strtolower($idocid));
  $lay->set("value",$value);

}

/**
 * generate HTML for enum attributes
 *
 * @param Layout $lay template of html input
 * @param Doc $doc current document in edition
 * @param DocAttribute $oattr current attribute for input
 * @param string $value value of the attribute to display (generaly the value comes from current document)
 * @param string $aname input HTML name (generaly it is '_'+$oattr->id)
 * @param int $index current row number if it is in array ("" if it is not in array)
 */
function getLayOptions(&$lay,&$doc, &$oattr,$value, $aname,$index) {
  $idocid=$oattr->format.$index;
  $lay->set("name",$aname);
  $idx=$oattr->id.$index;
  $lay->set("id",$idx);
  

  $tvalue=$doc->_val2array($value);

  $enuml = $oattr->getenumlabel();
  $ki=0;
  foreach($enuml as $k=>$v) {

    if (in_array($k,$tvalue)) {
      $topt[$k]["selected"] = "selected";
      $topt[$k]["checked"] = "checked";
    } else {
      $topt[$k]["selected"]="";
      $topt[$k]["checked"] = "";
    }
	  
    $topt[$k]["optid"] = "$idx$ki";
    $topt[$k]["fvalue"]=$v;
    $topt[$k]["kvalue"]=$k;
    $ki++;
  }

  $lay->setBlockData("OPTIONS",$topt);
  $lay->set("value",$value);

}
/**
 * generate HTML for text attributes with help function 
 *
 * @param Layout $lay template of html input
 * @param Doc $doc current document in edition
 * @param DocAttribute $oattr current attribute for input
 * @param string $value value of the attribute to display (generaly the value comes from current document)
 * @param string $aname input HTML name (generaly it is '_'+$oattr->id)
 * @param int $index current row number if it is in array ("" if it is not in array)

 */
function getLayTextOptions(&$lay,&$doc, &$oattr,$value, $aname,$index) {
  include_once("FDL/enum_choice.php");
  $idocid=$oattr->format.$index;
  $lay->set("name",$aname);
  $idx=$oattr->id.$index;
  $lay->set("id",$idx);
  
  $res=getResPhpFunc($doc,$oattr,$rargids,$tselect,$tval,false);

  if ($res===false) return false; // one or more attribute are not set

  $sattrid="[";
  $sattrid.= strtolower("'".implode("','", $rargids)."'");
  $sattrid.="]";
  $lay->Set("attrid", $sattrid);
  foreach ($tselect as $k=>$v) {
    if ($v["choice"]==$value) $tselect[$k]["selected"]="selected";
    else $tselect[$k]["selected"]="";
  }

  $lay->SetBlockData("SELECTENUM", $tselect);
  $lay->SetBlockData("ATTRVAL", $tval);

  $lay->set("value",$value);
  return true;
}
/**
 * add different js files needed in edition mode
 */
function editmode(&$action) {
  
  $action->parent->AddJsRef("htmlarea/htmlarea.js");
  $action->parent->AddJsRef("htmlarea/htmlarea-lang-en.js");
  $action->parent->AddJsRef("htmlarea/dialog.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/PopupWindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/ColorPicker2.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/DHTMLapi.js");
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/idoc.js");
  //  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/datepicker.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-fr.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-setup.js");
  $action->parent->AddCssRef("jscalendar/Layout/calendar-win2k-2.css");
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/common.js");
  $action->parent->AddJsRef($action->GetParam("CORE_STANDURL")."app=FDL&action=EDITJS");
  $action->parent->AddJsRef($action->GetParam("CORE_STANDURL")."app=FDL&action=EDITIJS");
  $action->parent->AddJsRef($action->GetParam("CORE_STANDURL")."app=FDL&action=ENUMCHOICEJS");
}
?>
