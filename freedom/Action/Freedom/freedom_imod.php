<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_imod.php,v 1.6 2005/06/28 08:37:46 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: freedom_imod.php,v 1.6 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_imod.php,v $
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

//include_once("FDL/freedom_util.php");
include_once("FDL/modcard.php");
include_once("FDL/Class.Dir.php");
include_once("FDL/Class.DocFam.php");
include_once("FDL/Class.Doc.php");



// -----------------------------------
function freedom_imod(&$action) {

  $famid=GetHttpVars("famid");
  $xml = GetHttpVars("xml_initial");
  $attrid = GetHttpVars("attrid");
  $noredirect = GetHttpVars("noredirect"); // if true its a quick save
  
  $action->lay->Set("attrid",$attrid);
  $action->lay->Set("famid",$famid);

  $type_attr=GetHttpVars("type_attr");
  $action->lay->Set("type_attr",$type_attr);

  $mod=GetHttpVars("mod");
  $action->lay->Set("mod",$mod);
  if ($noredirect)  $action->lay->Set("close","no");
  
  $dbaccess = $action->GetParam("FREEDOM_DB");


  $idoc=fromxml($dbaccess,$xml,$famid,true);


  SetHttpVar("id",$idoc->id);

  $err = modcard($action, $ndocid); // ndocid change if new doc
  if ($err != "")  $action-> ExitError($err);


  $idoc= new_Doc($dbaccess,$idoc->id);

   
  $idoc->RefreshTitle();

  $action->lay->Set("title",htmlentities(addslashes($idoc->title)));

  $xml2=$idoc->toxml(false,$attrid);

  $xml_send=base64_encode($xml2);
  $action->lay->Set("xml2",$xml_send);
  $action->lay->gen();
    
   
  
}



?>
