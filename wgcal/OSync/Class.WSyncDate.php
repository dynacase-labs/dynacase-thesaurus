<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005 
 * @version $Id: Class.WSyncDate.php,v 1.1 2005/04/11 19:05:42 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once('Class.QueryDb.php');
include_once('Class.DbObj.php');
Class WSyncDate extends DbObj {

var $fields = array ( "uid", "server_date", "outlook_date" );
var $id_fields = array ("uid");
var $dbtable = "wsyncdate";
var $sqlcreate = "create table wsyncdate ( 
                        uid                int,    
                        server_date        int,    
                        outlook_date       int );  
                  create index wsyncdate_idx on sessions(uid);";


}

?>