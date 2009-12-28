<?php
/**
 * View interface to search document from thesaurus
 *
 * @author Anakeen 2008
 * @version $Id: edittreesearch.php,v 1.6 2008/10/23 13:41:59 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage THESAURUS
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("THESAURUS/Lib.Thesaurus.php");
/**
 * View search interface
 * @param Action &$action current action
 * @global thid Http var : thesaurus document identificator to use
 * @global famid Http var : family document to search
 */
function edittreesearch(&$action) {
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $thid = GetHttpVars("thid");
  $filter=getHttpVars("filter"); 
  $fid = GetHttpVars("famid");
  $aid = strtolower(GetHttpVars("aid"));
  $multi=(getHttpVars("multi")=="yes")?'multi':false; 
  $level=getHttpVars("level",2); 
  $iname=getHttpVars("inputname","thvalue"); 
  $conid=getHttpVars("conid"); 

  if (! $lang) $lang=strtolower(strtok(getParam("CORE_LANG"),'_'));
  $error="";
  $b1=microtime(true);

  if ($conid) {
    $con=new_doc($dbaccess,$conid);
    if (! $con->isAlive()) $action->exitError(sprintf(_("document %s not alive"),$conid));
    $thid=$con->getValue("thc_thesaurus");
  }

  $fdoc=new_doc($dbaccess,$fid);
  if (! $thid) {
    if (! $fdoc->isAlive()) $action->exitError(sprintf(_("document %s not alive"),$fid));
    $at=$fdoc->getNormalAttributes();
    foreach ($at as $k=>$oa) {
      if ($oa->type=="thesaurus") {
	$aid=$oa->id;
	$thid=$oa->format;
	break;
      }
    }    
  }

  $th=new_doc($dbaccess,$thid);
  if (! $th->isAlive()) $action->exitError(sprintf(_("thesaurus %s not alive"),$thid));
	      
  if ($conid) $t=getChildConcepts($dbaccess, $conid);
  else $t=getConceptsLevel($dbaccess, $th->initid, $level);

  $b2=microtime(true);

  if ($conid) {
    $child=getUltree($t,$conid,$filter,$childgood,$lang,$fdoc->id,$aid,$dbaccess);
    $action->lay->template='[child]';

  } else {
    $child=getUltree($t,"",$filter,$childgood,$lang,$fdoc->id,$aid,$dbaccess);
  }
  $action->lay->set("first",true);
  $action->lay->set("child",$child);
  $action->lay->set("aid",$aid);
  $action->lay->set("multi",$multi);
  $action->lay->set("ymulti",$multi?"yes":"no");
  $action->lay->setBlockData("LIs",$t0);
  $action->lay->set("time",sprintf("%0.3f [%.03f]", $b2-$b1,
				   microtime(true) - $b1));



  $action->lay->set("thid",$thid);
  $action->lay->set("famid",$fid);
  $action->lay->set("iname",$iname);
  $action->lay->set("error",$error);
  
  
}

function getThLabelLang($v,$lang) {
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


function getUltree(&$t, $initid,$filter,&$oneisgood,$lang,$famid,$aid,$dbaccess) {


  $lay=new Layout(getLayoutFile("THESAURUS","editsubtreesearch.xml"));
  $b=array();
  $oneisgood=false;
  foreach ($t as $k=>$v) {
    if ($v["thc_broader"]==$initid) {
      $label=getThLabelLang($v,$lang);
      $isgood=(($filter == "") || (preg_match("/$filter/i", $v["thc_label"].$label, $reg)));
      $oneisgood |= $isgood;
      if ($filter) {
	$child=getUltree($t,$v["initid"],$filter,$childgood,$lang,$famid,$aid,$dbaccess);
	if ($child!="") $child='<ul>'.$child.'</ul>';
      } else {
	$child="";
	$childgood=hasChildConcepts($dbaccess, $v["initid"]);
	$isgood=true;
      }
      
      if ($childgood || $isgood) $cardinal=getThCardinal($dbaccess,$famid,$v["initid"],$aid);
      else $cardinal="nc";

      $oneisgood |= $childgood;
      $b[]=array("title"=>$v["thc_label"],
		 "desc"=>$label,
		 "conid"=>$v["initid"],
		 "isfiltergood"=>$isgood,
		 "ischildgoodnos"=>$childgood,
		 "nosee"=>(!$childgood) &&(!$isgood),
		 "openit"=>($childgood) &&(!$isgood),
		 "child"=>$child,
		 "filter"=>($filter != ""),
		 "cardinal"=>$cardinal);
      
    }
  }
  if (count($b)==0) {    
    return "";
  }
  $lay->setBlockData("LIs",$b);
  return $lay->gen();
}

?>