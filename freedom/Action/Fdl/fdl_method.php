<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: fdl_method.php,v 1.2 2003/08/18 15:47:03 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: fdl_method.php,v 1.2 2003/08/18 15:47:03 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Fdl/fdl_method.php,v $
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
function fdl_method(&$action) 
{
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);
  $method = GetHttpVars("method");


  $doc= new Doc($dbaccess,$docid);

  
  if ($doc && $doc->isAlive()) {
    
    $err = $doc->control("view");
    if ($err != "") $action->exitError($err);

    if (method_exists ( $doc, $method)) {
      
      $err=call_user_method($method,$doc);
    } else {
      $action->AddWarningMsg(sprintf(_("the method %s does not exist for this document",$method)));
    }
  }

  
  if ($err != "") $action->AddWarningMsg($err);
  
  
  $action->AddLogMsg(sprintf(_("%s has been locked"),$doc->title));
    
  
    
  redirect($action,"FDL","FDL_CARD&id=".$doc->id,$action->GetParam("CORE_STANDURL"));

}



?>
