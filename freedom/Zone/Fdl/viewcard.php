<?php
// ---------------------------------------------------------------
// $Id: viewcard.php,v 1.40 2003/07/24 12:53:00 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/viewcard.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2001
// O*O  Anakeen development team
//  O   dev@anakeen.com
// ---------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------

include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");

include_once("Class.TableLayout.php");
include_once("Class.QueryDb.php");
include_once("Class.QueryGen.php");
include_once("FDL/freedom_util.php");
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
  
  $ulink = (GetHttpVars("ulink",'2')); // add url link
  $target = GetHttpVars("target"); // may be mail
  $reload = ($action->read("reload$docid","N")=="Y"); // need reload


  if ($ulink == "N") $ulink = false;
  else  if ($ulink == "Y") $ulink = 1;

  // Set the globals elements

 $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/idoc.js");
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
  
 
    // set view zone
    if ($zonebodycard == "") {
      $zonebodycard = $doc->defaultview;
    }
    if ($zonebodycard == "") {
      $zonebodycard ="FDL:VIEWBODYCARD";
    }
  
  if ($doc->usefor=="D") $zonebodycard="FDL:VIEWBODYCARD"; // always default view for default document

  // with doc head ?
  if (GetHttpVars("dochead")=="")   $dochead=  (! ereg("[A-Z]+:[^:]+:T", $zonebodycard, $reg))||$props;
  else $dochead = (GetHttpVars("dochead",'Y') == "Y");

  
  if ($doc->doctype == 'Z') {
    $err =_("This document has been deleted");
     $err .= "\n\n".$doc->comment;
  } else {
    $err=$doc->refresh();
  }
  $action->lay->set("LGTEXTERROR", strlen($err));
  $action->lay->set("TEXTERROR", nl2br($err));
  $action->lay->Set("ZONEBODYCARD", $doc->viewDoc($zonebodycard,$target,$ulink,$abstract));
  
 

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");





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
  if ($doc->fromid > 0) {
    $cdoc = $doc->getFamDoc();
    $action->lay->Set("classtitle", $cdoc->title);
  } else {
    $action->lay->Set("classtitle", _("no family"));
  }
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
    else
      $action->lay->Set("profile", _("specific control"));
      
  }
  $action->lay->Set("profid", abs($doc->profid));
  $action->lay->Set("postitid", $doc->postitid);
  
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
    $nprop=4;
  } else {
    $listattr = $doc->GetNormalAttributes();
    if ($props) $action->lay->SetBlockData("ALLPROP",array(array("boo"=>1)));
    $nprop=7;
    
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
