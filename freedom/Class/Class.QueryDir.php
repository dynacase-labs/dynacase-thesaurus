<?php
// ---------------------------------------------------------------
// $Id: Class.QueryDir.php,v 1.5 2001/11/22 17:49:13 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Attic/Class.QueryDir.php,v $
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
// $Log: Class.QueryDir.php,v $
// Revision 1.5  2001/11/22 17:49:13  eric
// search doc
//
// Revision 1.4  2001/11/21 17:03:54  eric
// modif pour création nouvelle famille
//
// Revision 1.3  2001/11/21 13:12:55  eric
// ajout caractéristique creation profil
//
// Revision 1.2  2001/11/15 17:51:50  eric
// structuration des profils
//
// Revision 1.1  2001/11/09 09:41:14  eric
// gestion documentaire
//
//
// ---------------------------------------------------------------


$CLASS_CONTACT_PHP = '$Id: Class.QueryDir.php,v 1.5 2001/11/22 17:49:13 eric Exp $';
include_once('Class.DbObj.php');
include_once('Class.QueryDb.php');
include_once('Class.Log.php');
include_once('FREEDOM/Class.QueryDirV.php');

  
Class QueryDir extends DbObj
{
  var $fields = array ( "id","dirid","query","qtype");

  var $id_fields = array ("id");

  var $dbtable = "dirq";

  var $order_by="dirid";

  var $fulltextfields = array ("");

  var $sqlcreate = "
create table dirq ( id      int not null,
                    dirid   int not null,
                    query   varchar(1024),
                    qtype   varchar(1)
                   );
create sequence seq_id_qdoc start 10";


  // --------------------------------------------------------------------
  function PreInsert()
    // --------------------------------------------------------------------
    {
      
	// compute new id
	if ($this->id == "") {
	  $res = pg_exec($this->dbid, "select nextval ('seq_id_qdoc')");
	  $arr = pg_fetch_array ($res, 0);
	  $this->id = $arr[0];
	  
	}
    }
      

  // --------------------------------------------------------------------
  function PostInsert()
    // --------------------------------------------------------------------    
    {
      // update pre-calculate value 
      $tableid = array();
      $query = new QueryDb($this->dbaccess,"QueryDir");
      
      $tableq=$query->Query(0,0,"TABLE",$this->query);
      if ($query->nb > 0)
      {
	$oqdv = new QueryDirV($this->dbaccess);
	$dir = new Doc($this->dbaccess,$this->dirid);
	$oqdv->dirid = $dir->initid;
	while(list($k,$v) = each($tableq)) 
	  {
	    $oqdv->childid = $v["id"];
	    $oqdv->qid = $this->id;
	    $err = $oqdv->Add();
	    if ($err != "") return $err;
	  }
      }

    }


  // --------------------------------------------------------------------
  function RefreshDir($dirid)
    // --------------------------------------------------------------------    
    {
      // refresh values of QueryDirV table
      $dir = new Doc($this->dbaccess,$dirid);// use initial id for directories
      $oqdv = new QueryDirV($this->dbaccess,$dir->initid);
      $oqdv-> Delete();

      $querydir = new QueryDb($this->dbaccess,"QueryDir");
      $querydir->AddQuery("dirid=$dirid");
      $lqd=$querydir->Query();
      

      $query = new QueryDb($this->dbaccess,"QueryDir");
      $oqdv = new QueryDirV($this->dbaccess);

      if (is_array($lqd)) {
      while(list($k,$dq) = each($lqd)) {

	$tableq=$query->Query(0,0,"TABLE",$dq->query);
	if ($query->nb > 0)
	  {
	    $oqdv->dirid = $dir->initid;
	    while(list($k,$v) = each($tableq)) 
	      {
	
		$oqdv->childid = $v["id"];
		$oqdv->qid = $dq->id;
		$oqdv->Add();
	      }
	  
	  }
      }

      }
    }
  


  // --------------------------------------------------------------------
  function DeleteDir($dirid)
    // --------------------------------------------------------------------    
    {
      // refresh values of QueryDirV table
      $dir = new Doc($this->dbaccess,$dirid);// use initial id for directories
      $oqdv = new QueryDirV($this->dbaccess,$dir->initid);
      $oqdv-> Delete();

      $querydir = new QueryDb($this->dbaccess,"QueryDir");
      $querydir->AddQuery("dirid=$dirid");
      $lqd=$querydir->Query();
      

      $query = new QueryDb($this->dbaccess,"QueryDir");
      $oqdv = new QueryDirV($this->dbaccess);

      if (is_array($lqd)) {
	while(list($k,$dq) = each($lqd)) {
	  $dq->delete();
	}      
      }
    }


}
?>
