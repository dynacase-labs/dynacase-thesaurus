<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: viewacl.php,v 1.3 2003/08/18 15:47:04 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: viewacl.php,v 1.3 2003/08/18 15:47:04 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/viewacl.php,v $
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
// ---------------------------------------------------------------

include_once("FDL/Class.Doc.php");


// -----------------------------------
function viewacl(&$action) {
// ------------------------

  $docid= intval(GetHttpVars("docid")) ;
  $userid= intval(GetHttpVars("userid")) ;

  $action->lay->Set("docid",$docid);
  $action->lay->Set("userid",$userid);

  $action->parent->AddJsRef("FDL/Layout/viewacl.js");

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc=new Doc($dbaccess, $docid);

  //-------------------

  
  $perm = new DocPerm($dbaccess, array($docid,$userid));
  
  
  $acls = $doc->acls;
  $acls[]="viewacl";
  $acls[]="modifyacl"; //add this acl global for every document
  $tableacl= array();

  reset($acls);
  while(list($k,$v) = each($acls) ) {
      $tableacl[$k]["aclname"]=_($v);
      $tableacl[$k]["acldesc"]=" ("._($doc->dacls[$v]["description"]).")";

      $pos=$doc->dacls[$v]["pos"];

      $tableacl[$k]["aclid"]=$pos;
      $tableacl[$k]["iacl"]=$k; // index for table in xml
     
      if ($perm->ControlU($pos)) {
	    $tableacl[$k]["selected"]="checked";
      } else {
	    $tableacl[$k]["selected"]="";
      }
      if ($perm->ControlUn($pos)) {
	    $tableacl[$k]["selectedun"]="checked";
      } else {
	    $tableacl[$k]["selectedun"]="";
      } 
      if ($perm->ControlUp($pos)) {	
	    $tableacl[$k]["selectedup"]="checked";
      } else {
	    $tableacl[$k]["selectedup"]="";
      }
      if ($perm->ControlG($pos)) {	
	    $tableacl[$k]["selectedg"]="checked";
      } else {
	    $tableacl[$k]["selectedg"]="";
      }

    }

    $action->lay->SetBlockData("SELECTACL",$tableacl); 





}

?>
