<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: autocompletion.php,v 1.1 2007/10/24 15:53:38 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/enum_choice.php");

function autocompletion(&$action) {

  // list of choice to be insert in attribute values

  $docid = GetHttpVars("docid");        // document being edition
  if ($docid=="") $docid = GetHttpVars("fromid",0);        // in case of docid is null
  $attrid = GetHttpVars("attrid",0); // attribute need to enum
  $sorm = GetHttpVars("sorm","single"); // single or multiple
  $index = GetHttpVars("index",""); // index of the attributes for arrays
  $domindex = GetHttpVars("domindex",""); // index in dom of the attributes for arrays

  header('Content-type: text/xml; charset=utf-8'); 
  $action->lay->setEncoding("utf-8");

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc= new_Doc($dbaccess,$docid);

  if ($docid==0) {
    // specific interface  
    $value="";
    $label=GetHttpVars("label",_("no label"));
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
    $phpfile=GetHttpVars("phpfile");
    $phpfunc=GetHttpVars("phpfunc");
    $fieldSet=$doc->attr["FIELD_HIDDENS"];
    $elink="";
    $phpconstraint="";
    $usefor="";
    $eformat="";
    $options="";
    $oattr=new NormalAttribute($attrid,$doc->id,$label,"text",$format,$repeat, $order, $link,
			       $visibility, $needed,$isInTitle,$isInAbstract,
			       $fieldSet,$phpfile,$phpfunc,$elink,
			       $phpconstraint,$usefor,$eformat,$options);
  } else {
    $oattr= $doc->GetAttribute($attrid);
    if (! $oattr) 
      $err=sprintf(_("unknown attribute %s"), $attrid);
  }
  if ($err=="") {
    $notalone="true";

    if (ereg("([a-z]*)-alone",$sorm,$reg)) {
      $sorm=$reg[1];
      $notalone="false";
    }
    $action->lay->set("notalone",$notalone);

    $action->parent->AddJsRef($action->GetParam("CORE_STANDURL")."app=FDL&action=ENUMCHOICEJS");
    $phpfunc=$oattr->phpfunc;
    // capture title
    $ititle="";
  
    if ($phpfunc[0] == "[") {
      if (ereg('\[(.*)\](.*)', $phpfunc, $reg)) {   
	$oattr->phpfunc=$reg[2];
      
	$ititle=addslashes($reg[1]);
      }
    }
    $action->lay->set("ititle",$ititle);

    $res=getResPhpFunc($doc,$oattr,$rargids,$tselect,$tval,true,$index);

    if (! is_array($res)) {
      if ($res=="") $res=sprintf(_("error in calling function %s"),$oattr->phpfunc);
      $err=$res;
    }
    if ($err=="") {
      if (count($res) == 0) $err=sprintf(_("no match for %s"),$oattr->labelText);
 
      if ($err=="") {  
	// add  index for return args
	while (list($k, $v) = each($rargids)) {
	  $targids[]["attrid"]=strtolower($rargids[$k].$domindex);
	}

	$action->lay->SetBlockData("cibles",$targids );
	$topt=array();
	foreach ($res as $k=>$v) {
	  $topt[$k]["choice"]=$v[0];
	  $topt[$k]["cindex"]=$k;
	  unset($v[0]);
	  $topt[$k]["values"]='<val>'.stripslashes(implode("</val><val>",$v)).'</val>';
	}

	$action->lay->SetBlockData("SELECT", $topt);

	$action->lay->Set("count", count($tselect));
      }
    }
  }

  $action->lay->Set("warning", $err);
}



?>
