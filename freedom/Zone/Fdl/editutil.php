<?php
/**
 * Edition functions utilities
 *
 * @author Anakeen 2000 
 * @version $Id: editutil.php,v 1.143 2008/06/12 16:22:13 eric Exp $
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


/**
 * Compose html code to insert input
 * @param Doc &$doc document to edit
 * @param DocAttribute &$attr attribute to edit
 * @param string $value value of the attribute
 * @param string $index in case of array : row of the array
 * @param string $jsevent add an javascript callback on input (like onblur or onmouseover)
 * @param string $notd not add cells in html input generated (by default inputs are in arrays)
 */
function getHtmlInput(&$doc, &$oattr, $value, $index="",$jsevent="",$notd=false) {
  global $action;

  $docid=intval($doc->id);
  if ($docid== 0) $docid=intval($doc->fromid);
  $attrtype=$oattr->type;

  $usephpfunc=true;
  $alone=$oattr->isAlone; // set by method caller in special case to display alone


  $attrid=$oattr->id;
  $attrin='_'.$oattr->id; // for js name => for return values from client
  $attridk=$oattr->id.$index;
  if ($oattr->inArray()) {
    if ($index == -1) {
      $attrin.='[-1]';
      $attridk=$oattr->id.'_1x_';
    } else $attrin.='[]';
  }
  if (isset($oattr->mvisibility)) $visibility=$oattr->mvisibility;
  else $visibility=$oattr->visibility;
  if ($visibility == "I") return ""; // not editable attribute
 
  $idisabled = " disabled readonly=1 title=\""._("read only")."\" ";
  $input="";
		
  
  if (! $notd) $classname="class=\"fullresize\"";
  else $classname="";

  if ($visibility == "H") {
    $input="<input  type=\"hidden\" name=\"".$attrin."\" value=\"".chop(htmlentities(stripslashes($value)))."\"";    
    $input .= " id=\"".$attridk."\" "; 		      
    $input .= " > "; 	      
    if (!$notd) $input .= "</td><td>"; 
    return $input;
  }

  $oc = "$jsevent onchange=\"document.isChanged=true\" "; // use in "pleaseSave" js function
  if ($docid==0) {
    // case of specific interface
    $iopt='&phpfile='.$oattr->phpfile.'&phpfunc='.$oattr->phpfunc.'&label='.($oattr->labelText);
  } else $iopt="";
  if (($oattr->type != "array") && ($oattr->type != "htmltext")) {
    if  ($visibility != "S") {
      if ($usephpfunc && ($oattr->phpfunc != "") && ($oattr->phpfile  != "") && ($oattr->type != "enum") && ($oattr->type != "enumlist") ) {
	if ($oattr->getOption("autosuggest","yes")!="no") {
	  $autocomplete=" autocomplete=\"off\" onfocus=\"activeAuto(event,".$docid.",this,'$iopt')\" ";
	  $oc.=$autocomplete;
	}
      }
    }
  }
  // output change with type
  switch ($attrtype)  {		      
    //----------------------------------------
  case "image": 
    if (ereg (REGEXPFILE, $value, $reg)) {
			  
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
    $input .="<input name=\"".$attrin."\" type=\"hidden\" value=\"".$value."\" id=\"".$attridk."\">";
    $input .="<input type=\"hidden\" value=\"".$value."\" id=\"INIV".$attridk."\">";
    ;
    $input .="<input onchange=\"document.isChanged=true;changeFile(this,'$attridk')\" $classname accept=\"image/*\" size=15 type=\"file\" id=\"IF_$attridk\" name=\"_UPL".$attrin."\"";
      
    if (($visibility == "R")||($visibility == "S")) $input .=$idisabled;
    $input .= " > "; 
    break;
		      
    //----------------------------------------
  case "file": 
    if (ereg (REGEXPFILE, $value, $reg)) {
			  
      $dbaccess = $action->GetParam("FREEDOM_DB");
      $vf = newFreeVaultFile($dbaccess);
      if ($vf -> Show ($reg[2], $info) == "") {
	$vid=$reg[2];
	$DAV=getParam("FREEDAV_SERVEUR",false);
	 
	if ($DAV) {
	  global $action;
	  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/DAV/Layout/getsessionid.js");
	    
	  $oc="onclick=\"var sid=getsessionid('".$docid."','$vid');this.href='asdav://$DAV/freedav/vid-'+sid+'/$info->name'\"";
	  $fname="<A title=\""._("open file with your editor")."\" href=\"#\" $oc><img style=\"border:none\" src=\"Images/davedit.png\">";
	} else {
	  $fname = "<A target=\"$attrid\" href=\"".
	    $action->GetParam("CORE_BASEURL").
	    "app=FDL&action=EXPORTFILE&vid=$vid&docid=$docid&attrid=$attrid&index=$index\">";
	}
	$fname .= $info->name;
	$fname .= "</A>";
      }
      else $fname=_("error in filename");
    }
    else $fname=_("no filename");
		      
    $input = "<span class=\"FREEDOMText\">".$fname."</span><BR>";
		      
    // input 
    $input .="<input name=\"".$attrin."\" type=\"hidden\" value=\"".$value."\" id=\"".$attridk."\">";
    $input .="<input type=\"hidden\" value=\"".$value."\" id=\"INIV".$attridk."\">";

    $input .="<input onchange=\"document.isChanged=true;changeFile(this,'$attridk')\"  class=\"\" size=15 type=\"file\" id=\"IF_$attridk\" name=\"_UPL".$attrin."\" value=\"".chop(htmlentities($value))."\"";


    if (($visibility == "R")||($visibility == "S")) $input .=$idisabled;
    $input .= " > "; 
    break;
		      
    //----------------------------------------
  case "longtext": 
    $rows=2;
    if ($action->Read("navigator","")=="NETSCAPE") $rows--;
    $expid="exp".$attridk;
    $input="<textarea $oc wrap=\"virtual\"  onkeyup=\"textautovsize(event,this)\"  onclick=\"textautovsize(event,this)\" class=\"autoresize\" rows=$rows name=\"".
      $attrin."\" ";
    $input .= " id=\"".$attridk."\" "; 
    if (($visibility == "R")||($visibility == "S")) $input .=$idisabled;
    $input .= " >".
      str_replace(array("[","$"),array("&#091;","&#036;"),htmlentities(stripslashes(str_replace("<BR>","\n",$value)))).
      "</textarea>";
    //	"<input id=\"$expid\" style=\"display:none\" type=\"button\" onclick=\"document.getElementById('$attridk').rows=$rows;this.style.display='none'\" value=\"&Delta;\">";
    
    
    break;
    //----------------------------------------
  case "htmltext": 
    /*
     $expid="exp".$attrid;
     $input="<textarea $oc  style=\"width:100%\" rows=\"20\"   name=\"".
     $attrin."\" ";
     $input .= " id=\"".$attridk."\" "; 
     if (($visibility == "R")||($visibility == "S")) $input .=$idisabled;
     $input .= " >".
     htmlentities(stripslashes($value)).
     "</textarea>";
    */

    //      $input .= "<input type=\"button\" onclick=\"var editor$attridk = new HTMLArea('$attridk');editor$attridk.generate();\" value=\"Y\"></input>";
    // $input .= "<script >var editor$attridk = new HTMLArea('$attridk');setTimeout(\"editor$attridk.generate()\",500)</script>";

    if (($visibility=="H")||($visibility=="R")) {
      $input="<textarea    name=\"$attrin\">$value</textarea>";
    } elseif ($visibility=="S") {
      // no input : just text
      $input="<div class=\"static\" name=\"$attrin\">$value</div>";
      
    } else {
      $lay = new Layout("FDL/Layout/fckeditor.xml", $action);
      $lay->set("Value",str_replace(array("\n","\r","'","script>"),array(" "," ","\\'","pre>"), $value));
      $lay->set("label",ucFirst($oattr->labelText));
      $lay->set("need",$oattr->needed);
      $lay->set("height",$oattr->getOption("editheight","100%"));
      $lay->set("toolbar",$oattr->getOption("toolbar","Simple"));
      $lay->set("toolbarexpand",(strtolower($oattr->getOption("toolbarexpand"))=="no")?"false":"true");
      $lay->set("aid",$attridk);
      $lay->set("aname",$attrin);
      if (($visibility == "R")||($visibility == "S")) $lay->set("disabled",$idisabled);
      else $lay->set("disabled","");
      $input =$lay->gen(); 
    }

    
    break;
    //----------------------------------------
  case "idoc":

    $input.=getLayIdoc($doc,$oattr,$attridk,$attrin,$value);
      
    break;
      

    //----------------------------------------
  case "array": 

    $lay = new Layout("FDL/Layout/editarray.xml", $action);
    $rn=$oattr->getOption("roweditzone");
    if ($rn) getZoneLayArray($lay,$doc,$oattr,$rn);
    else getLayArray($lay,$doc,$oattr);
		      
    $input =$lay->gen(); 
    break;
		      
    //----------------------------------------
  case "doc": 

    $lay = new Layout("FDL/Layout/editadoc.xml", $action);
    getLayAdoc($lay,$doc,$oattr,$value,$attrin,$index);
		      
    if (($visibility == "R")||($visibility == "S")) $lay->set("disabled",$idisabled);
    else $lay->set("disabled","");
    $input =$lay->gen(); 
    break;		
    //----------------------------------------
 
     
  case "enum": 
    if ($oattr->eformat=="") $oattr->eformat=$oattr->getOption("eformat");
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
      if ($value=="") {
	if (($doc->id==0)||($oattr->eformat=='bool')) $value=key($enuml);
	else $value=" ";
	
      } 
      switch ($oattr->eformat) {
      case "vcheck":
	$lay = new Layout("FDL/Layout/editenumvcheck.xml", $action);
	break;
      case "hcheck":
	$lay = new Layout("FDL/Layout/editenumhcheck.xml", $action);
	break;
      case "auto":
	$lay = new Layout("FDL/Layout/editenumauto.xml", $action);
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
		      

		      
    //----------------------------------------
			
  case "color": 
    $elabel=$oattr->getOption("elabel");
    if ($elabel != "") $eopt.=" title=\"$elabel\"";
    $input="<input size=7  $eopt style=\"background-color:$value\" type=\"text\"  name=\"".$attrin."\" value=\"".chop(htmlentities($value))."\"";
    $input .= " id=\"".$attridk."\" "; 

    if (($visibility == "R")||($visibility == "S")) $input .= $idisabled; 
    else  if ($doc->usefor != 'D') $input .=" disabled "; // always but default

    $input .= " >&nbsp;"; 
    if (!(($visibility == "R")||($visibility == "S"))) {
      $input.="<input id=\"ic_$attridk\" type=\"button\" value=\"&#133;\"".
	" title=\""._("color picker")."\" onclick=\"colorPick.select(document.getElementById('$attridk'),'$attridk')\"".
	">";
    }
    break;      
		      
    //----------------------------------------
			
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
    $input =trim($lay->gen()); 
    break;     
    //----------------------------------------
			
  case "timestamp": 
    $lay = new Layout("FDL/Layout/edittimestamp.xml", $action);
    getLayDate($lay,$doc,$oattr,$value,$attrin,$index);
		      
    $lay->set("disabled","");
    if (($visibility == "R")||($visibility == "S")) {
      $lay->set("disabled",$idisabled);	
    } else  if ($doc->usefor != 'D') 	$lay->set("disabled","disabled");


    $input =$lay->gen(); 
    break;
		      
    //----------------------------------------
			
  case "time": 
    $isDisabled="";
    if (($visibility == "R")||($visibility == "S")) $isDisabled =$idisabled;
    list($hh,$mm,$ss) = explode(":",$value);
    $input ="<input $isDisabled size=2 maxlength=2 onchange=\"chtime('$attridk')\" type=\"text\"  value=\"".$hh."\" id=\"hh".$attridk."\">:";
     
    $input.="<input $isDisabled size=2 maxlength=2 onchange=\"chtime('$attridk')\" type=\"text\"  value=\"".$mm."\"id=\"mm".$attridk."\">";
      

    $input.="<input  type=\"hidden\"  name=\"".$attrin."\" id=\"".$attridk."\" value=\"".$value."\">";

    break;      
		      
    //----------------------------------------
  case "password" : 
    // don't see the value
    $eopt="$classname ";
    $esize=$oattr->getOption("esize");
    if ($esize > 0) $eopt="size=$esize";
    $input="<input $oc $eopt type=\"password\" name=\"".$attrin."\" value=\""."\"";
    $input .= " id=\"".$attridk."\" "; 


    if (($visibility == "R")||($visibility == "S")) $input .= $idisabled;
		      
    $input .= " > "; 
    break;

    //----------------------------------------
  case "option": 

    $lay = new Layout("FDL/Layout/editdocoption.xml", $action);
    getLayDocOption($lay,$doc,$oattr,$value,$attrin,$index);
    if (($visibility == "R")||($visibility == "S")) $lay->set("disabled",$idisabled);
    else $lay->set("disabled","");
    $input =$lay->gen(); 
    break;
    //----------------------------------------
  default : 
    
    if (($oattr->repeat)&&(!$oattr->inArray())) { // textlist
      $input="<textarea $oc $classname rows=2 name=\"".
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
	if (getLayTextOptions($lay,$doc,$oattr,$value,$attrin,$index)) {
	  if (($visibility == "R")||($visibility == "S")) $lay->set("disabled",$idisabled);
	  else $lay->set("disabled","");
	  $lay->set("adisabled",$idisabled);
	  $lay->set("oc",$jsevent);

	  if ($oattr->eformat=="hlist") $lay->set("atype","hidden");
	  else $lay->set("atype","text");
	  $input =$lay->gen(); 
	  $usephpfunc=false; // disabled default input help
	}  else {
	  $oattr->eformat = ""; // restore default display
	}
      }
      if ($oattr->eformat == "") {
	//Common representation
	$eopt="$classname ";
	$esize=$oattr->getOption("esize");
	if ($esize > 0) $eopt="size=$esize";
	$elabel=$oattr->getOption("elabel");
	if ($elabel != "") $eopt.=" title=\"$elabel\"";
	$ecolor=$oattr->getOption("color");
	$estyle=""; // css style
	if ($ecolor != "") $estyle="color:$ecolor;";
	$ealign=$oattr->getOption("align");
	if ($ealign != "") $estyle.="text-align:$ealign";
	if ($estyle) $estyle="style=\"$estyle\"";

	$input="<input $oc $eopt $estyle type=\"text\" name=\"".$attrin."\" value=\"".$hvalue."\"";     
	$input .= " id=\"".$attridk."\" "; 
	if (($visibility == "R")||($visibility == "S")) $input .= $idisabled;		      
	$input .= " > "; 
      } 
    }
    break;
		      
  }
  
  if (($oattr->type != "array") && ($oattr->type != "htmltext")) {
    if  ($visibility != "S") {
      if ($usephpfunc && ($oattr->phpfunc != "") && ($oattr->phpfile  != "") && ($oattr->type != "enum") && ($oattr->type != "enumlist") ) {
	$phpfunc=$oattr->phpfunc;
	// capture title
	if (isUTF8($oattr->labelText)) $oattr->labelText=utf8_decode($oattr->labelText);
	$ititle=sprintf(_("choose inputs for %s"),($oattr->labelText));
	if ($oattr->getOption("ititle") != "") $ititle=str_replace("\"","'",$oattr->getOption("ititle"));
	
	
	if ($phpfunc[0] == "[") {
	  if (ereg('\[(.*)\](.*)', $phpfunc, $reg)) {   
	    $phpfunc=$reg[2];
	    $ititle=addslashes($reg[1]);
	  }
	}
	if (!$notd) $input.="</td><td width=\"100px\">";
	if (ereg("list",$attrtype, $reg)) $ctype="multiple";
	else $ctype="single";

	if ($alone) $ctype.="-alone";
	
	/*$input.="<input id=\"ic2_$attridk\" type=\"button\" value=\"&#133;\"".
	 " title=\"".$ititle."\"".
	 " onclick=\"sendEnumChoice(event,".$docid.
	 ",this,'$attridk','$ctype','$iopt')\">";*/
	$input.="<input id=\"ic_$attridk\" type=\"button\" value=\"&#133;\"".
	  " title=\"".$ititle."\"".
	  " onclick=\"sendAutoChoice(event,".$docid.
	  ",this,'$attridk','$iopt')\">";

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
	if (!$notd) $input.="</td><td>";
      }else if ($oattr->type == "color") {
	$input.="<input id=\"ix_$attridk\" type=\"button\" value=\"&times;\"".
	  " title=\""._("clear inputs")."\"".
	  " onclick=\"clearInputs(['$attrid'],'$index')\">";  
	$input.="</td><td>";    
      }else if ($oattr->type == "time") {
	$input.="<input id=\"ix_$attridk\" type=\"button\" value=\"&times;\"".
	  " title=\""._("clear inputs")."\"".
	  " onclick=\"clearTime('$attridk')\">";   
	if (!$notd) $input.="</td><td>";   
      }else if (($oattr->type == "file")||($oattr->type == "image")) {
	$input.="<input id=\"ix_$attridk\" type=\"button\" value=\"&times;\"".
	  " title=\""._("clear file")."\"".	  
	  " title1=\""._("clear file")."\"".  
	  " value1=\"&times;\"".	  
	  " title2=\""._("restore original file")."\"". 
	  " value2=\"&minus;\"".
	  " onclick=\"clearFile(this,'$attridk')\">";   
	if (!$notd) $input.="</td><td>";   
      } else {
	if (!$notd) $input.="</td><td>";   
      }
		
      
    } else {
      if (!$notd) $input.="</td><td>";
    }
    if ($oattr->elink != "") {

      if (substr($oattr->elink,0,3)=="JS:") {
	// javascript action
	$url= elinkEncode($doc,substr($oattr->elink,3),$index,$ititle,$isymbol);

	$jsfunc=$url;
	  
      } else {
	$url= elinkEncode($doc,$oattr->elink,$index,$ititle,$isymbol);

	$target= $oattr->getOption("eltarget",$attrid);
	  
	$jsfunc="subwindowm(300,500,'$target','$url');";
      }
	
      if ($oattr->getOption("elsymbol") != "") $isymbol=$oattr->getOption("elsymbol");
      if ($oattr->getOption("eltitle") != "") $ititle=str_replace("\"","'",$oattr->getOption("eltitle"));
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
	$urllink.= "'+trim(document.getElementById('$sattrid').value)+'";
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
     
  $height=$oattr->getOption("height",false);
  $lay->set("tableheight",$height);
  $lay->set("thspan","2");

      $talabel=array();
      $tilabel=array();
      $tvattr = array();

      // get default values
      $ddoc = createDoc($doc->dbaccess, $doc->fromid==0?$doc->id:$doc->fromid,false);
      $tad = $ddoc->attributes->getArrayElements($attrid);
      $tval=array();
      $nbcolattr=0; // number of column
      foreach($ta as $k=>$v) {
	if ($v->mvisibility=="R") {
	  $v->mvisibility="H"; // don't see read attribute
	  $ta[$k]->mvisibility="H";
	}
	$visible = ($v->mvisibility!="H");
	$talabel[] = array("alabel"=>(!$visible)?"":$v->labelText,
			   "ahw"=>(!$visible)?"0px":$v->getOption("cwidth","auto"),
			   "astyle"=>$v->getOption("cellheadstyle"),
			   "ahvis"=>(!$visible)?"hidden":"visible");
	$tilabel[] = array("ilabel"=>getHtmlInput($doc,$v,$ddoc->getValue($tad[$k]->id),-1),
			   "ihw"=>(!$visible)?"0px":"auto",
			   "bgcolor"=>$v->getOption("bgcolor","inherit"),
			   "tdstyle"=>$v->getOption("cellbodystyle"),
			   "ihvis"=>(!$visible)?"hidden":"visible");
	
	
	if ($visible) $nbcolattr++;
	$tval[$k]=$doc->getTValue($k);
	$nbitem=count($tval[$k]);	
	
	if ($nbitem==0) {
	  // add first range
	  if ($oattr->format != "empty") {
	    $tval[$k]=array(0=>"");
	    $nbitem=1;
	  }
	}
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
      $lay->set("attrid",$attrid);
      if ($oattr->getOption("vlabel")=="") $lay->set("caption",$oattr->labelText);
      else $lay->set("caption","");
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
	  
	  $visible = ($va->mvisibility!="H");
	  
	  $tivalue[]=array("eivalue"=>getHtmlInput($doc,$va,$tval[$ka][$k],$k),
			   "ehvis"=>(!$visible)?"hidden":"visible",
			   "bgcolor"=>$va->getOption("bgcolor","inherit"),
			   "tdstyle"=>$va->getOption("cellbodystyle"),
			   "vhw"=>(!$visible)?"0pt":$talabel[$ika]["ahw"]);
	  $ika++;
	}
	$lay->setBlockData("bevalue_$k",$tivalue);
      }
      $lay->set("readonly",($oattr->mvisibility=='U'));
      if (count($tvattr) > 0) $lay->setBlockData("EATTR",$tvattr);          
}

function getZoneLayArray(&$lay,&$doc,&$oattr,$zone) {
  global $action;

  $height=$oattr->getOption("height",false);
  $lay->set("tableheight",$height);
  $lay->set("readonly",($oattr->mvisibility=='U'));
  $lay->set("thspan","1");

  if (($zone != "") &&  ereg("([A-Z_-]+):([^:]+):{0,1}[A-Z]{0,1}",$zone,$reg)) {
  $attrid=$oattr->id;
  $ta = $doc->attributes->getArrayElements($attrid);
  function xt_innerXML(&$node){
    if(!$node) return false;
    $document = $node->ownerDocument;
    $nodeAsString = $document->saveXML($node);
    preg_match('!\<.*?\>(.*)\</.*?\>!s',$nodeAsString,$match);
    return $match[1];
  }

  $dxml=new DomDocument();
  $rowlayfile=getLayoutFile($reg[1],strtolower($reg[2]).".xml");
  if (! @$dxml->load(DEFAULT_PUBDIR."/$rowlayfile")) {	      
    AddwarningMsg(sprintf(_("cannot open %s layout file"),DEFAULT_PUBDIR."/$rowlayfile"));
    return;
  }
  $theads=$dxml->getElementsByTagName('table-head');
  if ($theads->length > 0) {
    $thead=$theads->item(0);
    $theadcells=$thead->getElementsByTagName('cell');
    $talabel=array();
    for ($i = 0; $i < $theadcells->length; $i++) {   
      $th= xt_innerXML($theadcells->item($i));
      $thstyle=$theadcells->item($i)->getAttribute("style");
      
      $talabel[] = array("alabel"=>$th,
			 "ahw"=>"auto",
			 "astyle"=>$thstyle,
			 "ahvis"=>"visible");
    }
    $lay->setBlockData("TATTR",$talabel);
  }

  $tbodies=$dxml->getElementsByTagName('table-body');
  if ($tbodies->length > 0) {
    $tbody=$tbodies->item(0);
    $tbodycells=$tbody->getElementsByTagName('cell');
    for ($i = 0; $i < $tbodycells->length; $i++) {   
      $tr[]= xt_innerXML($tbodycells->item($i));      
      $tcellstyle[]=$tbodycells->item($i)->getAttribute("style");
    }
  }

  $nbitem=0;

  foreach($ta as $k=>$v) {	      	
    $tval[$k]=$doc->getTValue($k);
    $nbitem= max($nbitem,count($tval[$k]));
    if ($emptyarray && ($doc->getValue($k)!="")) $emptyarray=false;	 
    $lay->set("L_".strtoupper($v->id),ucfirst($v->labelText));  
  }


  $lay->set("attrid",$attrid);
  $lay->set("caption",$oattr->labelText);     
  $lay->set("footspan",count($ta)*2);

  
  // get default values
  $fdoc=$doc->getFamDoc();
  $defval=$fdoc->getDefValues();

  $tvattr = array();
  for ($k=0;$k<$nbitem;$k++) {
    $tvattr[]=array("bevalue" => "bevalue_$k");
    $tivalue=array();
    
    foreach ($tr as $kd=>$td) {
      $val = preg_replace("/\[([^\]]*)\]/e",
			   "rowattrReplace(\$doc,'\\1',$k)",
			   $td);	  
      $tivalue[]=array("eivalue"=>$val,
		       "ehvis"=>"visible",
		       "tdstyle"=>$tcellstyle[$kd],
		       "bgcolor"=>"inherit",
		       "vhw"=>"auto");
    }
    $lay->setBlockData("bevalue_$k",$tivalue);
  }

  foreach ($tr as $kd=>$td) {
    $dval = preg_replace("/\[([^\]]*)\]/e",
			 "rowattrReplace(\$doc,'\\1',-1,\$defval)",
			 $td);
    $tilabel[] = array("ilabel"=>$dval,
		       "ihw"=>"auto",
		       "tdstyle"=>$tcellstyle[$kd],
		       "bgcolor"=>"inherit",
		       "ihvis"=>"visible");
  }

  $lay->setBlockData("IATTR",$tilabel);
  $lay->set("readonly",($oattr->mvisibility=='U'));
  if (count($tvattr) > 0) $lay->setBlockData("EATTR",$tvattr);
      
 }    
}

function rowattrReplace(&$doc,$s,$index,&$defval=null) {
  if (substr($s,0,2)=="L_") return "[$s]";
    if (substr($s,0,2)=="V_") {
      $s=substr($s,2);
      if ($index != -1)  $value=$doc->getTValue($s,"",$index);
      else $value=$defval[strtolower($s)];
      $oattr=$doc->getAttribute($s);
      $v=getHtmlInput($doc, $oattr, $value, $index,"",true);
    } else {
      $sl=strtolower($s);
      if (! isset($doc->$sl)) return "[$s]";
      if ($index==-1) $v=$doc->getValue($sl);
      else $v=$doc->getTValue($sl,"",$index);
    }
    return $v;
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
  $lay->set("idi",$oattr->id);
  $etype=$oattr->getOption("etype");
  if ((!$etype) || ($etype=="close")) $doc->addParamRefresh($oattr->id,"li_".$oattr->id);

  $tvalue=$doc->_val2array($value);

  $lay->set("lvalue","");
  $enuml = $oattr->getenumlabel();
  $ki=0;
  foreach($enuml as $k=>$v) {

    if (in_array($k,$tvalue)) {
      $topt[$k]["selected"] = "selected";
      $topt[$k]["checked"] = "checked";
      $lay->set("lvalue",$v);
    } else {
      $topt[$k]["selected"]="";
      $topt[$k]["checked"] = "";
    }
	  
    $topt[$k]["optid"] = "$idx$ki";
    $topt[$k]["fvalue"]=$v;
    $topt[$k]["kvalue"]=$k;
    $topt[$k]["ki"]=$ki;
    $ki++;
  }
  $lay->setBlockData("OPTIONS",$topt);
  $lay->set("value",$value);
  $lay->set("docid",$doc->fromid);
  $lay->set("index",$index);

}/**
 * generate HTML for option attributes
 *
 * @param Layout $lay template of html input
 * @param Doc $doc current document in edition
 * @param DocAttribute $oattr current attribute for input
 * @param string $value value of the attribute to display (generaly the value comes from current document)
 * @param string $aname input HTML name (generaly it is '_'+$oattr->id)
 * @param int $index current row number if it is in array ("" if it is not in array)
 */
function getLayDocOption(&$lay,&$doc, &$oattr,$value, $aname,$index) {
  $idocid=$oattr->format.$index;
  $lay->set("name",$aname);
  $idx=$oattr->id.$index;
  $lay->set("id",$idx);
  $lay->set("didx",$index);
  $lay->set("di",trim(strtolower($oattr->format)));
  if ($index !== "") $lay->set("said",$doc->getTValue($oattr->format,"",$index));
  else $lay->set("said",$doc->getValue($oattr->format));
  

  $lay->set("value",$value);
  $lay->set("uuvalue",urlencode($value));

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
  if (is_array($rargids)) $sattrid.= strtolower("'".implode("','", $rargids)."'");
  $sattrid.="]";
  $lay->Set("attrid", $sattrid);

  if (is_array($tselect)) {
    foreach ($tselect as $k=>$v) {
      if ($v["choice"]==$value) $tselect[$k]["selected"]="selected";
      else $tselect[$k]["selected"]="";
    }
    $lay->SetBlockData("SELECTENUM", $tselect);
  }

  $lay->SetBlockData("ATTRVAL", $tval);

  $lay->set("value",$value);
  return true;
}
/**
 * generate HTML for idoc attribute
 *
 * @param DocAttribute $oattr current attribute for input
 * @param string $value value of the attribute to display (generaly the value comes from current document)
 * @return String the formated output
 */
function getLayIdoc(&$doc, &$oattr,$attridk,$attrin,$value,$zone="") {

  $idocfamid=$oattr->format;
  if($value!=""){
    $temp=base64_decode($value);
    $entete="<?xml version=\"1.0\" encoding=\"ISO-8859-1\" standalone=\"yes\" ?>";
    $xml=$entete;
    $xml.=$temp; 
    $title=recup_argument_from_xml($xml,"title");//in freedom_util.php
  } else {
    $famname=$doc->getTitle($idocfamid);
    $title=sprintf(_("create new %s"),$famname);
  }
  $input="<INPUT id=\"_" .$attridk."\" TYPE=\"hidden\"  name=$attrin value=\"".$value." \"><a id='iti_$attridk' ".
    " oncontextmenu=\"viewidoc_in_popdoc(event,'$attridk','_$attridk','$idocfamid')\"".
    " onclick=\"editidoc('_$attridk','_$attridk','$idocfamid','$zone');\">$title</a> ";
  return $input;
}
/**
 * add different js files needed in edition mode
 */
function editmode(&$action) {
  
  /*$action->parent->AddJsRef("htmlarea/htmlarea.js");
  $action->parent->AddJsRef("htmlarea/htmlarea-lang-en.js");
  $action->parent->AddJsRef("htmlarea/dialog.js");*/
  
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/fckeditor/fckeditor.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/PopupWindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/ColorPicker2.js");
  if ($action->Read("navigator")=="EXPLORER") $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/iehover.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/DHTMLapi.js");
  //  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/idoc.js");
  //  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/datepicker.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-fr.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-setup.js");
  $action->parent->AddCssRef("jscalendar/Layout/calendar-win2k-2.css");
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/common.js");
  $action->parent->AddJsRef($action->GetParam("CORE_STANDURL")."app=FDL&action=EDITJS");
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/viewicard.js");
  $action->parent->AddJsRef($action->GetParam("CORE_STANDURL")."app=FDL&action=EDITIJS");
  $action->parent->AddJsRef($action->GetParam("CORE_STANDURL")."app=FDL&action=ENUMCHOICEJS");  
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/iframe.js");
  $action->parent->AddJsRef($action->GetParam("CORE_STANDURL")."app=FDL&action=VIEWDOCJS");
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/autocompletion.js");
$action->parent->AddCssRef("FDL:autocompletion.css", true);
 
}
?>
