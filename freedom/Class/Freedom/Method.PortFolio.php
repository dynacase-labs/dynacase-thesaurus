// ---------------------------------------------------------------
// $Id: Method.PortFolio.php,v 1.1 2003/02/07 17:31:50 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Freedom/Method.PortFolio.php,v $
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


function PostCreated() {
  // copy all guide-card from default values
  include_once("FDL/Lib.Dir.php");  

  $fdoc= new Doc($this->dbaccess,$this->fromid);

  if ($fdoc->ddocid > 0) {
    $child = getChildDir($this->dbaccess,$this->userid,$fdoc->ddocid, false,"LIST");
    
    while (list($k,$doc) = each($child)) {
      if ($doc->usefor == "G") {
	$copy=$doc->Copy();


	$err=$this->AddFile($copy->id);

	print $err;
      }
    }
  }
}