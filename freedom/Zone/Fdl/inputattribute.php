<?php
/**
 * Compose html code to insert input
 *
 * @author Anakeen 2006
 * @version $Id: inputattribute.php,v 1.2 2006/08/10 15:08:44 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Dir.php");
include_once("FDL/editutil.php");


/**
 * Compose html code to insert input
 * @param Action &$action current action
 * @global type Http var : attribute type
 * @global id Http var : identificator of input generated
 * @global label Http var : label of attribute (only for doclink type when no choice is possible)
 * @global famid Http var : family identificator criteria (only for doclink type)
 * @global value Http var : predefined value
 * @global esize Http var : number of character visible (for text input)
 */
function inputattribute(&$action) {
  
  
  $attrid = GetHttpVars("id");
  $type = GetHttpVars("type");
  $label = GetHttpVars("label");
  $esize = GetHttpVars("esize");
  $value = GetHttpVars("value");
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc=new doc($dbaccess);
  $htmlinput="";

  if ($type=="doclink") {
    $famid = GetHttpVars("famid");

    $index="";
    $jsevent="";
    $format="";
    $repeat=false;
    $order=0;
    $link="";
    $visibility="H";
    $needed="N";
    $isInTitle=false;
    $isInAbstract=false;
    $fieldSet=$doc->attr["FIELD_HIDDENS"];
    $phpfile="";
    $phpfunc="::getTitle($id_$attrid):$attrid";
    $elink="";
    $phpconstraint="";
    $usefor="";
    $eformat="";
    $options="";

    $oattr1=new NormalAttribute("id_$attrid",$doc->id,"id $label","docid",$format,$repeat, $order, $link,
				$visibility, $needed,$isInTitle,$isInAbstract,
				$fieldSet,$phpfile,$phpfunc,$elink,
				$phpconstraint,$usefor,$eformat,$options);

    $index="";
    $jsevent="";
    $format="";
    $repeat=false;
    $order=0;
    $link="";
    $visibility="W";
    $needed="N";
    $isInTitle=false;
    $isInAbstract=false;
    $phpfile="fdl.php";
    $phpfunc="lfamily(D,$famid,$attrid):id_$attrid,$attrid";
    $fieldSet=$doc->attr["FIELD_HIDDENS"];
    $elink="";
    $phpconstraint="";
    $usefor="";
    $eformat="";
    $options="elabel=".utf8_decode($label);
    if ($esize) $options.="|esize=$esize";
    $oattr=new NormalAttribute($attrid,$doc->id,$label,"text",$format,$repeat, $order, $link,
			       $visibility, $needed,$isInTitle,$isInAbstract,
			       $fieldSet,$phpfile,$phpfunc,$elink,
			       $phpconstraint,$usefor,$eformat,$options);

    $doc->attr[$oattr1->id]=$oattr1;
    $htmlinput=getHtmlInput($doc, $oattr1, $value, $index, $jsevent,true);
    $doc->attr[$oattr->id]=$oattr;
  } else {

  $index="";
  $jsevent="";
  $format="";
  $repeat=false;
  $order=0;
  $link="";
  $visibility="W";
  $needed="N";
  $isInTitle=false;
  $isInAbstract=false;
  $fieldSet=$doc->attr["FIELD_HIDDENS"];
  $phpfile="";
  $phpfunc="";
  $elink="";
  $phpconstraint="";
  $usefor="";
  $eformat="";

  $options="elabel=".utf8_decode($label);
  if ($esize) $options.="|esize=$esize";
  $oattr=new NormalAttribute($attrid,$doc->id,$label,$type,$format,$repeat, $order, $link,
			     $visibility, $needed,$isInTitle,$isInAbstract,
			     $fieldSet,$phpfile,$phpfunc,$elink,
			     $phpconstraint,$usefor,$eformat,$options);

  $doc->attr[$attrid]=$oattr;
  }

  $htmlinput.=getHtmlInput($doc, $oattr, $value, $index, $jsevent,true);

  $action->lay->template=$htmlinput;
  
}
?>