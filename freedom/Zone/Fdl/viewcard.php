<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: viewcard.php,v 1.55 2005/03/04 17:18:47 eric Exp $
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
 $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/idoc.js");
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

  $doc = new Doc($dbaccess, $docid);
  if (! $doc->isAffected()) $action->exitError(sprintf(_("cannot see unknow reference %s"),$docid));

  $err = $doc->control("view");
  if ($err != "") $action->exitError($err);
  
  if (($vid != "") && ($doc->cvid > 0)) {
    // special controlled view
    $cvdoc= new Doc($dbaccess, $doc->cvid);
    $cvdoc->set($doc);
    
    $err = $cvdoc->control($vid); // control special view
    if ($err != "") $action->exitError($err);
  

    $tview = $cvdoc->getView($vid);
    $doc->setMask($tview["CV_MSKID"]);
    if ($zonebodycard == "") $zonebodycard=$tview["CV_ZVIEW"];
  }

  // set emblem
  if ($doc->locked == -1) $action->lay->set("emblem", $action->getImageUrl("revised.gif"));
  else if ((abs($doc->locked) == $action->parent->user->id)) $action->lay->set("emblem",$action->getImageUrl("clef1.gif"));
  else if ($doc->locked != 0) $action->lay->set("emblem",$action->getImageUrl("clef2.gif"));
  else if ($doc->control("edit") != "") $action->lay->set("emblem",$action->getImageUrl("nowrite.gif"));
  else $action->lay->set("emblem",$action->getImageUrl("1x1.gif"));
    // set view zone
    if ($zonebodycard == "") {
      $zonebodycard = $doc->defaultview;
    }
    if ($zonebodycard == "") {
      $zonebodycard ="FDL:VIEWBODYCARD";
    }
  
  if ($doc->usefor=="D") $zonebodycard="FDL:VIEWBODYCARD"; // always default view for default document

  if ($doc->defaultmview != "") $action->lay->set("mzone", $doc->defaultmview);
  else $action->lay->set("mzone", $zonebodycard);
  // with doc head ?
  if (GetHttpVars("dochead")=="")   $dochead=  (! ereg("[A-Z]+:[^:]+:T", $zonebodycard, $reg))||$props;
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
  $action->lay->Set("reference", $doc->initid);

  $action->lay->Set("revision", $doc->revision);
  
  if ($action->GetParam("CORE_LANG") == "fr_FR") { // date format depend of locale
    setlocale (LC_TIME, "fr_FR");
    $action->lay->Set("revdate", strftime ("%a %d %b %Y %H:%M",$doc->revdate));
  } else {
    $action->lay->Set("revdate", strftime ("%x %T",$doc->revdate));
  }

  $action->lay->Set("comment", $doc->comment);


  if ($doc->locked == 0) {
      $action->lay->Set("locked", _("nobody"));
  } else {
    if ($doc->locked == -1) {
      $action->lay->Set("locked", _("fixed"));
    } else {
      $user = new User("", abs($doc->locked));
      $action->lay->Set("locked", $user->firstname." ".$user->lastname);
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
  $action->lay->Set("profid", abs($doc->profid));
  if ((abs($doc->profid) > 0) && ($doc->profid != $doc->id)) {
    $pdoc = new Doc($dbaccess, abs($doc->profid));
    $action->lay->Set("profile", $pdoc->title);
    $action->lay->Set("displaylprof", "inherit");
    $action->lay->Set("displayprof", "none");
  } else {
    $action->lay->Set("displaylprof", "none");
    $action->lay->Set("displayprof", "inherit");
    if ($doc->profid == 0)
      $action->lay->Set("profile", _("no access control"));
    else {
      if ($doc->dprofid==0) $action->lay->Set("profile", _("specific control"));
      else {
	
	$action->lay->Set("displaylprof", "inherit");
	$action->lay->Set("displayprof", "none");
	$action->lay->Set("profile", _("dynamic control"));
	$action->lay->Set("profid", abs($doc->dprofid));
      }
    }
  }
  $action->lay->Set("postitid", $doc->postitid);
  
  if ($doc->cvid == 0) {
    $action->lay->Set("cview", _("no view control"));
    $action->lay->Set("displaylcv", "none");
    $action->lay->Set("displaycv", "");
  } else {
    $cvdoc = new Doc($dbaccess, abs($doc->cvid));
    $action->lay->Set("cview", $cvdoc->title);
    $action->lay->Set("cvid", $cvdoc->id);
    $action->lay->Set("displaylcv", "");
    $action->lay->Set("displaycv", "none");
  }
  if (($target=="mail") && ($doc->icon != "")) $action->lay->Set("iconsrc", "cid:icon");
  else $action->lay->Set("iconsrc", $doc->geticon());

  if ($doc->fromid > 0)    $action->lay->Set("cid", $doc->fromid);
  else   $action->lay->Set("cid", $doc->id);
  

  if ($doc->wid > 0) { // see only if it is a transitionnal doc
    $action->lay->Set("state", $action->text($doc->state));
    $action->lay->Set("viewstate", "inherit");
    $action->lay->Set("wid", $doc->wid);
  } else {
    $action->lay->Set("viewstate", "none");
    $action->lay->Set("state", "");
  }
    




      

      
      
    
 

  $action->lay->Set("TITLE", $doc->title);
  $action->lay->Set("id", $docid);




  

  if ($props) {
    $action->lay->SetBlockData("PROP",array(array("boo"=>1)));
  }
  $action->lay->Set("dicon",$props?"none":"inline");
  if ($abstract){
    // only 3 properties for abstract mode
    $listattr = $doc->GetAbstractAttributes();
    $nprop=5;
  } else {
    $listattr = $doc->GetNormalAttributes();
    if ($props) $action->lay->SetBlockData("ALLPROP",array(array("boo"=>1)));
    $nprop=8;
    
  }
  // see locker for lockable document
  if ($doc->isRevisable())  {
    if ($props) $action->lay->SetBlockData("LOCK",array(array("boo"=>1)));  
  } else  $nprop-=1; // not revision 
  $action->lay->Set("nprop",$nprop);  

    
  // see or don't see head
  if ($dochead)  $action->lay->SetBlockData("HEAD",array(array("boo"=>1))); 
  if ($ulink)  $action->lay->SetBlockData("ACTIONS",array(array("boo"=>1))); 

  $action->lay->Set("amail",(($doc->usefor != "P")&&
			     ($doc->control('send')==""))?"inline":"none");

  $owner = new User("", abs($doc->owner));
  $action->lay->Set("username", $owner->firstname." ".$owner->lastname);



}


?>
