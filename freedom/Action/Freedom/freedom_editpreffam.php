<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_editpreffam.php,v 1.2 2003/08/18 15:47:03 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: freedom_editpreffam.php,v 1.2 2003/08/18 15:47:03 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_editpreffam.php,v $
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
include_once("FDL/Lib.Dir.php");

function freedom_editpreffam(&$action) 
{
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $tcdoc=GetClassesDoc($dbaccess,$action->user->id,1);
  
  $idsfam = $action->GetParam("FREEDOM_PREFFAMIDS");
  $tidsfam = explode(",",$idsfam);



  $selectclass=array();
  if (is_array($tcdoc)) {
    while (list($k,$pdoc)= each ($tcdoc)) {
    
	$selectclass[$k]["cid"]=$pdoc->id;
	$selectclass[$k]["ctitle"]=$pdoc->title;
	$selectclass[$k]["selected"]=(in_array($pdoc->id,$tidsfam))?"checked":"";
      
    }
    
  }

  $action->lay->SetBlockData("SELECTPREF", $selectclass);
	  
      
    
  


}

?>
