<?php
/**
 * View tab in portfolio
 *
 * @author Anakeen 2000 
 * @version $Id: foliotab.php,v 1.8 2007/10/19 15:20:34 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */





include_once("FDL/Lib.Dir.php");
include_once("FDL/freedom_util.php");  




// -----------------------------------
function foliotab(&$action) {
  // -----------------------------------

  // Get all the params      
  $docid=GetHttpVars("id",0); // portfolio id

  $dbaccess = $action->GetParam("FREEDOM_DB");

  include_once("FDL/popup_util.php");
  $nbfolders=1;
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/common.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/resizeimg.js");
 
  $doc = new_Doc($dbaccess,$docid);
  $action->lay->set("docid",$docid);
  $action->lay->set("dirid",$doc->initid);
  $action->lay->set("title",$doc->title);


  $child = getChildDir($dbaccess,$action->user->id,$doc->initid, false,"TABLE");
  
  if ($action->Read("navigator")=="EXPLORER") { // different tab class for PNG transparency
    $tabonglet = "ongletvgie";
    $tabongletsel = "ongletvsie";
  } else {
    $tabonglet = "ongletvg";
    $tabongletsel = "ongletvs";
  }

  $action->lay->set("tabonglets",$tabongletsel);
  $action->lay->set("icon",$doc->getIcon());
  $ttag=array();
  while(list($k,$v) = each($child)) {
	$icolor=getv($v,"gui_color");
      if ($v["initid"] != $doc->initid) {
      $ttag[$v["initid"]] = array(
		      "tabid"=>$v["initid"],
		      "doctype"=>$v["doctype"],
		      "TAG_LABELCLASS" => $v["doctype"]=="S"?"searchtab":"",
		      "tag_cellbgclass"=>($v["id"] ==$docid)?$tabongletsel:$tabonglet,
		      "icolor"=>$icolor,
		      "icontab"=>$doc->getIcon($v["icon"]),
		      "tabtitle"=>str_replace(" ","&nbsp;",$v["title"]));
      }
   
  }

  $action->lay->setBlockData("TAG",$ttag);
  $action->lay->setBlockData("nbcol",count($ttag)+1);
}

?>