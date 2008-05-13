<?php
/**
 * Specific menu for family
 *
 * @author Anakeen 2000 
 * @version $Id: viewbarmenu.php,v 1.9 2008/05/13 15:02:27 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/popupdocdetail.php");
include_once("FDL/popupfamdetail.php");


function viewbarmenu(&$action) {
  $docid = GetHttpVars("id");
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new_Doc($dbaccess, $docid);
  if ($docid == "") $action->exitError(_("No identificator"));
  if ($doc->doctype=='C') $popup=getpopupfamdetail($action,$docid);
  else {
    if ($doc->specialmenu) {
      if (ereg("(.*):(.*)",$doc->specialmenu,$reg)) {
	$action->getmenulink=true;
	$dir=$reg[1];
	$function=strtolower($reg[2]);
	$file=$function.".php";
	if (include_once("$dir/$file")) {
	  $function($action);
	  $popup=$action->menulink;
	} else {	  
	  AddwarningMsg(sprintf(_("Incorrect specification of special menu : %s"),$doc->specialmenu));
	}
      } else {
	AddwarningMsg(sprintf(_("Incorrect specification of special menu : %s"),$doc->specialmenu));
      }
    } 
  }
  if (!$popup) $popup=getpopupdocdetail($action,$docid);

  foreach ($popup as $k=>$v) {
    if ($v["visibility"]!=POPUP_ACTIVE) unset($popup[$k]);
    else {
      $popup[$k]["menu"]=($v["submenu"]!="");
      if ($popup[$k]["menu"]) {
	$idxmenu=$v["submenu"];
	if (! isset($mpopup[$idxmenu])) {
	  $mpopup[$idxmenu]=true;
	  $popup[$k]=array("idlink"=>$idxmenu,
			   "descr"=>ucfirst((_($v["submenu"]))),
			   "visibility"=>false,
			   "confirm"=>false,
			   "jsfunction"=>false,
			   "title"=>_("Click to view menu"),
			   "barmenu"=>false,
			   "m"=>"",
			   "url"=>false,
			   "target"=>false,
			   "mwidth"=>false,
			   "mheight"=>false,
			   "smid"=>false,
			   "menu"=>true,
			   "tconfirm"=>false,
			   "issubmenu"=>false);
	} else {
	  unset($popup[$k]);
	}
      } else {
	$popup[$k]["idlink"]=$k;
	if (!isset($v["jsfunction"])) $popup[$k]["jsfunction"]='';
	if ($v["mwidth"]=="")  $popup[$k]["mwidth"]=$action->getParam("FDL_HD2SIZE");
	if ($v["mheight"]=="")  $popup[$k]["mheight"]=$action->getParam("FDL_VD2SIZE");
	if ($v["target"]=="")  $popup[$k]["target"]="$k$docid";
	$popup[$k]["descr"]=ucfirst($v["descr"]);
	$popup[$k]["title"]=ucfirst($v["title"]);
	$popup[$k]["m"]=($v["barmenu"]=="true")?"m":"";
	$popup[$k]["ISJS"]=($v["jsfunction"]!="");
	$popup[$k]["confirm"]=($v["confirm"]=="true");
	$popup[$k]["tconfirm"]=str_replace("'","&rsquo;",$v["tconfirm"]);
      }
    }
  }
  $action->lay->setBlockData("LINKS",$popup);
  $action->lay->set("id",$docid);
  
  $action->lay->Set("canmail",(($doc->usefor != "P")&& ($doc->control('send')=="")));

}