<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: adddirfile.php,v 1.15 2005/06/28 08:37:46 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: adddirfile.php,v 1.15 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/adddirfile.php,v $
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


include_once("FDL/Lib.Dir.php");
include_once("FDL/freedom_util.php");  



// -----------------------------------
function adddirfile(&$action) {
  // -----------------------------------


    //    PrintAllHttpVars();

  // Get all the params      
  $dirid=GetHttpVars("dirid");
  $docid=GetHttpVars("docid");
  $mode=GetHttpVars("mode");
  $return=GetHttpVars("return"); // return action may be folio
  $folio=(GetHttpVars("folio","N")=="Y"); // return in folio
  $folio = ($folio|$return);

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc= new_Doc($dbaccess, $docid);
  $dir= new_Doc($dbaccess, $dirid);

  $err = $dir->AddFile($docid, $mode);
  

  if ($err != "") $action->addWarningMsg($err);
  
  

  
  if ($folio) {
    $refreshtab=(($doc->doctype == "F")?"N":"Y");
    redirect($action,GetHttpVars("app"),"FOLIOLIST&refreshtab=$refreshtab&dirid=$dirid");
  } else redirect($action,GetHttpVars("app"),"FREEDOM_VIEW&dirid=$dirid");
}




?>
