<?php
/**
 * Import SKOS thesaurus
 *
 * @author Anakeen 2000 
 * @version $Id: inputtree.php,v 1.2 2008/08/07 06:09:21 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage THESAURUS
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("THESAURUS/Lib.Thesaurus.php");

function inputtree(&$action) {
  $id=getHttpVars("id"); 
  $filter=getHttpVars("filter"); 
  $aid=getHttpVars("aid"); 
  $level=2;
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  $doc=new_doc($dbaccess,$id);
  if ($doc->isAlive()) {
    $t=getConceptsLevel($dbaccess, $doc->initid, $level);
    foreach ($t as $k=>$v) {
      if ($v["thc_level"]==0) {

	if (($filter == "") || (eregi($filter, $v["title"].$v["thc_langlabel"], $reg))) {
	  $t0[]=array("title"=>$v["title"],
		    "desc"=>$v["thc_langlabel"],
		    "thid"=>$v["initid"],
		    "child"=>getUltree($t,$v["initid"]));
	}
      }
    }
  }
  $action->lay->set("first",true);
  $action->lay->set("aid",$aid);
  $action->lay->setBlockData("LIs",$t0);
}


function getUltree(&$t, $initid) {
  $lay=new Layout(getLayoutFile("THESAURUS","inputtree.xml"));
  $b=array();
  foreach ($t as $k=>$v) {
    if ($v["thc_broader"]==$initid) {
      $b[]=array("title"=>$v["title"],
		 "desc"=>$v["thc_langlabel"],
		 "thid"=>$v["initid"],
		 "child"=>getUltree($t,$v["initid"]));
      
    }
  }
  if (count($b)==0) return "";
  $lay->set("first",false);
  $lay->setBlockData("LIs",$b);
  return $lay->gen();
}






?>