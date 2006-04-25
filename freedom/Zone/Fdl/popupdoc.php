<?php
/**
 * Specific menu for family
 *
 * @author Anakeen 2000 
 * @version $Id: popupdoc.php,v 1.3 2006/04/25 09:55:32 eric Exp $
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
  foreach ($tlink as $k=>$v) {
    if ($v["visibility"]==POPUP_INVISIBLE) unset($tlink[$k]);
    else {
      if ((!isset($v["icon"])) || ($v["icon"]=="")) {
	$tlink[$k]["icon"]="Images/none.gif";
      } else {
	$useicon=true;
      }
      
      $tlink[$k]["descr"]=utf8_encode($v["descr"]);
      $tlink[$k]["tconfirm"]=utf8_encode($v["tconfirm"]);
      if ((!isset($v["idlink"])) || ($v["idlink"]=="")) $tlink[$k]["idlink"]=$k;
      if ((!isset($v["target"])) || ($v["target"]=="")) $tlink[$k]["target"]=$k;
      $tlink[$k]["smid"]="";
      if ((isset($v["submenu"])) && ($v["submenu"]!="")) {
	$smid=base64_encode($v["submenu"]);
	$tlink[$k]["smid"]=$smid;
	if (! isset($tsubmenu[$smid])) {
	  $tsubmenu[$smid]=array("idmenu"=>$smid,
				 "labelmenu"=>utf8_encode($v["submenu"]));
	}
      }
    }
  }

          

  $action->lay->Set("ICONS",$useicon);
  $action->lay->SetBlockData("ADDLINK",$tlink);
  $action->lay->SetBlockData("SUBMENU",$tsubmenu);
  $action->lay->SetBlockData("SUBDIVMENU",$tsubmenu);
  $action->lay->Set("count",count($tlink));
  $action->lay->Set("SEP",true);// to see separator
  $action->lay->set("delay",microtime_diff(microtime(),$mb));
}


?>