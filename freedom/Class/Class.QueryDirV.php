<?php
// ---------------------------------------------------------------
// $Id: Class.QueryDirV.php,v 1.2 2001/11/09 18:54:21 eric Exp $
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
// Revision 1.2  2001/11/09 18:54:21  eric
// et un de plus
//
// Revision 1.1  2001/11/09 09:41:14  eric
// gestion documentaire
//
//
// ---------------------------------------------------------------


$CLASS_CONTACT_PHP = '$Id: Class.QueryDirV.php,v 1.2 2001/11/09 18:54:21 eric Exp $';
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
  


  function getChildRep($dirid) {
    // query to find child directories
    $qsql= "select t0.* from doc t0,dirv t1,dirq t2  where  (t2.id=t1.qid) and  (t2.dirid=t1.dirid) and  (t0.id=t1.childid) and (t0.doctype='D') and (t2.dirid=$dirid);";


    $tableid = array();
    $query = new QueryDb($this->dbaccess,"Doc");
    $query -> AddQuery("dirid=".$dirid);

    $tableq=$query->Query(0,0,"LIST",$qsql);
    if ($query->nb > 0)
      {
	while(list($k,$v) = each($tableq)) 
	  {
	    //	    $doc = new Doc($this->dbaccess, $v->childid); // very slow
	    //if ($v->doctype == 'D')
	      $tableid[] = $v;
	  }
	unset ($tableq);
      }


    return($tableid);
  }

  function getChildDoc($dirid) {
    // query to find child directories
    $qsql= "select t0.* from doc t0,dirv t1,dirq t2  where  (t2.id=t1.qid) and  (t2.dirid=t1.dirid) and  (t0.id=t1.childid) and (t0.doctype='F') and (t2.dirid=$dirid);";


    $tableid = array();
    $query = new QueryDb($this->dbaccess,"Doc");
    $query -> AddQuery("dirid=".$dirid);

    $tableq=$query->Query(0,0,"LIST",$qsql);
    if ($query->nb > 0)
      {
	while(list($k,$v) = each($tableq)) 
	  {
	    //	    $doc = new Doc($this->dbaccess, $v->childid); // very slow
	    //if ($v->doctype == 'D')
	      $tableid[] = $v;
	  }
	unset ($tableq);
      }


    return($tableid);
  }

}
?>
