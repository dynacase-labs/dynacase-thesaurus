<?php
/**
 * Specific menu for family
 *
 * @author Anakeen 2000 
 * @version $Id: popupdoc.php,v 1.13 2006/09/08 16:28:17 eric Exp $
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
  $noctrlkey=($action->getParam("FDL_CTRLKEY","yes")=="no");

  $useicon=false;
  $rlink=array();
  $rlinkbottom=array();
  foreach ($tlink as $k=>$v) {
    if ($v["visibility"]!=POPUP_INVISIBLE)   {
      if ((!isset($v["icon"])) || ($v["icon"]=="")) {
	$v["icon"]="Images/none.gif";
      } else {
	$useicon=true;
      }
      $v["issubmenu"]=false;
      $v["descr"]=ucfirst(utf8_encode($v["descr"]));
      $v["tconfirm"]=str_replace("\n","\\n",utf8_encode($v["tconfirm"]));
      if (! isset($v["visibility"])) $v["visibility"]="";
      if (! isset($v["confirm"])) $v["confirm"]="";

      if (! isset($v["jsfunction"])) $v["jsfunction"]="";
      if (! isset($v["barmenu"])) $v["barmenu"]="";
      if (! isset($v["url"])) $v["url"]="";
      if (! isset($v["separator"])) $v["separator"]=false;
      if ((!isset($v["idlink"])) || ($v["idlink"]=="")) $v["idlink"]=$k;
      if ((!isset($v["target"])) || ($v["target"]=="")) $v["target"]=$k;
      if ((!isset($v["mwidth"])) || ($v["mwidth"]=="")) $v["mwidth"]=$action->getParam("FDL_HD2SIZE",300);
      if ((!isset($v["mheight"])) || ($v["mheight"]=="")) $v["mheight"]=$action->getParam("FDL_VD2SIZE",400);
      if ((isset($v["url"])) && ($v["url"]!="")) $v["URL"]=true;
      else 	$v["URL"]=false;
      
      if ($noctrlkey) {
	if ($v["visibility"]==POPUP_CTRLACTIVE) {
	  $v["submenu"]=N_("menuctrlkey");
	  $v["visibility"]=POPUP_ACTIVE;
	}
      }
      
      if ((isset($v["jsfunction"])) && ($v["jsfunction"]!="")) $v["JSFT"]=true;
      else $v["JSFT"]=false;
      $v["smid"]="";
      if ((isset($v["submenu"])) && ($v["submenu"]!="")) {
	$smid=base64_encode($v["submenu"]);
	$v["smid"]=$smid;
	if (! isset($tsubmenu[$smid])) {
	  $tsubmenu[$smid]=array("idlink"=>$smid,
				 "descr"=>ucfirst(utf8_encode(_($v["submenu"]))),
				 "visibility"=>false,
				 "confirm"=>false,
				 "jsfunction"=>false,
				 "barmenu"=>false,
				 "url"=>false,
				 "target"=>false,
				 "mwidth"=>false,
				 "mheight"=>false,
				 "smid"=>false,
				 "tconfirm"=>false,
				 "issubmenu"=>false);
	}

	if (! isset($tsubmenu[$smid]["displayed"])) {
	  $tsubmenu[$smid]["displayed"]=true;
	  $tsubmenu[$smid]["issubmenu"]=true;
	  $tsubmenu[$smid]["URL"]=false;
	  $tsubmenu[$smid]["JSFT"]=false;
	  $tsubmenu[$smid]["separator"]=false;
	  if ($noctrlkey && ($v["submenu"]=="menuctrlkey")) {
	    $rlinkbottom[]=$tsubmenu[$smid];
	  } else {
	    $rlink[]=$tsubmenu[$smid];
	  }

	}
      }
  
      $rlink[]=$v;
    }
  }

  if ($noctrlkey) {
    // ctrlkey submenu at bottom
    $rlink=array_merge($rlink,$rlinkbottom);
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