<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: exportfile.php,v 1.8 2003/08/18 15:47:03 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: exportfile.php,v 1.8 2003/08/18 15:47:03 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Fdl/exportfile.php,v $
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
include_once("VAULT/Class.VaultFile.php");

// --------------------------------------------------------------------
function exportfile(&$action) 
// --------------------------------------------------------------------
{
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("docid",0);
  $attrid = GetHttpVars("attrid",0);
  $vaultid = GetHttpVars("vaultid",0);
  $index = GetHttpVars("index");

  $isControled=false;


  if ($vaultid == 0) {

    $doc= new Doc($dbaccess,$docid);
    // ADD CONTROL ACCESS HERE
    $err = $doc->control("view");
    if ($err != "") $action->exiterror($err);
    $isControled=true;;
    $ovalue = $doc->getValue($attrid);

    if (($index !== "") && ($index >= 0)) {
      $tvalue = explode("\n",$ovalue);
      $ovalue= $tvalue[$index];
    }
    
    if ($ovalue == "") $action->exiterror(_("no file referenced"));
    
    ereg ("(.*)\|(.*)", $ovalue, $reg);
    $vaultid= $reg[2];
    $mimetype=$reg[1];
  } else {
    $mimetype = "";
  }

  DownloadVault($action, $vaultid, $isControled, $mimetype);

    
  exit;
    
  
    
}


  // --------------------------------------------------------------------
function exportfirstfile(&$action) 
  // --------------------------------------------------------------------
{
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("docid",0);


    $doc= new Doc($dbaccess,$docid);
  // ADD CONTROL ACCESS HERE
    $err = $doc->control("view");
    if ($err != "") $action->exiterror($err);

  $isControled=true;
  $attr = $doc->GetFirstFileAttributes();

  $ovalue = $doc->getValue($attr->id);

    
  if ($ovalue == "") $action->exiterror(_("no file referenced"));
    
  ereg ("(.*)\|(.*)", $ovalue, $reg);
  $vaultid= $reg[2];
  $mimetype=$reg[1];

  
  DownloadVault($action, $vaultid, $isControled, $mimetype);
        
  
    
}


  // --------------------------------------------------------------------
function DownloadVault(&$action, $vaultid, $isControled, $mimetype="") {
  // --------------------------------------------------------------------
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $vf = new VaultFile($dbaccess, "FREEDOM");

  if ($vf -> Retrieve ($vaultid, $info) != "") {    
      Http_DownloadFile("FREEDOM/Images/doc.gif", "unknow", "image/gif");
  } else
    {
      //Header("Location: $url");
      if ($isControled || ( $info->public_access)) {
	Http_DownloadFile($info->path, $info->name, $mimetype);
	if (! $info->public_access)   AddlogMsg(sprintf(_("%s has be sended"),$info->name));
      } else {
	$action->exiterror(_("file must be controlled : read permission needed"));
      }
    }

  exit;
}

?>
