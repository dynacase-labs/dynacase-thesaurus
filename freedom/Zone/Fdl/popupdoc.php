<?php
/**
 * Specific menu for family
 *
 * @author Anakeen 2000 
 * @version $Id: popupdoc.php,v 1.9 2006/04/27 08:25:46 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/Class.Doc.php");
// -----------------------------------
function popupdoc(&$action,$tlink,$tsubmenu) {
  // -----------------------------------
  // ------------------------------
  header('Content-type: text/xml; charset=utf-8'); 
  $action->lay = new Layout(getLayoutFile("FDL","popupdoc.xml"),$action);

  $mb=microtime();

    $action->lay->set("CODE","OK");
    $action->lay->set("warning","");
  // define accessibility

  $action->lay->Set("SEP",false);


  $useicon=false;
  $rlink=array();
  foreach ($tlink as $k=>$v) {
    if ($v["visibility"]!=POPUP_INVISIBLE)   {
      if ((!isset($v["icon"])) || ($v["icon"]=="")) {
	$v["icon"]="Images/none.gif";
      } else {
	$useicon=true;
      }
      $v["issubmenu"]=false;
      $v["descr"]=utf8_encode($v["descr"]);
      $v["tconfirm"]=utf8_encode($v["tconfirm"]);
      if (! isset($v["jsfunction"])) $v["jsfunction"]="";
      if (! isset($v["url"])) $v["url"]="";
      if (! isset($v["separator"])) $v["separator"]=false;
      if ((!isset($v["idlink"])) || ($v["idlink"]=="")) $v["idlink"]=$k;
      if ((!isset($v["target"])) || ($v["target"]=="")) $v["target"]=$k;
      if ((!isset($v["mwidth"])) || ($v["mwidth"]=="")) $v["mwidth"]=$action->getParam("FDL_VD2SIZE",300);
      if ((!isset($v["mheight"])) || ($v["mheight"]=="")) $v["mheight"]=$action->getParam("FDL_HD2SIZE",400);
      if ((isset($v["url"])) && ($v["url"]!="")) $v["URL"]=true;
      else 	$v["URL"]=false;
      
      if ((isset($v["jsfunction"])) && ($v["jsfunction"]!="")) $v["JSFT"]=true;
      else $v["JSFT"]=false;
      $v["smid"]="";
      if ((isset($v["submenu"])) && ($v["submenu"]!="")) {
	$smid=base64_encode($v["submenu"]);
	$v["smid"]=$smid;
	if (! isset($tsubmenu[$smid])) {
	  $tsubmenu[$smid]=array("idlink"=>$smid,
				 "descr"=>utf8_encode($v["submenu"]));
	}

	if (! isset($tsubmenu[$smid]["displayed"])) {
	  $tsubmenu[$smid]["displayed"]=true;
	  $tsubmenu[$smid]["issubmenu"]=true;
	  $tsubmenu[$smid]["URL"]=false;
	  $tsubmenu[$smid]["JSFT"]=false;
	  $tsubmenu[$smid]["separator"]=false;
	  $rlink[]=$tsubmenu[$smid];
	}
      }
      $rlink[]=$v;
    }
  }


  $action->lay->Set("ICONS",$useicon);
  $action->lay->SetBlockData("ADDLINK",$rlink);
  $action->lay->SetBlockData("SUBMENU",$tsubmenu);
  $action->lay->SetBlockData("SUBDIVMENU",$tsubmenu);
  $action->lay->Set("count",count($tlink));
  $action->lay->Set("SEP",(count($tsubmenu)>0));// to see separator
  $action->lay->set("delay",microtime_diff(microtime(),$mb));
}


?>