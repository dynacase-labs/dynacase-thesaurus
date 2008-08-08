<?php
/**
 * Import SKOS thesaurus
 *
 * @author Anakeen 2000 
 * @version $Id: inputtree.php,v 1.5 2008/08/08 15:20:45 eric Exp $
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
  $multi=(getHttpVars("multi")=="yes")?'multi':false; 
  $level=2;
  $b1=microtime(true);
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $lang='fr';
  
  //   header("Pragma: cache "); // HTTP 1.0
  $doc=new_doc($dbaccess,$id);
  if ($doc->isAlive()) {
    $t=$action->read("conceptlevel");
    if (! $t) {
      $t=getConceptsLevel($dbaccess, $doc->initid, $level);
    }
  $b2=microtime(true);
    foreach ($t as $k=>$v) {
      if ($v["thc_level"]==0) {
	$label=getLabelLang($v,$lang);
	$isgood=(($filter == "") || (eregi($filter, $v["title"].$label, $reg)));
	$oneisgood |= $isgood;
	$child=getUltree($t,$v["initid"],$filter,$childgood,$lang);
	$oneisgood |= $childgood;
	  $t0[]=array("title"=>$v["title"],
		      "desc"=>$label,
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
  $action->lay->set("multi",$multi);
  $action->lay->setBlockData("LIs",$t0);
  $action->lay->set("time",sprintf("%0.3f [%.03f][%.03f]", $b2-$b1,
				   microtime(true) - $b1,
				   microtime(true) - $action->b1));

  if (! $oneisgood) $action->lay->set("error",sprintf(_("no result matching %s"),$filter));
  else $action->lay->set("error","");


}
function getLabelLang($v,$lang) {
  $tlang=Doc::_val2array($v["thc_lang"]);
  $tll=Doc::_val2array($v["thc_langlabel"]);

  $kgood=-1;

  foreach ($tlang as $k=>$v) {
    if ($tlang[$k] == $lang) {
      $kgood=$k;
      break;
    }
  }

  return (isset($tll[$kgood]))?$tll[$kgood]:$tll[0];
}

function getUltree(&$t, $initid,$filter,&$oneisgood,$lang) {
  $lay=new Layout(getLayoutFile("THESAURUS","inputtree.xml"));
  $b=array();
  $oneisgood=false;
  foreach ($t as $k=>$v) {
    if ($v["thc_broader"]==$initid) {
	$label=getLabelLang($v,$lang);
      $isgood=(($filter == "") || (eregi($filter, $v["title"].$label, $reg)));
      $oneisgood |= $isgood;
      $child=getUltree($t,$v["initid"],$filter,$childgood,$lang);
      $oneisgood |= $childgood;
      $b[]=array("title"=>$v["title"],
		 "desc"=>$label,
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