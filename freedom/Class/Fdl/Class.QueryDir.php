<?php
// ---------------------------------------------------------------
// $Id: Class.QueryDir.php,v 1.12 2003/05/19 10:44:15 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Class.QueryDir.php,v $
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



$CLASS_CONTACT_PHP = '$Id: Class.QueryDir.php,v 1.12 2003/05/19 10:44:15 eric Exp $';
include_once("Class.DbObj.php");
include_once("Class.QueryDb.php");
include_once("Class.Log.php");

  
Class QueryDir extends DbObj
{
  var $fields = array ( "id","dirid","query","childid","qtype");

  var $id_fields = array ("id");

  var $dbtable = "fld";

  var $order_by="dirid";

  var $fulltextfields = array ("");

  var $sqlcreate = "
create table fld ( id      int PRIMARY KEY,
                    dirid   int not null ,
                    query   text,
                    childid   int,
                    qtype   char
                   );
create index fld_iqd on fld(qtype,dirid);
create unique index fld_u on fld(qtype,dirid,childid);
create sequence seq_id_fld start 100";

  var $relatedCacheClass= array("doc"); // class must ne cleaned also in case of modify

  // --------------------------------------------------------------------
  function PreInsert()
    // --------------------------------------------------------------------
    {
      // test if not already exist 
      if ($this->qtype != "M") {
	$query = new QueryDb($this->dbaccess,"QueryDir");
	$query->AddQuery("dirid=".$this->dirid);
	$query->AddQuery("childid='".$this->childid."'");
	$query->Query(0,0,"TABLE");
	if ($query->nb != 0) return _("already exist : not added");
      }
      // compute new id
      if ($this->id == "") {
	$res = pg_exec($this->dbid, "select nextval ('seq_id_fld')");
	$arr = pg_fetch_array ($res, 0);
	$this->id = $arr[0];
	  
      }
    }
 
}
?>
