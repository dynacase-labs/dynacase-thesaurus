<?php
/**
 * History log for document
 *
 * @author Anakeen 2005
 * @version $Id: Class.DocUTag.php,v 1.1 2006/07/27 16:16:07 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 */
/**
 */



include_once("Class.DbObj.php");
Class DocUtag extends DbObj {
  public $fields = array ( "id", // doc id
                           "initid", // doc initid
			   "uid",  // user what id
			   "uname", // use name
			   "date", // date of entry
			   "tag", // tag code
			   "fromuid", // user what id of the user which has set the tag	
			   "comment"
			   );

  /**
   * identificator of document
   * @public int
   */
  public $id;  
			  
  /**
   * identificator system of the user
   * @public int
   */
  public $uid;		  
  /**
   * firstname and last name of the user
   * @public string
   */
  public $uname;		  
  /**
   * comment date record
   * @public date
   */
  public $date;		  
  /**
   * level of comment
   * @public int
   */
  public $tag;		  
  /**
   * identificator system of the author user
   * @public int
   */
  public $fromuid;


  public $id_fields = array ("id","uid","tag");

  public $dbtable = "docutag";


  public $sqlcreate = "
create table docutag ( id int not null,   
                   initid int not null,                    
                   uid int not null,
                   uname text,
                   date timestamp,
                   tag text,
                   fromuid int,
                   comment text);
create index i_docutag on docutag(id);
create index in_docutag on docutag(initid);
";


}
?>