<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: generic_usort.php,v 1.3 2004/06/03 14:47:28 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: generic_usort.php,v 1.3 2004/06/03 14:47:28 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_usort.php,v $
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
include_once("GENERIC/generic_util.php");

// -----------------------------------
function generic_usort(&$action) {
  // -----------------------------------

  
  // get all parameters
  $aorder=GetHttpVars("aorder"); // id for controlled object

  if ($aorder == "-") {
    // invert order
    $uorder = getDefUSort($action);
    if ($uorder[0] == "-") $aorder=substr($uorder,1);
    else $aorder="-".$uorder;
  }

 
   $action->parent->param->Set("GENERIC_USORT",setUsort($action,$aorder),PARAM_USER.$action->user->id,$action->parent->id);


  $famid = getDefFam($action);

   redirect($action,$action->GetParam("APPNAME","GENERIC"),
	    "GENERIC_TAB&tab=0&famid=$famid",
	     $action->GetParam("CORE_STANDURL"));
  
 
}


function setUsort(&$action, $aorder) {
  
  $famid=getDefFam(&$action);
  $dbaccess = $action->GetParam("FREEDOM_DB");


  $fdoc= new Doc( $dbaccess, $famid);

  $pu = $action->GetParam("GENERIC_USORT");
  $tr=array();
  if ($pu) {
    // disambled parameter
    $tu = explode("|",$pu);
    
    while (list($k,$v) = each($tu)) {
      list($afamid,$uorder,$sqlorder) = explode(":",$v);
      $tr[$afamid]=$uorder.":".$sqlorder;
    }
  }

  $sqlorder=$aorder; 
  if ($aorder[0] == "-") $sqlorder=substr($aorder,1);
  $a = $fdoc->getAttribute($sqlorder);
  if ($a && $a->type == "text") $sqlorder="lower($sqlorder)";
  if ($aorder[0] == "-") $sqlorder.= " desc";

 
  $tr[$famid]=$aorder.":".$sqlorder;

  // rebuild parameter
  $tu=array();
  reset($tr);
  while (list($k,$v) = each($tr)) {
    $tu[]="$k:$v";
  }
  return implode("|", $tu);
  
  
  
}
?>
