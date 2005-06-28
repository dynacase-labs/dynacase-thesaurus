<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: changeicon.php,v 1.7 2005/06/28 08:37:46 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: changeicon.php,v 1.7 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/changeicon.php,v $
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
// Revision 1.7  2005/06/28 08:37:46  eric
// PHP5 change new_doc
//
// Revision 1.6  2004/08/05 09:47:21  eric
// For multibase
//
// Revision 1.5  2004/03/25 11:10:10  eric
// Replace global variable HTTP_ by _
//
// Revision 1.4  2003/08/18 15:47:03  eric
// phpdoc
//
// Revision 1.3  2003/01/20 19:09:28  eric
// homogénisation visu des documents
//
// Revision 1.2  2002/06/19 12:32:28  eric
// modif des permissions : intégration de rq sql hasviewpermission
//
// Revision 1.1  2002/02/05 16:34:07  eric
// decoupage pour FREEDOM-LIB
//
// Revision 1.4  2002/01/28 16:51:35  eric
// modif pour cache dbobj
//
// Revision 1.3  2001/12/18 09:18:10  eric
// first API with ZONE
//
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

include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");
include_once("FREEDOM/freedom_mod.php");
include_once("VAULT/Class.VaultFile.php");





function changeicon(&$action) 
{
  global $_FILES;

  $destdir="./".GetHttpVars("app")."/Upload/";

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);

  $action->lay->Set("docid",$docid);

  $doc= new_Doc($dbaccess,$docid);

  //print_r($_FILES);
  $fileinfo = $_FILES["ifile"];


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


      $vf = newFreeVaultFile($dbaccess);


      $err = $vf -> Store($destfile, true , $vid);
      if ($err != "")   $action->ExitError($err);


      $doc->ChangeIcon($fileinfo['type']."|".$vid);


      

      unlink($destfile);
    
    
  }
    
  redirect($action,"FDL","FDL_CARD&id=".$doc->id);

}

?>
