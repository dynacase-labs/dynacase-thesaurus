<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Class.DocAttr.php,v 1.19 2003/12/30 10:12:57 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 */
 /**
 */


// ---------------------------------------------------------------
// $Id: Class.DocAttr.php,v 1.19 2003/12/30 10:12:57 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Class.DocAttr.php,v $
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


$CLASS_CONTACTATTR_PHP = '$Id: Class.DocAttr.php,v 1.19 2003/12/30 10:12:57 eric Exp $';
include_once('Class.DbObj.php');
include_once('Class.QueryDb.php');
include_once('Class.Log.php');

/**
 * Database Attribute document
 * @package FREEDOM
 *
 */
Class DocAttr extends DbObj
{
  var $fields = array ("id",
		       "docid",
		       "frameid",
		       "labeltext", 
		       "title",
		       "abstract",
		       "type",
		       "ordered",
		       "visibility", // W, R, H, O, M, C
		       "needed",
		       "link",
		       "phpfile", 
		       "phpfunc", 
		       "elink", 
		       "phpconstraint");

  var $id_fields = array ("docid","id");

  var $dbtable = "docattr";

  var $order_by="ordered";

  var $fulltextfields = array ("labeltext");

  var $sqlcreate = "
create table docattr ( id  name,
                     docid int not null,
                     frameid  name,
                     labeltext text,
                     Title  char,
                     Abstract  char,
                     Type  varchar(40),
                     ordered int,
                     visibility char,
                     needed char,
                     link text,
                     phpfile text,
                     phpfunc text,
                     elink text,
                     phpconstraint text
                   );
create sequence seq_id_docattr start 1000;
create unique index idx_iddocid on docattr(id, docid)";


  // possible type of attributes
  var $deftype = array("text",
		       "longtext",
		       "image",
		       "file",
		       "frame",
		       "enum",
		       "date",
		       "integer",
		       "double",
		       "money",
		       "password");
  var $isCacheble= false;
		    
 
  
     
  function PreInsert()
    {

      // compute new id

  
      if ($this->id == "") {
	$res = pg_exec($this->dbid, "select nextval ('seq_id_docattr')");
	$arr = pg_fetch_array ($res, 0);
	$this->id = "auto_".$arr[0];  // not a number must be alphanumeric begin with letter
      }

      if ($this->type=="") $this->type="text";
      if ($this->abstract=="") $this->abstract='N';
      if ($this->title=="") $this->title='N';
      if ($this->visibility=="") $this->visibility='W';
    } 

   function CVisibility() {
     
     if ((isset($this->fieldSet))&&($this->fieldSet->visibility == "H")) return "H";
     else if (($this->fieldSet->visibility == "R") && ($this->visibility != "H")) return "R";
     return $this->visibility;
   }

    
}
?>
