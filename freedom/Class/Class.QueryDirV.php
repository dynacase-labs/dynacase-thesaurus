<?php
// ---------------------------------------------------------------
// $Id: Class.QueryDirV.php,v 1.1 2001/11/09 09:41:14 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Attic/Class.QueryDirV.php,v $
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
// $Log: Class.QueryDirV.php,v $
// Revision 1.1  2001/11/09 09:41:14  eric
// gestion documentaire
//
//
// ---------------------------------------------------------------


$CLASS_CONTACT_PHP = '$Id: Class.QueryDirV.php,v 1.1 2001/11/09 09:41:14 eric Exp $';
include_once('Class.DbObj.php');
include_once('Class.QueryDb.php');
include_once('Class.Log.php');

  
Class QueryDirV extends DbObj
{
  var $fields = array ( "dirid","childid","qid");

  var $id_fields = array ("dirid");

  var $dbtable = "dirv";

  var $order_by="dirid";

  var $fulltextfields = array ("");

  var $sqlcreate = "
create table dirv ( dirid      int not null,
                    childid    int not null,
                    qid        int not null
                   );";


  // --------------------------------------------------------------------
  function getChildId() {
    // return array of document id includes in a directory
  // --------------------------------------------------------------------

    $tableid = array();
    $query = new QueryDb($this->dbaccess,"QueryDirV");
    $query -> AddQuery("dirid=".$this->dirid);
    $tableq=$query->Query();
    if ($query->nb > 0)
      {
	while(list($k,$v) = each($tableq)) 
	  {
	    $tableid[$k] = $v->childid;
	  }
	unset ($tableq);
      }


    return($tableid);
  }

  // --------------------------------------------------------------------
  function getQids($docid) {
    // return array of document id includes in a directory
  // --------------------------------------------------------------------

    $tableid = array();
    $query = new QueryDb($this->dbaccess,"QueryDirV");
    $query -> AddQuery("dirid=".$this->dirid);
    $query -> AddQuery("childid=".$docid);
    $tableq=$query->Query();
    if ($query->nb > 0)
      {
	while(list($k,$v) = each($tableq)) 
	  {
	    $tableid[$k] = $v->qid;
	  }
	unset ($tableq);
      }


    return($tableid);
  }
  

  // to be optimized
  function getChildRep($dirid) {
    $tableid = array();
    $query = new QueryDb($this->dbaccess,"QueryDirV");
    $query -> AddQuery("dirid=".$dirid);
    $tableq=$query->Query();
    if ($query->nb > 0)
      {
	while(list($k,$v) = each($tableq)) 
	  {
	    $doc = new Doc($this->dbaccess, $v->childid); // very slow
	    if ($doc->doctype == 'D')
	      $tableid[] = $doc;
	  }
	unset ($tableq);
      }


    return($tableid);
  }


}
?>
