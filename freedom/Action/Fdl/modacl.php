<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: modacl.php,v 1.4 2003/12/09 10:51:14 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: modacl.php,v 1.4 2003/12/09 10:51:14 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Fdl/modacl.php,v $
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





// -----------------------------------
function modacl(&$action) {
  // ----------------------------------- 
  // get all parameters
  $userid=GetHttpVars("userid");

  $aclp=GetHttpVars("aclup"); // ACL + (more access)
  $acln=GetHttpVars("aclun"); // ACL - (less access)
  $docid=GetHttpVars("docid"); // oid for controlled object


  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  $perm = new DocPerm($dbaccess, array($docid,$userid));

  $perm->UnSetControl();

  if (is_array($aclp)) {
    while (list($k,$v) = each($aclp)) {
      $perm->SetControlP($v);
    }
  }
  if (is_array($acln)) {
    while (list($k,$v) = each($acln)) {
      $perm->SetControlN($v);
    }
  }

  if ($perm -> isAffected()) $perm ->modify();
  else $perm->Add();


  RedirectSender($action);

}
?>
