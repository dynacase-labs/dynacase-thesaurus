<?php
// ---------------------------------------------------------------
// $Id: Class.DocValue.php,v 1.4 2002/04/09 14:48:44 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Class.DocValue.php,v $
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

$CLASS_CONTACTVALUE_PHP = '$Id: Class.DocValue.php,v 1.4 2002/04/09 14:48:44 eric Exp $';
include_once('Class.DbObj.php');
include_once('Class.QueryDb.php');
include_once('Class.Log.php');





Class Docvalue extends DbObj
{
  var $fields = array ( "docid","attrid","value","zouq");

  var $id_fields = array ("docid", "attrid");

  var $dbtable = "docvalue";

  var $order_by="docid";

  var $fulltextfields = array ( "docid","attrid","value");

  var $sqlcreate = "
create table docvalue ( docid  int not null,
                        attrid varchar(20) not null,
                        value  text,
                        zouq varchar(1)
                   ); 
create unique index idx_docvalue on docvalue (docid, attrid);";

  // --------------------------------------------------------------------
  function PreUpdate() {
  
    // modify need to add before if not exist
    $query = new QueryDb($this->dbaccess, "Docvalue");


        $query->basic_elem->sup_where=array ("attrid = '".$this->attrid."'",
    				 "docid = ".$this->docid);
    
    $query->Query();
    if ($query->nb == 0)
      $this->Add();
  }




  // return docs where text is in value
  function GetDocids($text)
    {
  
      $query = new QueryDb($this->dbaccess,"$this->dbtable");

      $query->basic_elem->sup_where=array ("value ~* '.*$text.*'");
    
      $table1 = $query->Query();
      
      $title = array();


      if ($query->nb > 0)
	{
	  while(list($k,$v) = each($table1)) 
	    {
	      $title[$k] = $v->docid;
	    }
	  unset ($table1);
	}
      return $title;

    }


  // delete all values for a document
  function DeleteValues($docid)
    {
  
      $query = new QueryDb($this->dbaccess,"$this->dbtable");

      $query->basic_elem->sup_where=array ("docid = $docid");
    
      $table1 = $query->Query();
      



      if ($query->nb > 0)
	{

	  while(list($k,$v) = each($table1)) 
	    {
	      $table1[$k] -> delete();
	    }
	  unset ($table1);


	}
  
    }
  
}
?>
