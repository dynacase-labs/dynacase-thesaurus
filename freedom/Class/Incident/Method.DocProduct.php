<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Method.DocProduct.php,v 1.4 2003/11/17 10:44:49 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage INCIDENT
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: Method.DocProduct.php,v 1.4 2003/11/17 10:44:49 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Incident/Attic/Method.DocProduct.php,v $
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


/**
 * Special methods for PRODUCT family
 *
 * @author Anakeen 2001
 * @version $Revision: 1.4 $
 * @package FREEDOM
 * @subpackage INCIDENT
 */
function SpecRefresh() {
  //gettitle(D,PR_IDSITE):PR_SITE,PR_IDSITE
  $this->refreshDocTitle("PR_IDSITE","PR_SITE");
  //gettitle(D,PR_IDCONTRACT):PR_CONTRACT,PR_IDCONTRACT
  $this->refreshDocTitle("PR_IDCONTRACT","PR_CONTRACT");
  //gettitle(D,PR_IDMARK):PR_MARK,PR_IDMARK
  $this->refreshDocTitle("PR_IDMARK","PR_MARK");


  $this->AddParamRefresh("PR_IDCMDFUR","PR_CMDFUR,PR_IDFUR,PR_FUR");
  $this->AddParamRefresh("PR_IDMODEL","PR_MODEL,PR_IDMARK,PR_MARK,PR_DESG,PR_IDTARIF,PR_TARIF,PR_IDFUR,PR_FUR");
  $this->AddParamRefresh("PR_IDTARIF","PR_TARIF");

  //date2epoch(PR_INDATE):PR_INDATE_EPOCH
  $this->AddParamRefresh("PR_INDATE","PR_INDATE_EPOCH");
  $date = $this->getValue("PR_INDATE");
  
  if ($date != "") {    
    list($d,$m,$y)=split("/",$date);
    $this->setValue("PR_INDATE_EPOCH",mktime(0,0,0,$m,$d,$y));
  }

  //dateplat(D,PR_IDCONTRACT):PR_PLATDATE
  $this->AddParamRefresh("PR_IDCONTRACT","PR_PLATDATE");
  $idcontract=$this->getValue("PR_IDCONTRACT");
  if ($idcontract > 0) {
    $doc = new Doc($this->dbaccess,$idcontract);
    if ($doc->locked == -1) { // get latest contract
      $doc= new Doc($this->dbaccess, $doc->latestId());
      $this->setValue("PR_IDCONTRACT", $doc->id);
    }
    $tidprod = $doc->getTvalue("CO_IDPRODUCT");
    if (! in_array($this->initid, $tidprod)) {
      // delete reference to contract
      $this->setValue("PR_PLATDATE", " ");
      $this->setValue("PR_IDCONTRACT", " ");
      $this->setValue("PR_CONTRACT", " ");
    }
    
  }
 
}

function postModify() {
  $this->setConstructor();
  $this->setAffair();
  
}

function setConstructor() {
  $idart = $this->getValue("PR_IDMODEL");
  $doc = new Doc($this->dbaccess, $idart);
  if ($doc->isAlive()) {
    $this->setValue("PR_IDMARK",$doc->getValue("AR_IDCONST"," "));
    $this->setValue("PR_MARK",$doc->getValue("AR_CONST"," "));
  }
}
function setAffair() {
  $idart = $this->getValue("PR_IDCMDFUR");
  $doc = new Doc($this->dbaccess, $idart);
  if ($doc->isAlive()) {
    $cmdint = new Doc($this->dbaccess, $doc->getValue("CMF_IDCMC"));
    if ($cmdint->isAlive()) {
      $this->setValue("PR_IDAFFAIR",$cmdint->getValue("CMC_IDAFFAIR"," "));
      $this->setValue("PR_AFFAIR",$cmdint->getValue("CMC_PROPO"));
    }
    
  }
}
?>