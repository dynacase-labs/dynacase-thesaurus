<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005 
 * @version $Id: Class.WSyncIds.php,v 1.1 2005/04/11 19:05:42 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once('Class.QueryDb.php');
include_once('Class.DbObj.php');
Class WSyncIds extends DbObj{

var $fields = array ( "uid", "evid", "outlookid" );
var $id_fields = array ("uid", "evid");
var $dbtable = "wsyncids";
var $sqlcreate = "create table wsyncdate ( 
                        uid        int,    // user id
                        eid        int,    // event id
                        oid        int );  // outlook id
                  create index wsyncdate_idx on sessions(uid,eid);";


}

?>