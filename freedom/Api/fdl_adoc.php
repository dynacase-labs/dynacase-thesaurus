<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: fdl_adoc.php,v 1.11 2003/12/30 10:12:41 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



// refreah for a classname
// use this only if you have changed title attributes

include_once("FDL/Lib.Attr.php");
include_once("FDL/Class.DocFam.php");




$appl = new Application();
$appl->Set("FDL",	   $core);


$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}



$docid = GetHttpVars("docid",0); // special docid


	
$query = new QueryDb($dbaccess,"DocFam");
$query ->AddQuery("doctype='C'");
$query->order_by="id";

  
if ($docid > 0) {
  $query->AddQuery("id=$docid");
  $tid = $query->Query(0,0,"TABLE");
} else {
  // sort id by dependance
  $table1 = $query->Query(0,0,"TABLE");
  $tid=array();
  pushfam(0, $tid, $table1);  
}      

    
if ($query->nb > 0)	{

  $pubdir = $appl->GetParam("CORE_PUBDIR");

  foreach ($tid as $k=>$v)   {	     
    //    print AttrtoPhp($dbaccess,$v->id);
    print "$pubdir/FDLGEN/Class.Doc".$v["id"].".php\n";
    createDocFile($dbaccess,$v);
    
    $msg=PgUpdateFamilly($dbaccess, $v["id"]);
    print $msg;
    if ($v["usefor"] == "W") { // add special attribute for workflow
      $wdoc= createDoc($dbaccess,$v["id"]);
            $wdoc->CreateProfileAttribute();
    }
    activateTrigger($dbaccess, $v["id"]);
    
  }	 
  
}      
    

// recursive sort by fromid
function pushfam($fromid, &$tid, $tfam) {
  
  foreach($tfam as $k=>$v) {
   
    if ($v["fromid"]==$fromid) {
      $tid[$v["id"]]=$v;
     
      pushfam($v["id"],$tid,$tfam);
    }
  }
}

?>