<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Method.DocContrat.php,v 1.3 2003/08/18 15:47:04 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage INCIDENT
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: Method.DocContrat.php,v 1.3 2003/08/18 15:47:04 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Incident/Attic/Method.DocContrat.php,v $
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

  // gclient(D,CO_IDCLT1):CO_CLTNAME1,CO_CLTPHONE1,CO_CLTMAIL1
  // gclient(D,CO_IDCLT2):CO_CLTNAME2,CO_CLTPHONE2,CO_CLTMAIL2


  // First Clients & Second clients

  for ($idt=1; $idt < 3; $idt++) {
    $this->AddParamRefresh("CO_IDCLT$idt","CO_CLTNAME$idt,CO_CLTPHONE$idt,CO_CLTMAIL$idt");

    if ($this->getValue("CO_IDCLT$idt") > 0) {
      $doc = new doc($this->dbaccess,$this->getValue("CO_IDCLT$idt"));
      if ($doc->isAlive()) {
	$this->setValue("CO_CLTNAME$idt",$doc->title);
	$this->setValue("CO_CLTPHONE$idt",$doc->getValue("US_PHONE"));
	$this->setValue("CO_CLTMAIL$idt",$doc->getValue("US_MAIL"));
      }
    }
 
  }
 
}
	
?>