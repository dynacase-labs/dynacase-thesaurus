<?php

// ---------------------------------------------------------------
// $Id: Class.DocAttr.php,v 1.3 2002/04/22 14:03:35 eric Exp $
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
// $Log: Class.DocAttr.php,v $
// Revision 1.3  2002/04/22 14:03:35  eric
// ajout type date dans attribut
//
// Revision 1.2  2002/03/14 14:56:55  eric
// evolution de incident
//
// Revision 1.1  2002/02/13 14:31:58  eric
// ajout usercard application
//
// Revision 1.6  2001/12/13 17:45:01  eric
// ajout attribut classname sur les doc
//
// Revision 1.5  2001/12/10 10:05:52  eric
// mise en place iFrame pour choix enum
//
// Revision 1.4  2001/12/08 17:16:30  eric
// evolution des attributs
//
// Revision 1.3  2001/11/30 15:13:39  eric
// modif pour Css
//
// Revision 1.2  2001/11/26 18:01:02  eric
// new popup & no lock for no revisable document
//
// Revision 1.1  2001/11/09 09:41:14  eric
// gestion documentaire
//
// Revision 1.3  2001/06/22 09:44:45  eric
// support attribut multimédia
//
// Revision 1.2  2001/06/19 16:15:39  eric
// importation fichier
//
// Revision 1.1  2001/06/13 14:41:13  eric
// contact address book
//
// ---------------------------------------------------------------

$CLASS_CONTACTATTR_PHP = '$Id: Class.DocAttr.php,v 1.3 2002/04/22 14:03:35 eric Exp $';
include_once('Class.DbObj.php');
include_once('Class.QueryDb.php');
include_once('Class.Log.php');

Class Docattr extends DbObj
{
  var $fields = array ("id","docid","frameid","labeltext", "title", "abstract","type","ordered","visibility","link","phpfile", "phpfunc","todefine");

  var $id_fields = array ("docid","id");

  var $dbtable = "docattr";

  var $order_by="ordered";

  var $fulltextfields = array ("labeltext");

  var $sqlcreate = "
create table docattr ( id      varchar(20) not null,
                     primary key (id),
                     docid int not null,
                     FrameId  varchar(20),
                     LabelText varchar(60),
                     Title  varchar(1),
                     Abstract  varchar(1),
                     Type  varchar(20),
                     ordered int,
                     visibility varchar(1),
                     link varchar(256),
                     phpfile varchar(64),
                     phpfunc varchar(128),
                     todefine varchar(20)
                   );
create sequence seq_id_docattr start 1000";


  // possible type of attributes
  var $deftype = array("text",
		       "longtext",
		       "image",
		       "file",
		       "frame",
		       "enum",
		       "date",
		       "textlist",
		       "enumlist");
		    
 
  
     
  function PreInsert()
    {

      // compute new id

  
      if ($this->id == "") {
	$res = pg_exec($this->dbid, "select nextval ('seq_id_docattr')");
	$arr = pg_fetch_array ($res, 0);
	$this->id = $arr[0];  
      }

      if ($this->abstract=="") $this->abstract='N';
      if ($this->title=="") $this->title='N';
      if ($this->visibility=="") $this->visibility='W';
    } 


 






  // return array of attributes id for abstract
  function GetAbstractIds()
    {
      $query = new QueryDb($this->dbaccess,"$this->dbtable");

      $query->basic_elem->sup_where=array ("abstract='Y'");
    
      $table1 = $query->Query();
      
      $abstract = array();

      if ($query->nb > 0)
	{
	  while(list($k,$v) = each($table1)) 
	    {
	      $abstract[$k] = $v->id;
	    }
	  unset ($table1);
	}
      return $abstract;
    }


  // return array of attributes id for title
  function GetTitleIds()
    {
      $query = new QueryDb($this->dbaccess,"$this->dbtable");

      $query->basic_elem->sup_where=array ("title='Y'");
    
      $table1 = $query->Query();
      
      $title = array();

      if ($query->nb > 0)
	{
	  while(list($k,$v) = each($table1)) 
	    {
	      $title[$k] = $v->id;
	    }
	  unset ($table1);
	}
      return $title;
    }
  
    
  // return array of attributes id for partivular type
  // type are [text, longtext, image, url, mail]
  function GetTypedIds($type)
    {
      $query = new QueryDb($this->dbaccess,"$this->dbtable");

      $query->basic_elem->sup_where=array ("type='".$type."'");
    
      $table1 = $query->Query();
      
      $title = array();

      if ($query->nb > 0)
	{
	  while(list($k,$v) = each($table1)) 
	    {
	      $title[$k] = $v->id;
	    }
	  unset ($table1);
	}
      return $title;
    }
  
    
}
?>
