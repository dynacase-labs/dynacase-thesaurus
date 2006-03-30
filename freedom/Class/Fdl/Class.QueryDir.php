<?php
/**
 * Folder managing
 *
 * @author Anakeen 2001
 * @version $Id: Class.QueryDir.php,v 1.20 2006/03/30 09:52:49 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 */
 /**
 */


include_once("Class.DbObj.php");
include_once("Class.QueryDb.php");
include_once("Class.Log.php");

  
/**
 * Folder managing 
 * @package FREEDOM
 *
 */
Class QueryDir extends DbObj
{
  var $fields = array ( "dirid","query","childid","qtype");

  var $id_fields = array ("dirid","childid");

  var $dbtable = "fld";

  var $order_by="dirid";

  var $fulltextfields = array ("");

  var $sqlcreate = "
create table fld ( 
                    dirid   int not null,
                    query   text,
                    childid   int,
                    qtype   char
                   );
create index fld_iqd on fld(qtype,dirid);
create unique index fld_u on fld(qtype,dirid,childid);
create sequence seq_id_fld start 100;";

#CREATE TRIGGER tfldrel after insert or update or delete on fld FOR EACH ROW execute procedure relfld();";

  var $relatedCacheClass= array("doc"); // class must ne cleaned also in case of modify

  // --------------------------------------------------------------------
  function PreInsert()
    // --------------------------------------------------------------------
    {
      // test if not already exist 
      if ($this->qtype != "M") {
// 	$query = new QueryDb($this->dbaccess,"QueryDir");
// 	$query->AddQuery("dirid=".$this->dirid);
// 	$query->AddQuery("childid='".$this->childid."'");
// 	$query->Query(0,0,"TABLE");
// 	if ($query->nb != 0) return _("already exist : not added");
	$this->delete(false);
      }
      // compute new id
   //    if ($this->id == "") {
// 	$res = pg_exec($this->dbid, "select nextval ('seq_id_fld')");
// 	$arr = pg_fetch_array ($res, 0);
// 	$this->id = $arr[0];
	  
//       }
    }
 
}
?>
