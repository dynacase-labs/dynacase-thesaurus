<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: viewicard.php,v 1.4 2005/03/04 17:15:51 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */


// ---------------------------------------------------------------
// $Id: viewicard.php,v 1.4 2005/03/04 17:15:51 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/viewicard.php,v $
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
/*
include_once("FDL/Class.WDoc.php");
include_once("Class.QueryDb.php");
include_once("FDL/freedom_util.php");
include_once("FDL/Lib.Dir.php");
include_once("VAULT/Class.VaultFile.php");*/
include_once("FREEDOM/freedom_card.php");

// -----------------------------------
function viewicard(&$action) {
  // -----------------------------------
  global $action;
  // Get All Parameters
  $xml = GetHttpVars("xml");
  $famid = GetHttpVars("famid");


  //$xml=stripslashes($xml);
  //$xml=ltrim($xml);
  //  printf("<br>[$xml]");
  $xml=base64_decode(trim($xml));

  $famid = GetHttpVars("famid"); 
 
  $dbaccess = $action->GetParam("FREEDOM_DB");
   $idoc= createDoc($dbaccess,$famid);///new doc
 $action->lay->Set("TITLE",$idoc->title);
 


  $idoc=fromxml($xml,$idoc);
  $idoc->doctype='T';
  $idoc->Add();
  $idoc->SetTitle($idoc->title);

  redirect($action,GetHttpVars("redirect_app","FDL"),
	     GetHttpVars("redirect_act","IMPCARD&dochead=no&id=".$idoc->id),
	     $action->GetParam("CORE_STANDURL"));
  //printf($idoc->title);
  SetHttpVar("id",$idoc->id);
  // $action->lay = new Layout("FREEDOM/Layout/freedom_card.xml",$action);
 
  //freedom_card($action);

}
?>