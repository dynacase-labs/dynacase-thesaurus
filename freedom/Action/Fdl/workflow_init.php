<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: workflow_init.php,v 1.3 2003/08/18 15:47:03 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: workflow_init.php,v 1.3 2003/08/18 15:47:03 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Fdl/workflow_init.php,v $
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
include_once("FDL/Lib.Attr.php");
include_once("FDL/Class.DocFam.php");

// -----------------------------------
function workflow_init(&$action) {
  // -----------------------------------

  $docid = GetHttpVars("id");// view doc abstract attributes
  
  if ($docid == "") {
    $action->exitError(_("workflow_init :: id is empty"));
  }

  $dbaccess = $action->GetParam("FREEDOM_DB");

  
    
  $wdoc = new Doc($dbaccess,$docid);
  $wdoc->CreateProfileAttribute();
  if ($wdoc->doctype=='C') $cid = $wdoc->id;
  else $cid= $wdoc->fromid;
 
  $query = new QueryDb($dbaccess,"DocFam");
  $query ->AddQuery("id=$cid");
  $table1 = $query->Query(0,0,"TABLE");
  if ($query->nb > 0)	{
    $tdoc = $table1[0];

    if ($wdoc->isAffected() && $wdoc->usefor=="W") {

      createDocFile($dbaccess,$tdoc);
      PgUpdateFamilly($dbaccess, $cid);

    } else {
      $action->exitError(sprintf(_("workflow_init :: id %s is not a workflow"),$docid));
    
    }
  } else {
      $action->exitError(sprintf(_("workflow_init :: workflow id %s not found"),$cid));
  }
}
?>
