<?php

// ---------------------------------------------------------------
// $Id: freedom_iedit2.php,v 1.2 2003/06/27 09:43:02 mathieu Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_iedit2.php,v $
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
include_once("FDL/Class.WDoc.php");
include_once("Class.QueryDb.php");
include_once("FDL/freedom_util.php");
include_once("FDL/Lib.Dir.php");
include_once("VAULT/Class.VaultFile.php");


// -----------------------------------
function freedom_iedit2(&$action) {
  // -----------------------------------
  global $action;


  // Get All Parameters
  $xml = GetHttpVars("xml");
 
  $famid = GetHttpVars("famid");
  //printf($famid);
  $type_attr=GetHttpVars("type_attr");
  $action->lay->Set("type_attr",$type_attr);

  $mod=GetHttpVars("mod");
  $action->lay->Set("mod",$mod);


  $attrid=GetHttpVars("attrid");
  //printf($attrid);
  $action->lay->Set("attrid",$attrid);

  $action->lay->Set("xml_initial",$xml);

 //   $xml=stripslashes($xml);
 // $xml=ltrim($xml);
	$temp=base64_decode($xml);
	$entete="<?xml version=\"1.0\" encoding=\"ISO-8859-1\" standalone=\"yes\" ?>";
	$xml=$entete;
	$xml.=$temp;
	

	//	printf($xml);

 
  $famid = GetHttpVars("famid");
  // printf($famid);
 
  $dbaccess = $action->GetParam("FREEDOM_DB");
   $idoc= createDoc($dbaccess,$famid);///new doc
 



  $idoc=fromxml($xml,$idoc);
  $idoc->doctype='T';
  //printf($idoc->fromid);
  $idoc->Add();
  SetHttpVar("id",$idoc->id);
  $idoc->SetTitle($idoc->title);

  $action->lay->Set("docid",$idoc->id);
  $action->lay->Set("TITLE",$idoc->title);
  $action->lay->Set("iconsrc", $idoc->geticon()); 
  $action->lay->Set("famid", $famid);

  // $xml_initial=addslashes(htmlentities($xml));


    

}
?>
