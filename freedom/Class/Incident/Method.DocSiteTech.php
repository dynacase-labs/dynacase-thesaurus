
// ---------------------------------------------------------------
// $Id: Method.DocSiteTech.php,v 1.1 2002/11/04 09:13:17 eric Exp $
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
  parent::SpecRefresh(); // user site refresh

  // gclient(D,SI_IDTECH1):SI_TECHNAME1,SI_TECHPHONE1,SI_TECHMAIL1


  // First Technical & Second technical

  for ($idt=1; $idt < 3; $idt++) {
    $this->AddParamRefresh("SI_IDTECH$idt","SI_TECHNAME$idt,SI_TECHPHONE$idt,SI_TECHMAIL$idt");

    if ($this->getValue("SI_IDTECH$idt") > 0) {
      $doc = new doc($this->dbaccess,$this->getValue("SI_IDTECH$idt"));
      if ($doc->isAffected()) {
	$this->setValue("SI_TECHNAME$idt",$doc->title);
	$this->setValue("SI_TECHPHONE$idt",$doc->getValue("US_PHONE"));
	$this->setValue("SI_TECHMAIL$idt",$doc->getValue("US_MAIL"));
      }
    }
 
  }

  // contracts():SI_IDCONTRATS,SI_CONTRATS
  $famid=getParam("IDFAM_CONTRACT");
  $filter[]="in_textlist(co_idsites, $this->id)";
  $contract = getChildDoc($this->dbaccess, 0,0,"ALL", $filter,$action->user->id,"TABLE",$famid);
  $idc=array();
  $tc=array();
  while(list($k,$v) = each($contract)) {

    $idc[] = $v["id"];
    $tc[] = $v["title"];
  }


  $this->setValue("SI_IDCONTRACTS",implode("\n", $idc));
  $this->setValue("SI_CONTRACTS",implode("\n", $tc));
  
}
	