<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Method.Action_impl.php,v 1.3 2003/08/18 15:47:04 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */
// ---------------------------------------------------------------
// $Id: Method.Action_impl.php,v 1.3 2003/08/18 15:47:04 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Freedom/Method.Action_impl.php,v $
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

var $defaultedit="FREEDOM:EDIT_IMPL";

function edit_impl($target="finfo",$ulink=true,$abstract="Y") {
  global $action;
 include_once("FDL/editutil.php");
 //$action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FREEDOM/Layout/workflow.js");
 
 $this->lay->Set("famid",552);

  $this->lay->Set("docid", $this->id);
  $this->lay->Set("TITLE", $this->title);
  
		  

  $this->editattr($target,$ulink,$abstract);


		  
}
?>