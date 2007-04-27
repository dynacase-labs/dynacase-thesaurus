<?php
/**
 * History log for document
 *
 * @author Anakeen 2005
 * @version $Id: Class.DocRead.php,v 1.2 2007/04/27 06:52:29 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 */
/**
 */



include_once("Class.Doc.php");
Class DocRead extends DbObj {
 

  /**
   * identificator of document
   * @public int
   */
  public $id;  



  public $id_fields = array ("id");

  public $dbtable = "docread";
  public $sqlcreate = "
create table docread ( id int not null,
                   primary key (id),
                   owner int,
                   title varchar(256),
                   revision int ,
                   initid int,
                   fromid int,
                   doctype char,
                   locked int ,
                   allocated int ,
                   icon varchar(256),
                   lmodify char,
                   profid int ,
                   usefor char  ,
                   revdate int, 
                   version text,
                   cdate timestamp,  
                   adate timestamp,  
                   comment text,
                   classname varchar(64),
                   state varchar(64),
                   wid int ,  
                   values text,  
                   attrids text,  
                   postitid int,
                   cvid int,
                   name text,
                   dprofid int,
                   prelid int,
                   atags text,
                   confidential int,
                   ldapdn text,
                   fulltext tsvector
                   );
create index fromid_docread on docread(fromid);
create index title_docread on docread(title);
create index docty_docread on docread(doctype);
create index full_docread on docread using gist(fulltext);";

 public $fields = array ( "id","owner","title",
			   "revision","version",
			   "initid","fromid",
			   "doctype",
			   "locked",
			   "allocated",
			   "icon",
			   "lmodify",
			   "profid",
			   "usefor",
			   "cdate",
			   "adate",
			   "revdate",
			   "comment",
			   "classname",
			   "state",
			   "wid",
			   "postitid",
			   "cvid",
			   "name",
			   "dprofid",
			   "atags",
			   "prelid",
			   "confidential",
			   "ldapdn");

  public $sup_fields= array("values",
			    "attrids",
			    "fulltext"); // not be in fields else trigger error

}
?>