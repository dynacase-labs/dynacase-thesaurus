<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_iedit2.php,v 1.4 2005/03/04 17:15:51 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */


// ---------------------------------------------------------------
// $Id: freedom_iedit2.php,v 1.4 2005/03/04 17:15:51 eric Exp $
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
  $action->lay->Set("attrid",$attrid);

  $action->lay->Set("xml_initial",$xml);

  $temp=base64_decode(trim($xml));
  $entete="<?xml version=\"1.0\" encoding=\"ISO-8859-1\" standalone=\"yes\" ?>";
  $xml=$entete;
  $xml.=$temp;
	


 
  $famid = GetHttpVars("famid");
 
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $idoc= createDoc($dbaccess,$famid,false);///new doc
 



  $idoc=fromxml($xml,$idoc);
  $idoc->doctype='T';
  $idoc->Add();
  SetHttpVar("id",$idoc->id);
  $idoc->SetTitle($idoc->title);

  $action->lay->Set("docid",$idoc->id);
  $action->lay->Set("TITLE",$idoc->title);
  $action->lay->Set("STITLE",addslashes($idoc->title));
  $action->lay->Set("iconsrc", $idoc->geticon()); 
  $action->lay->Set("famid", $famid);

  // $xml_initial=addslashes(htmlentities($xml));


    

}
?>
