<?php
/**
 * Import SKOS thesaurus
 *
 * @author Anakeen 2000 
 * @version $Id: inputtree.php,v 1.3 2008/08/07 11:22:40 eric Exp $
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
	$isgood=(($filter == "") || (eregi($filter, $v["title"].$v["thc_langlabel"], $reg)));
	$oneisgood |= $isgood;
	$child=getUltree($t,$v["initid"],$filter,$childgood);
	$oneisgood |= $childgood;
	  $t0[]=array("title"=>$v["title"],
		      "desc"=>$v["thc_langlabel"],
		      "isfiltergood"=>$isgood,
		      "ischildgood"=>$childgood,
		      "nosee"=>(!$childgood) &&(!$isgood),
		      "openit"=>($childgood) &&(!$isgood),
		      "thid"=>$v["initid"],
		      "child"=>$child);	  
      }
    }
  }
  $action->lay->set("first",true);
  $action->lay->set("aid",$aid);
  $action->lay->setBlockData("LIs",$t0);
}


function getUltree(&$t, $initid,$filter,&$oneisgood) {
  $lay=new Layout(getLayoutFile("THESAURUS","inputtree.xml"));
  $b=array();
  $oneisgood=false;
  foreach ($t as $k=>$v) {
    if ($v["thc_broader"]==$initid) {
      $isgood=(($filter == "") || (eregi($filter, $v["title"].$v["thc_langlabel"], $reg)));
      $oneisgood |= $isgood;
      $child=getUltree($t,$v["initid"],$filter,$childgood);
      $oneisgood |= $childgood;
      $b[]=array("title"=>$v["title"],
		 "desc"=>$v["thc_langlabel"],
		 "thid"=>$v["initid"],
		 "isfiltergood"=>$isgood,
		 "ischildgoodnos"=>$childgood,
		 "nosee"=>(!$childgood) &&(!$isgood),
		 "openit"=>($childgood) &&(!$isgood),
		 "child"=>$child);
      
    }
  }
  if (count($b)==0) {    
    //$oneisgood=true; // for leaf
    return "";
  }
  $lay->set("first",false);
  $lay->setBlockData("LIs",$b);
  return $lay->gen();
}






?>