<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: generic_modkind.php,v 1.4 2004/09/09 12:58:27 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: generic_modkind.php,v 1.4 2004/09/09 12:58:27 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_modkind.php,v $
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
include_once("FDL/Class.DocAttr.php");
include_once("FDL/Lib.Attr.php");
include_once("GENERIC/generic_util.php"); 

// -----------------------------------
function generic_modkind(&$action) {
  // -----------------------------------

  
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $aid    = GetHttpVars("aid");    // attribute id
  $famid  = GetHttpVars("fid");    // family id
  $tlevel = GetHttpVars("alevel"); // levels
  $tref   = GetHttpVars("aref");   // references
  $tlabel = GetHttpVars("alabel"); // label

  $tsref=array();
  $ref="";$ple = 1;
  while (list($k, $v) = each($tref)) {
    $le= intval($tlevel[$k]);
    if ($le == 1) $ref=''; 
    else if ($ple < $le) {
      // add level ref index
      $ref = $ref  . $tref[$k-1].'.';
    } else  if ($ple > $le) {
      // suppress one or more level ref index
      for ($l=0;$l<$ple-$le;$l++)  $ref=substr($ref,0,strrpos($ref,'.')-1);
    }
    $ple = $le;
   

    $tsenum[$k] = $ref.$v."|".$tlabel[$k];
  }

  $attr = new DocAttr($dbaccess, array($famid,$aid));
  if ($attr->isAffected()) {
  
    if (ereg("\[([a-z]+)\](.*)",$attr->phpfunc, $reg)) {	 
      $funcformat=$reg[1];
    } else {	  
      $funcformat="";
    }
    $attr->phpfunc = stripslashes(implode(",",$tsenum));
    if ($funcformat != "") $attr->phpfunc="[$funcformat]".$attr->phpfunc;
    $attr->modify();

    refreshPhpPgDoc($dbaccess, $famid);
  }
		      

  redirect($action,GetHttpVars("app"),"GENERIC_TAB",
	   $action->GetParam("CORE_STANDURL"));

}


?>
