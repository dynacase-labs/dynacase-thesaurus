<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_modpreffam.php,v 1.3 2003/08/18 15:47:03 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: freedom_modpreffam.php,v 1.3 2003/08/18 15:47:03 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_modpreffam.php,v $
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
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 

include_once("FDL/Class.Doc.php");

function freedom_modpreffam(&$action) 
{
  $tidsfam = GetHttpVars("idsfam"); // preferenced families
  $dbaccess = $action->GetParam("FREEDOM_DB");


  
  $idsfam = "";
  if (is_array($tidsfam)) $idsfam = implode(",",$tidsfam);

  $action->parent->param->Set("FREEDOM_PREFFAMIDS",$idsfam,PARAM_USER.$action->user->id,$action->parent->id);
	  
      
  redirect($action,"CORE","FOOTER");
  


}

?>
