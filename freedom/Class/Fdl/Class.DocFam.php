<?php

// ---------------------------------------------------------------
// $Id: Class.DocFam.php,v 1.9 2003/03/17 12:04:33 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Class.DocFam.php,v $
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


$CLASS_DOCFAM_PHP = '$Id: Class.DocFam.php,v 1.9 2003/03/17 12:04:33 eric Exp $';
include_once('FDL/Class.DocFile.php');

Class DocFam extends DocFile {
 
  var $dbtable="docfam";

  var $sqlcreate = "
create table docfam (cprofid int , 
                     dfldid int, 
                     cfldid int, 
                     ddocid int,
                     name text,
                     methods text) inherits (doc);
create unique index idx_idfam on docfam(id);";


 
  var $defaultview= "FDL:VIEWFAMCARD";

  var $attr;

  function DocFam ($dbaccess='', $id='',$res='',$dbid=0) {

    $this->fields["dfldid"] ="dfldid";
    $this->fields["cfldid"] ="cfldid";
    $this->fields["cprofid"]="cprofid";
    $this->fields["ddocid"] ="ddocid";
    $this->fields["methods"]="methods";
    $this->fields["name"]="name";
    DocFile::DocFile($dbaccess, $id, $res, $dbid);
     
     
    if ($this->id > 0) {
      $adoc = "Doc".$this->id;
      include_once("FDLGEN/Class.$adoc.php");
      $adoc = "ADoc".$this->id;
      $this->attributes = new $adoc();
      uasort($this->attributes->attr,"tordered"); 
    }
               
  }



  function PostModify() {    
    include_once("FDL/Lib.Attr.php");
    return refreshPhpPgDoc($this->dbaccess, $this->id);
  }

  // use to view default attribute when new doc
  function PostSelect($id) { 
    if ($this->ddocid > 0) {

      
      $ddoc= new Doc($this->dbaccess, $this->ddocid);
      $nattr = $ddoc->GetNormalAttributes();
      while (list($k,$v) = each($nattr)) {
	$aid = $v->id;
	$this->$aid = $ddoc->GetValueMethod($ddoc->getValue($aid));//$ddoc->getValue($aid);
      }              
    }
  }
  // -----------------------------------
  function viewfamcard($target="_self",$ulink=true,$abstract=false) {
    // -----------------------------------

    global $action;

    while (list($k,$v) = each($this->fields)) {

      $this->lay->set("$v",$this->$v);
      switch ($v) {
      case cprofid:
	if ($this->$v > 0) {
	  $tdoc = new Doc($this->dbaccess,$this->$v);
	  $this->lay->set("cproftitle",$tdoc->title);
	  $this->lay->set("cprofdisplay","");
	} else {
	  $this->lay->set("cprofdisplay","none");
	}
	break;
      case cfldid:
	if ($this->$v > 0) {
	  $tdoc = new Doc($this->dbaccess,$this->$v);
	  $this->lay->set("cfldtitle",$tdoc->title);
	  $this->lay->set("cflddisplay","");
	} else {
	  $this->lay->set("cflddisplay","none");
	}
	break;
      case dfldid:
	if ($this->$v > 0) {
	  $tdoc = new Doc($this->dbaccess,$this->$v);
	  $this->lay->set("dfldtitle",$tdoc->title);
	  $this->lay->set("dflddisplay","");
	} else {
	  $this->lay->set("dflddisplay","none");
	}
	break;
      case wid:
	if ($this->$v > 0) {
	  $tdoc = new Doc($this->dbaccess,$this->$v);
	  $this->lay->set("wtitle",$tdoc->title);
	  $this->lay->set("wdisplay","");
	} else {
	  $this->lay->set("wdisplay","none");
	}
	break;
      }
    }


  }
}

?>
