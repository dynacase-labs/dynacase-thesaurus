<?php
// ---------------------------------------------------------------
// $Id: viewcard.php,v 1.9 2002/07/11 13:25:45 eric Exp $
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
include_once("FDL/Class.DocValue.php");

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
  $props = (GetHttpVars("props",'Y') == "Y"); // view doc properties
  $zonebodycard = GetHttpVars("zone"); // define view action

  // Set the globals elements

  $baseurl=$action->GetParam("CORE_BASEURL");
  $standurl=$action->GetParam("CORE_STANDURL");
  $dbaccess = $action->GetParam("FREEDOM_DB");


  $doc = new Doc($dbaccess, $docid);

  if ($doc->doctype == 'C') {
    $zonebodycard ="FDL:VIEWFAMCARD";
  } else {
    // set view zone
    if ($zonebodycard == "") {
      $zonebodycard = $doc->dviewzone;
    }
    if ($zonebodycard == "") {
      $zonebodycard ="FDL:VIEWBODYCARD";
    }
  }
  $action->lay->Set("ZONEBODYCARD", $zonebodycard);
  



  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");




  $err=$doc->refresh();
  if ($err != "") $action->exitError($err);
  //------------------------------
  // display document attributes
  $action->lay->Set("reference", $doc->initid);

  $action->lay->Set("revision", $doc->revision);
  
  if ($action->GetParam("CORE_LANG") == "fr_FR") { // date format depend of locale
    setlocale (LC_TIME, "fr_FR");
    $action->lay->Set("revdate", strftime ("%a %d %b %H:%M",$doc->revdate));
  } else {
    $action->lay->Set("revdate", strftime ("%x %T",$doc->revdate));

  }

  $action->lay->Set("comment", $doc->comment);


  if ($doc->locked > 0) {
    $user = new User("", $doc->locked);
    $action->lay->Set("locked", $user->firstname." ".$user->lastname);
  } else {
    if ($doc->locked < 0) {
      $action->lay->Set("locked", _("fixed"));
    } else {
      $action->lay->Set("locked", _("nobody"));
    }
  }
  if ($doc->fromid > 0) {
    $cdoc = new Doc($dbaccess, $doc->fromid);
    $action->lay->Set("classtitle", $cdoc->title);
  } else {
    $action->lay->Set("classtitle", _("no family"));
  }
  if (($doc->profid > 0) && ($doc->profid != $doc->id)) {
    $pdoc = new Doc($dbaccess, $doc->profid);
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
  $action->lay->Set("profid", $doc->profid);
  $action->lay->Set("iconalt","icon");
  
  $action->lay->Set("iconsrc", $doc->geticon());

  if ($doc->fromid > 0)    $action->lay->Set("cid", $doc->fromid);
  else   $action->lay->Set("cid", $doc->id);
  

  if (count($doc->transitions) > 0) { // see only if it is a transitionnal doc
    $action->lay->Set("state", $action->text($doc->state));
    $action->lay->Set("viewstate", "inherit");
  } else {
    $action->lay->Set("viewstate", "none");
  }
    




      

      
      
    
 

  $action->lay->Set("TITLE", $doc->title);
  $action->lay->Set("id", $docid);




  

  if ($props) {
    $action->lay->SetBlockData("PROP",array(array("boo"=>1)));
  }
  if ($abstract){
    // only 3 properties for abstract mode
    $listattr = $doc->GetAbstractAttributes();
    $nprop=4;
  } else {
    $listattr = $doc->GetAttributes();
    if ($props) $action->lay->SetBlockData("ALLPROP",array(array("boo"=>1)));
    $nprop=7;
    
  }
  // see locker for lockable document
  if ($doc->isRevisable())  {
    if ($props) $action->lay->SetBlockData("LOCK",array(array("boo"=>1)));  
  } else  $nprop-=2; // revision & locker
  $action->lay->Set("nprop",$nprop);  

    
  

  $owner = new User("", abs($doc->owner));
  $action->lay->Set("username", $owner->firstname." ".$owner->lastname);



}


?>
