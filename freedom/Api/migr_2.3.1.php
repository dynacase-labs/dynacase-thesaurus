<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: migr_2.3.1.php,v 1.1 2006/04/18 07:52:51 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



// refreah for a classname
// use this only if you have changed title attributes

include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocFam.php");

function addFamIndexes($dbaccess) {
  
  $query = new QueryDb($dbaccess,"Docfam");
  $lfam=$query->Query(0,0,"TABLE");
  foreach ($lfam as $k=>$v) {
    print sprintf("create index doc_initid%d on doc%d(initid);\n",$v["id"],$v["id"]);
    print sprintf("create index doc_fldrels%d on doc%d(fldrels);\n",$v["id"],$v["id"]);
  }
 
      
    
  $table1 = $query->Query();
  
}
$dbaccess=GetParam("FREEDOM_DB");

addFamIndexes($dbaccess);

?>