<?php
/**
 * Class.DocVaultIndex.php manages a full index
 * for files attached to a Freedom document
 *
 * @author Anakeen 2000 
 * @version $Id: Class.DocVaultIndex.php,v 1.1 2004/10/18 08:46:13 marc Exp $
 * @license http://license.i-cesam.com/license.php
 * @package FREEDOM
 */
 /**
 */

$CLASS_DOCVAULTINDEX_PHP = '$Id: Class.DocVaultIndex.php,v 1.1 2004/10/18 08:46:13 marc Exp $';
include_once('Class.DbObj.php');
include_once('Class.QueryDb.php');
include_once('Class.Log.php');


Class DocVaultIndex extends DbObj
{
  var $fields = array ( "docid","vaultid");

  var $id_fields = array ("docid", "vaultid");

  var $dbtable = "docvaultindex";

  var $order_by="docid";

  var $sqlcreate = "
create table docvaultindex ( docid  int not null,
                             vaultid int not null
                   ); 
create unique idx_docvaultindex on docvaultindex (docid, vaultid);";

  // --------------------------------------------------------------------
  function GetDocId($vid) {
    $t = array();
    $query = new QueryDb($this->dbaccess, "DocVaultIndex");
    $query->basic_elem->sup_where=array ("vaultid = $vid");
    $t = $query->Query();
    return $t;
  }

  function DeleteDoc($docid) {
      $err=$this->exec_query("delete from ".$this->dbtable." where docid=".$docid  );
      return $err;
  }
  
}
?>
