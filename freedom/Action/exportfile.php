<?php
// ---------------------------------------------------------------
// $Id: exportfile.php,v 1.1 2001/11/09 09:41:13 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Attic/exportfile.php,v $
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
// $Log: exportfile.php,v $
// Revision 1.1  2001/11/09 09:41:13  eric
// gestion documentaire
//

// ---------------------------------------------------------------

include_once("FREEDOM/Class.Doc.php");
include_once("FREEDOM/Class.DocAttr.php");
include_once("FREEDOM/Class.FileDisk.php");
function exportfile(&$action) 
{
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("docid",0);
  $attrid = GetHttpVars("attrid",0);
  $vaultid = GetHttpVars("vaultid",0);


    $doc= new Doc($dbaccess,$docid);
  // ADD CONTROL ACCESS HERE

  if ($vaultid == 0) {

    $ovalue = new DocValue($dbaccess,array($docid,$attrid));

    
    if (!($ovalue->IsAffected())) $action->exiterror(_("no file referenced"));
    
    ereg ("(.*)\|(.*)", $ovalue->value, $reg);
    $vaultid= $reg[2];
    $mimetype=$reg[1];
  } else {
    $mimetype = "";
  }

  
  $efd = new FileDisk($dbaccess, $vaultid);

  $destdir="./".GetHttpVars("app")."/Download/";
  $efile = $destdir.$efd->origname;  
  $efd->Copyin($action->GetParam("CORE_PUBDIR")."/".$efile);

  $action->log->info("exportfile ".$efd->origname);
  $url = $action->GetParam("CORE_PUBURL")."/".$efile;
  //Header("Location: $url");
  Http_DownloadFile($efile, $efd->origname, $mimetype);


  //unlink($efile);
  exit;
    
    
  
    
}



?>
