<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: migr_2.3.1.php,v 1.2 2006/06/30 15:24:49 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



// refreah for a classname
// use this only if you have changed title attributes

include_once("FDL/Class.Dir.php");
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
function updateFldrel($dbaccess) {
  
  $query = new QueryDb($dbaccess,"QueryDir");
  $query->AddQuery("qtype='S'");
  $lfam=$query->Query(0,0,"TABLE");
  foreach ($lfam as $k=>$v) {
    print sprintf("update fld set qtype=qtype where dirid=%s and childid=%s;\n",$v["dirid"],$v["childid"]);

  }
 
      
    
  $table1 = $query->Query();
  
}
$dbaccess=GetParam("FREEDOM_DB");

addFamIndexes($dbaccess);
//updateFldrel($dbaccess);

?>