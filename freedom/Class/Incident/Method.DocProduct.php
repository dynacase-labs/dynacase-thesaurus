
// ---------------------------------------------------------------
// $Id: Method.DocProduct.php,v 1.1 2002/11/04 09:13:17 eric Exp $
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



function SpecRefresh() {
  //gettitle(D,PR_IDSITE):PR_SITE,PR_IDSITE
  $this->refreshDocTitle("PR_IDSITE","PR_SITE");
  //gettitle(D,PR_IDCONTRACT):PR_CONTRACT,PR_IDCONTRACT
  $this->refreshDocTitle("PR_IDCONTRACT","PR_CONTRACT");
  //gettitle(D,PR_IDMARK):PR_MARK,PR_IDMARK
  $this->refreshDocTitle("PR_IDMARK","PR_MARK");



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
    $this->setValue("PR_PLATDATE", $doc->getValue("CO_DATEEND"));
  }
 
}
	