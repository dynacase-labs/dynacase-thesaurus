<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: generic_editimport.php,v 1.10 2003/08/18 15:47:03 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: generic_editimport.php,v 1.10 2003/08/18 15:47:03 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_editimport.php,v $
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


include_once("FDL/Class.Dir.php");
include_once("GENERIC/generic_util.php");  

// -----------------------------------
function generic_editimport(&$action) {
  // -----------------------------------

  global $dbaccess;
  
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $homefld = new Doc( $dbaccess, getDefFld($action));

  


  $stree=getChildCatg($homefld->id, 1);

  reset($stree);
  
  $action->lay->SetBlockData("CATG",$stree);
  $action->lay->Set("topdir", getDefFld($action));
  
  $famid = getDefFam($action);

  // spec for csv file
  $doc=new Doc($dbaccess, $famid);
  $lattr = $doc->GetImportAttributes();
  $format = "DOC;".$doc->id.";0;". getDefFld($action)."; ";

  while (list($k, $attr) = each ($lattr)) {
    $format .= $attr->labelText." ;";
  }
  $action->lay->Set("format",$format);

}


?>
