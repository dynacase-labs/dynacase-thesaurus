<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_modaccess.php,v 1.9 2004/11/12 10:16:49 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: freedom_modaccess.php,v 1.9 2004/11/12 10:16:49 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_modaccess.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2000
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

// -----------------------------------
function freedom_modaccess(&$action) {
  // -----------------------------------

  
  // get all parameters

  $acls=GetHttpVars("acls", array()); 
  $docid=GetHttpVars("docid"); // id for controlled object

  $dbaccess = $action->GetParam("FREEDOM_DB");

 


  $doc = new Doc($dbaccess, $docid);


  // test if current user can modify ACL 
  $err = $doc->Control("modifyacl");
  if ($err != "") $action->exitError($err);


  while (list($userid,$aclon) = each ($acls)) {     
  
    // modif permission for a particular user
    $perm = new DocPerm($dbaccess, array($docid,$userid));
    $perm->UnsetControl();
    while (list($k,$pos) = each ($aclon)) { 
      if (intval($pos) > 0)  $perm->SetControlP($pos);
    }
    if ($perm->isAffected()) $perm ->modify();
    else $perm->Add();
    
  }
  
  // recompute all related profile
  $pfamid=$doc->getValue("DPDOC_FAMID");
  if ($pfamid > 0) {
    
    $filter = array("dprofid = ".$doc->id);
    $tdoc = getChildDoc($dbaccess, 0,0,"ALL", $filter,1,"TABLE",
			$pfamid	);
    if (count($tdoc)>0) {
      $kdoc = createDoc($dbaccess,$pfamid,true);
      foreach( $tdoc as $k=>$v) {
	$kdoc->Affect($v);
	$kdoc->computeDProfil();

      }
    }
  }
  
 
  
  RedirectSender($action); // return to sender
}
?>
