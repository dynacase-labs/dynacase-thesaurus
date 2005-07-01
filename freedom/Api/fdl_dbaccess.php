<?php
/**
 * Get database coordonate for freedom access by psql
 *
 * @author Anakeen 2000 
 * @version $Id: fdl_dbaccess.php,v 1.1 2005/07/01 15:08:57 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

$dbaccess=getParam("FREEDOM_DB");
if ($dbaccess != "") {
  if (ereg('dbname=[ ]*([a-z_0-9]*)',$dbaccess,$reg)) {  
      $dbname=$reg[1];
    }
    if (ereg('host=[ ]*([a-z_0-9]*)',$dbaccess,$reg)) {  
      $dbhost=$reg[1];
    }
    if (ereg('port=[ ]*([a-z_0-9]*)',$dbaccess,$reg)) {  
      $dbport=$reg[1];
    }
    $dbpsql="";
    if ($dbhost != "")  $dbpsql.= "--host $dbhost ";
    if ($dbport != "")  $dbpsql.= "--port $dbport ";
    $dbpsql.= "--username anakeen --dbname $dbname ";
}

print $dbpsql;

?>