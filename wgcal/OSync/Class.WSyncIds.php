<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005 
 * @version $Id: Class.WSyncIds.php,v 1.3 2005/04/22 16:03:29 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once('Class.QueryDb.php');
include_once('Class.DbObj.php');
Class WSyncIds extends DbObj{

var $fields = array ( "user_id", "event_id", "outlook_id" );
var $id_fields = array ("user_id", "event_id");
var $dbtable = "wsyncids";
var $sqlcreate = "create table wsyncids ( 
                        user_id        int,    
                        event_id        int,    
                        outlook_id      text );  
                  create index wsyncids_idx on sessions(user_id,outlook_id);";


}

?>