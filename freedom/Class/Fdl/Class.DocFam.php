<?php

// ---------------------------------------------------------------
// $Id: Class.DocFam.php,v 1.3 2002/11/22 18:08:22 eric Exp $
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


$CLASS_DOCFAM_PHP = '$Id: Class.DocFam.php,v 1.3 2002/11/22 18:08:22 eric Exp $';
include_once('FDL/Class.DocFile.php');

Class DocFam extends DocFile {
 
  var $dbtable="docfam";

  var $sqlcreate = "
create table docfam (cprofid int , 
                     dfldid int, 
                     ddocid int, 
                     methods text) inherits (doc);
create unique index idx_idfam on docfam(id);";


 
  var $attr;

   function DocFam ($dbaccess='', $id='',$res='',$dbid=0) {

     $this->fields["dfldid"] ="dfldid";
     $this->fields["cprofid"]="cprofid";
     $this->fields["ddocid"] ="ddocid";
     $this->fields["methods"]="methods";
     DocFile::DocFile($dbaccess, $id, $res, $dbid);
     
     
     if ($this->id > 0) {
       $adoc = "Doc".$this->id;
       include_once("FDLGEN/Class.$adoc.php");
       $adoc = "ADoc".$this->id;
       $this->attributes = new $adoc();
       uasort($this->attributes->attr,"tordered"); 
     }
               
   }


   // use to view default attribute when new doc
   function PostSelect($id) { 
     if ($this->ddocid > 0) {
       $ddoc= new Doc($this->dbaccess, $this->ddocid);
       $nattr = $ddoc->GetNormalAttributes();
       while (list($k,$v) = each($nattr)) {
	 $aid = $v->id;
	 $this->$aid = $ddoc->getValue($aid);
       }
       
       
     }
   }
}


?>
