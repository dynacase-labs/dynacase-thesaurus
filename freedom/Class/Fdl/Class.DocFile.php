<?php
// ---------------------------------------------------------------
// $Id: Class.DocFile.php,v 1.6 2003/01/31 14:31:30 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Class.DocFile.php,v $
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

$CLASS_DOCFILE_PHP = '$Id: Class.DocFile.php,v 1.6 2003/01/31 14:31:30 eric Exp $';


include_once("FDL/Class.PDoc.php");




Class DocFile extends PDoc
{
    
  var $defDoctype='F';
  var $defClassname='DocFile';

  function refreshDocTitle($nameId,$nameTitle) {
  
    // gettitle(D,SI_IDSOC):SI_SOCIETY,SI_IDSOC

    $this->AddParamRefresh("$nameId","$nameTitle,$nameId");
    $doc=new Doc($this->dbaccess, $this->getValue($nameId));
    if ($doc->isAlive())  $this->setValue($nameTitle,$doc->title);
    else {
      // suppress
      $this->deleteValue($nameId);
    }
  }


  // return the personn doc id conform to firstname & lastname of the user
  function userDocId() {
    
    include_once("FDL/Lib.Dir.php");
    $famid=getFamIdFromName($this->dbaccess,"USER");
    $filter[]="title = '".$this->userName()."'";
    
    $tpers = getChildDoc($this->dbaccess, 0,0,1, $filter,$action->user->id,"TABLE",$famid);
    if (count($tpers) > 0)    return($tpers[0]["id"]);
    
    return "";
    
  }
  // return the personn doc id conform to firstname & lastname of the user
  function userName() {
    global $action;

    return $action->user->lastname." ".$action->user->firstname;
  }

  function getTitle($id) {
    $doc = new Doc($this->dbaccess,$id);
    if ($doc->isAlive()) {
      return $doc->title;
    }
    return " "; // delete title
  }
}

?>