<?php

// ---------------------------------------------------------------
// $Id: Class.DocPerm.php,v 1.1 2002/11/07 16:00:00 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Class.DocPerm.php,v $
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


$CLASS_DOCPERM_PHP = '$Id: Class.DocPerm.php,v 1.1 2002/11/07 16:00:00 eric Exp $';
include_once("Class.DbObj.php");

Class Docperm extends DbObj
{
  var $fields = array ("docid",
		       "userid",
		       "upacl",
		       "unacl",
		       "cacl");

  var $sup_fields = array("getuperm(userid,docid) as uperm");
  var $id_fields = array ("docid","userid");

  var $dbtable = "docperm";

  var $order_by="docid";

  var $sqlcreate = "
create table docperm ( 
                     docid int,
                     userid int not null,
                     upacl int  not null,
                     unacl int  not null,
                     cacl int not null
                   );
create unique index idx_perm on docperm(docid, userid);";
  
  function getUperm($docid, $userid) {
    $q = new QueryDb($this->dbaccess, "docperm");
    $t = $q -> Query(0,1,"TABLE","select getuperm($userid,$docid) as uperm");

    return $t[0]["uperm"];
  }
    
}
?>
