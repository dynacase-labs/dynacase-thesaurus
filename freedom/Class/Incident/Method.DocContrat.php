<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Method.DocContrat.php,v 1.8 2004/01/16 09:20:05 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage INCIDENT
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: Method.DocContrat.php,v 1.8 2004/01/16 09:20:05 eric Exp $
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
  $this->setProductSite();
  $this->setCallerPhone();
}

function postModify() {
  $this->setProductContract();  
  $this->modify();
  $this->refreshProduct();
  $this->recupProduct();
}
	
function setProductSite() {
  $tproduct = $this->getTvalue("CO_IDPRODUCT");
  while (list($k,$v) = each($tproduct)) {
    $prodoc = getLatestTDoc($this->dbaccess, $v);
    if ($prodoc) {
       $tidsite[]=getv($prodoc,"pr_idsite"," ");
      $tsite[]=getv($prodoc,"pr_site"," ");
    }
  }
  $this->setValue("CO_IDPSITE",$tidsite);
  $this->setValue("CO_PSITE",$tsite);
}	
function setProductInit() {
  $tproduct = $this->getTvalue("CO_IDPRODUCT");
  while (list($k,$v) = each($tproduct)) {
    $prodoc = getTDoc($this->dbaccess, $v);
    if ($prodoc) {
       $tidprod[]=getv($prodoc,"initid");
    }
  }
  $this->setValue("CO_IDPRODUCT",$tidprod);
}	
function setCallerPhone() {
  $tproduct = $this->getTvalue("CO_IDCALLER");
  $tcidsite = $this->getTvalue("CO_IDCSITES");
  $tcsite = $this->getTvalue("CO_CSITES");
  $tcphone = $this->getTvalue("CO_CPHONE");
  $tcmail = $this->getTvalue("CO_CMAIL");
  $tcname = $this->getTvalue("CO_CALLER");
  foreach($tproduct as $k=>$v) {
    $prodoc = getTDoc($this->dbaccess, $v);
    if ($prodoc) {
       $tcidsite[$k]=getv($prodoc,"us_idsociety");
       $tcsite[$k]=getv($prodoc,"us_society");
       $tcphone[$k]=getv($prodoc,"us_phone");
       $tcmail[$k]=getv($prodoc,"us_mail");
       $tcname[$k]=getv($prodoc,"title");
    } 
  }
  $this->setValue("CO_CPHONE",$tcphone);
  $this->setValue("CO_CSITES",$tcsite);
  $this->setValue("CO_CMAIL",$tcmail);
  $this->setValue("CO_IDCSITES",$tcidsite);
  $this->setValue("CO_CALLER",$tcname);
}
	
function setProductContract() {
  $tproduct = $this->getTvalue("CO_IDPRODUCT");
  
  foreach ($tproduct as $k=>$v) {
    $prodoc = new doc($this->dbaccess, $v);
    if ($prodoc->locked == -1) { // get latest contract
      $prodoc= new Doc($this->dbaccess, $prodoc->latestId());
      
    }
    if ($prodoc->isAlive()) {

      $idcontract = $prodoc->getValue("PR_IDCONTRACT");
      $enddate = $prodoc->getValue("PR_PLATDATE");

      if (($idcontract != $this->id) || ($enddate != $this->getValue("CO_DATEEND")))  {
	$prodoc->setValue("PR_IDCONTRACT",$this->id);
	$prodoc->setValue("PR_CONTRACT",$this->title);
	$prodoc->setValue("PR_PLATDATE",$this->getValue("CO_DATEEND"));
	$err=$prodoc->modify();

      }
    }
  } 
  
}

function refreshProduct() {
  include_once("FDL/Lib.Dir.php");

  $filter[]="pr_idcontract =".$this->id;

  $tdoc = getChildDoc($this->dbaccess, 
			   0,0,100, 
			   $filter,1,"TABLE",
			   "PRODUCT");

  $doc = createDoc($this->dbaccess,"PRODUCT");
  while (list($k,$prodoc) = each($tdoc)) {
    $doc->Affect($prodoc);
    $doc->refresh();
    $doc->modify();
    
  }
}
function recupProduct() {
  include_once("FDL/Lib.Dir.php");

  $filter[]="pr_idcontract =".$this->id;

  $tdoc = getChildDoc($this->dbaccess, 
			   0,0,100, 
			   $filter,1,"TABLE",
			   "PRODUCT");


  while (list($k,$prodoc) = each($tdoc)) {

       $tidprod[]=$prodoc["initid"];
       $tprod[]=$prodoc["title"];
       $tidsite[]=getv($prodoc,"pr_idsite"," ");
       $tsite[]=getv($prodoc,"pr_site"," ");

    
  }
  $this->setValue("CO_IDPSITE",$tidsite);
  $this->setValue("CO_PSITE",$tsite);
  $this->setValue("CO_IDPRODUCT",$tidprod);
  $this->setValue("CO_PRODUCT",$tprod);

}

function recupCallers() {
  $tsite = $this->getTvalue("CO_IDSITES");
  $tidcall=array();
  $tcall=array();
  $tcidsite=array();
  $tcsite=array();
  $tcphone=array();
  $tcmail=array();
  while (list($k,$v) = each($tsite)) {
    $sidoc = getTDoc($this->dbaccess, $v);
   
    if ($sidoc) {
      $tech1 = getv($sidoc,"si_techname1");
      if ($tech1 != "") {
	$tidcall[$tech1]=getv($sidoc,"si_idtech1"," ");
	$tcphone[$tech1]=getv($sidoc,"si_techphone1"," ");
	$tcmail[$tech1]=getv($sidoc,"si_techmail1"," ");
	$tcidsite[$tech1]=$sidoc["id"];
	$tcsite[$tech1]=$sidoc["title"];
	$tcall[$tech1]=$tech1;
      }
      $tech2 = getv($sidoc,"si_techname2");
      if ($tech2 != "") {
	$tidcall[$tech2]=getv($sidoc,"si_idtech2"," ");
	$tcphone[$tech2]=getv($sidoc,"si_techphone2"," ");
	$tcmail[$tech2]=getv($sidoc,"si_techmail2"," ");
	$tcidsite[$tech2]=$sidoc["id"];
	$tcsite[$tech2]=$sidoc["title"];
	$tcall[$tech2]=$tech2;
      }
    }
  }
  $this->setValue("CO_IDCALLER",$tidcall);
  $this->setValue("CO_CALLER",$tcall);
  $this->setValue("CO_CPHONE",$tcphone);
  $this->setValue("CO_CSITES",$tcsite);
  $this->setValue("CO_IDCSITES",$tcidsite);
  $this->setValue("CO_CMAIL",$tcmail);

}
?>