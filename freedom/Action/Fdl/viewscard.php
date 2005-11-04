<?php
/**
 * View standalone document (without popup menu)
 *
 * @author Anakeen 2000 
 * @version $Id: viewscard.php,v 1.7 2005/11/04 09:53:14 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: viewscard.php,v 1.7 2005/11/04 09:53:14 marc Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Fdl/viewscard.php,v $
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


/**
 * View a document without standard header and footer. It is display in raw format
 * @param Action &$action current action
 * @global id Http var : document identificator to see
 * @global latest Http var : (Y|N) if Y force view latest revision
 * @global abstract Http var : (Y|N) if Y view only abstract attribute
 * @global zonebodycard Http var : if set, view other specific representation
 * @global vid Http var : if set, view represention describe in view control (can be use only if doc has controlled view)
 * @global ulink Http var : (Y|N)if N hyperlink are disabled
 * @global target Http var : is set target of hyperlink can change (default _self)
 */
function viewscard(&$action) {

  // GetAllParameters
  $docid = GetHttpVars("id");
  $abstract = (GetHttpVars("abstract",'N') == "Y");// view doc abstract attributes
  $zonebodycard = GetHttpVars("zone"); // define view action
  $ulink = (GetHttpVars("ulink",'Y') == "Y"); // add url link
  $target = GetHttpVars("target"); // may be mail
  $wedit = (GetHttpVars("wedit")=="Y"); // send to be view by word editor
  $fromedit = (GetHttpVars("fromedit","N")=="Y"); // need to compose temporary doc
  $latest = GetHttpVars("latest");
  $tmime = GetHttpVars("tmime", "");  // type mime

  // Set the globals elements

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc = new_Doc($dbaccess, $docid);
  if (($latest == "Y") && ($doc->locked == -1)) {
    // get latest revision
    $docid=$doc->latestId();
    $doc = new_Doc($dbaccess, $docid);
    SetHttpVar("id",$docid);
  } 
  $err = $doc->control("view");
  if ($err != "") $action->exitError($err);
  if ($fromedit) {
    include_once("FDL/modcard.php");

    $doc = $doc->copy(true,false,true);
    $err=setPostVars($doc);
    $doc->modify();
    setHttpVar("id",$doc->id);
  } 
  if ($zonebodycard == "") $zonebodycard=$doc->defaultview;
  if ($zonebodycard == "") $action->exitError(_("no zone specified"));


  $err=$doc->refresh();
  $action->lay->Set("ZONESCARD", $doc->viewDoc($zonebodycard,$target,$ulink,$abstract));
  
 

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");


  if ($wedit) {
    $export_file = uniqid("/tmp/export").".doc";
  
    $of = fopen($export_file,"w+");
    fwrite($of, $action->lay->gen());
    fclose($of);
  
    http_DownloadFile($export_file, chop($doc->title).".html", "application/msword");
  
    unlink($export_file);
    exit;
  }

  if ($tmime!="") {
    header("Content-Type: $tmime");
    print $action->lay->gen();
    exit;
  }

}
?>
