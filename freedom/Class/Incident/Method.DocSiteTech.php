<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Method.DocSiteTech.php,v 1.6 2003/11/17 10:44:49 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage INCIDENT
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: Method.DocSiteTech.php,v 1.6 2003/11/17 10:44:49 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Incident/Attic/Method.DocSiteTech.php,v $
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



function SpecRefresh() {
  global $action;

  include_once("FDL/Lib.Dir.php");



  // contracts():SI_IDCONTRATS,SI_CONTRATS
  if ($this->initid > 0) {
    $filter[]="in_textlist(co_idsites, $this->initid)";
    $contract = getChildDoc($this->dbaccess, 0,0,"ALL", $filter,1,"TABLE","CONTRACT");
    $idc=array();
    $tc=array();
    while(list($k,$v) = each($contract)) {

      $idc[] = $v["id"];
      $tc[] = $v["title"];
    }


    $this->setValue("SI_IDCONTRACTS",$idc);
    $this->setValue("SI_CONTRACTS",$tc);
  }
  
}
	
?>