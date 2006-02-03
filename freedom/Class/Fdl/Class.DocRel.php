<?php
/**
 * Document Relation Class
 *
 * @author Anakeen 2005
 * @version $Id: Class.DocRel.php,v 1.5 2006/02/03 08:10:54 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
/**
 */

include_once("Class.DbObj.php");
Class DocRel extends DbObj {
  public $fields = array ( "sinitid", // source id
			   "cinitid",  // cible id
			   "ctitle", // title of cible
			   "cicon", // icon of cible
			   "stitle", // title of source
			   "sicon", // icon of source
			   "type"); // relation kind);

  /**
   * identificator of the source document
   * @public int
   */
  public $sinitid;  
			  
  /**
   * identificator of the cible document
   * @public int
   */
  public $cinitid;		  
  /**
   * title of the cible document
   * @public int
   */
  public $title;		  
  /**
   * relation kind
   * @public int
   */
  public $type;


  public $id_fields = array ("sinitid");

  public $dbtable = "docrel";


  public $sqlcreate = "
create table docrel ( sinitid int not null,                   
                   cinitid int not null,
                   stitle text,
                   ctitle text,
                   sicon text,
                   cicon text,
                   type text  );
create index i_docrelc on docrel(cinitid);
create index i_docrels on docrel(sinitid);
create unique index docrel_u on docrel(sinitid,cinitid,type);
";


  public function getRelations() {
    include_once("Class.QueryDb.php");
    $q=new QueryDb($this->dbaccess,get_class($this));
    $q->AddQuery("sinitid=".$this->sinitid);
    $l=$q->Query(0,0,"TABLE");
    if (is_array($l))  return $l;
    return array();
  }
  public function getIRelations() {
    include_once("Class.QueryDb.php");
    $q=new QueryDb($this->dbaccess,get_class($this));
    $q->AddQuery("cinitid=".$this->sinitid);
    $l=$q->Query(0,0,"TABLE");
    if (is_array($l))  return $l;
    return array();
  }
  
  public function resetRelations($type="") {
    if ($this->sinitid > 0) {
      if ($type != "")  $this->exec_query("delete from docrel where sinitid=".$this->sinitid." and type='$type'");
      else $this->exec_query("delete from docrel where sinitid=".$this->sinitid." and type != 'folder'");
    }
  }
}