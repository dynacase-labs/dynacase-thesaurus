<?php
// ---------------------------------------------------------------
// $Id: changeicon.php,v 1.2 2001/11/21 13:12:55 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Attic/changeicon.php,v $
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
// $Log: changeicon.php,v $
// Revision 1.2  2001/11/21 13:12:55  eric
// ajout caractéristique creation profil
//
// Revision 1.1  2001/11/21 08:40:34  eric
// ajout historique
//
// Revision 1.1  2001/11/09 09:41:14  eric
// gestion documentaire
//

// ---------------------------------------------------------------

include_once("FREEDOM/Class.Doc.php");
include_once("FREEDOM/Class.DocAttr.php");
include_once("FREEDOM/freedom_mod.php");
include_once("VAULT/Class.VaultFile.php");





function changeicon(&$action) 
{
  global $HTTP_POST_FILES;

  $destdir="./".GetHttpVars("app")."/Upload/";

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);

  $action->lay->Set("docid",$docid);

  $doc= new Doc($dbaccess,$docid);

  //print_r($HTTP_POST_FILES);
  $fileinfo = $HTTP_POST_FILES["ifile"];


  if (is_array($fileinfo )) {
    // if no file specified, keep current file
    if ($fileinfo['tmp_name'] == "none") $action->ExitError(_("no file specified : update aborted"));
    if ( ! is_uploaded_file($fileinfo['tmp_name'])) $action->ExitError(_("file not expected : possible attack : update aborted"));

      $err = $doc->CanUpdateDoc();

      if ($err != "")   $action->ExitError($err);
  

      ereg ("(.*)\.(.*)$", $fileinfo['name'], $reg);
      $ext=$reg[2];



      // move to add extension
      $destfile=str_replace(" ","_","/tmp/".$fileinfo['name']);
      move_uploaded_file($fileinfo['tmp_name'], $destfile);


      $vf = new VaultFile($dbaccess, $action->parent->name);
      $vf -> Store($destfile, true , $vid);


      $doc->ChangeIcon($fileinfo['type']."|".$vid);


      

      unlink($destfile);
    
    
  }
    
  redirect($action,GetHttpVars("app"),"FREEDOM_CARD&id=".$doc->id);

}

?>
