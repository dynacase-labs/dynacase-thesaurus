<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Method.PostIt.php,v 1.2 2003/08/18 15:47:04 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: Method.PostIt.php,v 1.2 2003/08/18 15:47:04 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Method.PostIt.php,v $
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



  
var $defaultview= "FDL:VIEWPOSTIT:T";
  
  
// -----------------------------------
function viewpostit($target="_self",$ulink=true,$abstract=false) {
  // -----------------------------------

  $tcomment = $this->getTvalue("PIT_COM");
  $tuser = $this->getTvalue("PIT_USER");
  $tdate = $this->getTvalue("PIT_DATE");
  $tcolor = $this->getTvalue("PIT_COLOR");


  $tlaycomment=array();
  while (list($k,$v) = each($tcomment)) {
    $tlaycomment[]=array("comments"=>$v,
			 "user"=>$tuser[$k],
			 "date"=>$tdate[$k],
			 "color"=>$tcolor[$k]);
  }

 
  // Out


  $this->lay->SetBlockData("TEXT",	 $tlaycomment);

}

function PostModify() {
  $docid= $this->getValue("PIT_IDADOC");
  if ($docid > 0) {
    $doc= new Doc($this->dbaccess, $docid);
    if (intval($doc->postitid) == 0) {
      $doc->disableEditControl();
      $doc->postitid=$this->id;
      $doc->modify();
      $doc->enableEditControl();
    }
  }
}

function PostDelete() {
  $docid= $this->getValue("PIT_IDADOC");
  if ($docid > 0) {
    $doc= new Doc($this->dbaccess, $docid);
    if ($doc->locked == -1) $doc= new Doc($this->dbaccess, $doc->latestId());
    if (intval($doc->postitid) > 0) {
      $doc->disableEditControl();
      $doc->postitid=0;
      $doc->modify();
      $doc->enableEditControl();
    }
  }
}
?>