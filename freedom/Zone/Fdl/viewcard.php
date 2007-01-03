<?php
/**
 * View document zone
 *
 * @author Anakeen 2000 
 * @version $Id: viewcard.php,v 1.75 2007/01/03 19:39:13 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");

include_once("Class.TableLayout.php");
include_once("Class.QueryDb.php");
include_once("Class.QueryGen.php");
include_once("FDL/freedom_util.php");
include_once("FDL/family_help.php");
include_once("VAULT/Class.VaultFile.php");

// -----------------------------------
// -----------------------------------
function viewcard(&$action) {
  // -----------------------------------


  // GetAllParameters
  $docid = GetHttpVars("id");
  $abstract = (GetHttpVars("abstract",'N') == "Y");// view doc abstract attributes
  $props = (GetHttpVars("props",'N') == "Y"); // view doc properties
  $zonebodycard = GetHttpVars("zone"); // define view action
  $vid = GetHttpVars("vid"); // special controlled view
  
  $ulink = (GetHttpVars("ulink",'2')); // add url link
  $target = GetHttpVars("target"); // may be mail
  $reload = ($action->read("reload$docid","N")=="Y"); // need reload

  if ($target != "mail") $action->lay->setBlockData("MVIEW",array(array("zou")));
  $action->lay->set("fhelp",($action->Read("navigator","")=="EXPLORER")?"_blank":"fhidden");
 

  if ($ulink == "N") $ulink = false;
  else  if ($ulink == "Y") $ulink = 1;

  // Set the globals elements

 $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/WHAT/Layout/AnchorPosition.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/resizeimg.js");
 // $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/idoc.js");
 $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/common.js");
 //pour les idocs
 $jsfile=$action->GetLayoutFile("viewicard.js");
 $jslay = new Layout($jsfile,$action);
 $action->parent->AddJsCode($jslay->gen());

  $baseurl=$action->GetParam("CORE_BASEURL");
  $standurl=$action->GetParam("CORE_STANDURL");
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/DHTMLapi.js");
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/iframe.js");
   if ($reload) {
     $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/reload.js");
     $action->unregister("reload$docid");
   } else {
     $action->lay->set("refreshfld",  GetHttpVars("refreshfld"));
   }

  $doc = new_Doc($dbaccess, $docid);
  if (! $doc->isAffected()) $action->exitError(sprintf(_("cannot see unknow reference %s"),$docid));

  $err = $doc->control("view");
  if ($err != "") $action->exitError($err);
  
  if ($doc->isConfidential()) {      
    redirect($action,"FDL",
	     "FDL_CONFIDENTIAL&id=".$doc->id);
  }

  if ($doc->cvid > 0) {
    // special controlled view
    $cvdoc= new_Doc($dbaccess, $doc->cvid);
    $cvdoc->set($doc);
    if ($vid != "") {    
      $err = $cvdoc->control($vid); // control special view
      if ($err != "") $action->exitError($err);  
    } else  {
      // search preferred view	
      $tv=$cvdoc->getAValues("CV_T_VIEWS");
      // sort
      usort($tv,"cmp_cvorder3");
      foreach ($tv as $k=>$v) {
	if ($v["cv_order"]>0) {
	  if ($v["cv_kview"]=="VCONS") {
	    $err = $cvdoc->control($v["cv_idview"]); // control special view
	    if ($err == "") {
	      $vid=$v["cv_idview"];
	      setHttpVar("vid",$vid);
	      break;
	    }
	  }
	}
      }      
    } 
    if ($vid != "") {
      $tview = $cvdoc->getView($vid);
      $doc->setMask($tview["CV_MSKID"]);
      if ($zonebodycard == "") $zonebodycard=$tview["CV_ZVIEW"];
    }
  }
  // set emblem
  $action->lay->set("emblem",$doc->getEmblem());
  
    // set view zone
    if ($zonebodycard == "") {
      $zonebodycard = $doc->defaultview;
    }
    if ($zonebodycard == "") {
      $zonebodycard ="FDL:VIEWBODYCARD";
    }
  

  if ($doc->defaultmview != "") $action->lay->set("mzone", $doc->defaultmview);
  else $action->lay->set("mzone", $zonebodycard);
  // with doc head ?
  if (GetHttpVars("dochead")=="")   $dochead=  (! ereg("[A-Z]+:[^:]+:[T|U]", $zonebodycard, $reg));
  else $dochead = (GetHttpVars("dochead",'Y') == "Y");

  
  if ($doc->doctype == 'Z') {
    $err =_("This document has been deleted");
  } else {    
    // disabled control just to refresh
    $doc->disableEditControl();
    $err=$doc->refresh();
    $doc->enableEditControl();
  }
  $action->lay->set("LGTEXTERROR", strlen($err));
  $action->lay->set("TEXTERROR", nl2br($err));
  $action->lay->Set("ZONEBODYCARD", $doc->viewDoc($zonebodycard,$target,$ulink,$abstract));
  
 

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_STANDURL")."app=FDL&action=VIEWDOCJS");





  //------------------------------
  // display document attributes
  $action->lay->Set("reference", $doc->initid.(( $doc->name=="")?"":" ({$doc->name})"));

  $action->lay->Set("revision", $doc->revision);
  
  $action->lay->Set("lockedid",0);
  $action->lay->Set("comment", $doc->comment);

  if ($doc->confidential >0) $action->lay->Set("locked", _("confidential"));
  else if ($doc->control("edit") != "") $action->lay->Set("locked", _("read only"));
  else if ($doc->locked == 0) {
    $action->lay->Set("locked", _("nobody"));
  } else {
    if ($doc->locked == -1) {
      $action->lay->Set("locked", _("fixed"));
    } else {
      $user = new User("", abs($doc->locked));
      $action->lay->Set("locked", $user->firstname." ".$user->lastname);
      $action->lay->Set("lockedid", $user->fid);
    }
  }
  $action->lay->Set("dhelp", "none");
  if ($doc->fromid > 0) {
    $cdoc = $doc->getFamDoc();
    $action->lay->Set("classtitle", $cdoc->title);
    if (getFamilyHelpFile($action,$doc->fromid) ) {
      
      $action->lay->Set("dhelp", "");
      $action->lay->Set("helpid", $doc->fromid);
    }
  } else {
    $action->lay->Set("classtitle", _("no family"));
  }
  $action->lay->Set("postitid", $doc->postitid);
  
  
  if (($target=="mail") && ($doc->icon != "")) $action->lay->Set("iconsrc", "cid:icon");
  else $action->lay->Set("iconsrc", $doc->geticon());

  if ($doc->fromid > 0)    $action->lay->Set("cid", $doc->fromid);
  else   $action->lay->Set("cid", $doc->id);
  
  $action->lay->Set("viewstate", "none");
  $action->lay->Set("state", "");

  $state=$doc->getState();
  if ($state) { // see only if it is a transitionnal doc
    if (($doc->locked == -1)||($doc->lmodify != 'Y'))    $action->lay->Set("state", $action->text($state));
    else $action->lay->Set("state", sprintf(_("current (<i>%s</i>)"),$action->text($state)));
    $action->lay->Set("viewstate", "inherit");
    $action->lay->Set("wid", ($doc->wid>0)?$doc->wid:$doc->state);
  } 
  $action->lay->Set("version", $doc->version);

  $action->lay->Set("TITLE", $doc->title);
  $action->lay->Set("id", $docid);

  

  if ($abstract){
    // only 3 properties for abstract mode
    $listattr = $doc->GetAbstractAttributes();
  } else {
    $listattr = $doc->GetNormalAttributes();    
  }
 

    
  // see or don't see head
  if ($dochead)  $action->lay->SetBlockData("HEAD",array(array("boo"=>1))); 
  if ($ulink)  $action->lay->SetBlockData("ACTIONS",array(array("boo"=>1))); 

  $action->lay->Set("amail",(($doc->usefor != "P")&&
			     ($doc->control('send')==""))?"inline":"none");

  

  // update access date
  $doc->adate=$doc->getTimeDate(0,true);
  $doc->modify(true,array("adate"),true);
  if ($doc->delUTag($action->user->id,"TOVIEW")=="") {
    $err=$doc->addUTag($action->user->id,"VIEWED");
  }

}


function cmp_cvorder3($a, $b) {
   if ($a["cv_order"] == $b["cv_order"]) {
       return 0;
   }
   return ($a["cv_order"] < $b["cv_order"]) ? -1 : 1;
}
?>
