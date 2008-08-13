<?php

/**
 * thesaurus Library
 *
 * @author Anakeen 2008
 * @version $Id: Lib.Thesaurus.php,v 1.3 2008/08/13 10:09:32 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage THESAURUS
 */
 /**
 */

include_once("FDL/Class.SearchDoc.php");

/**
 * return concept document from URI reference
 * @param string $dbaccess database coordinates
 * @param string $uri URI to find
 * @return Doc document find - false if not find
 */
function getConceptFromURI($dbaccess,$uri) {
  $s=new SearchDoc($dbaccess, "THCONCEPT");
  //  $s->addFilter("thc_thesaurus=".$this->getValue("thc_thesaurus"));
  $s->addFilter("thc_uri='".pg_escape_string($uri)."'"); 
  $s->setObjectReturn();
  $s->noViewControl();
  $t=$s->search();
  if ($s->count() == 1) return $s->nextDoc();
  return false;    
  }
/**
 * return concept Id from URI reference
 * @param string $dbaccess database coordinates
 * @param string $uri URI to find
 * @return int document identificator find - false if not find
 */
function getConceptIdFromURI($dbaccess,$uri) {
  $s=new SearchDoc($dbaccess, "THCONCEPT");
  //  $s->addFilter("thc_thesaurus=".$this->getValue("thc_thesaurus"));
  $s->addFilter("thc_uri='".pg_escape_string($uri)."'"); 
  
  $s->noViewControl();
  $t=$s->search();
  if ($s->count() == 1) return $t[0]["initid"];
  return false;    
  }

/**
 * return thesaurus document from URI reference
 * @param string $dbaccess database coordinates
 * @param string $uri URI to find
 * @return Doc document find - false if not find
 */
function getThesaurusFromURI($dbaccess,$uri) {
  $s=new SearchDoc($dbaccess, "THESAURUS");
  //  $s->addFilter("thc_thesaurus=".$this->getValue("thc_thesaurus"));
  $s->addFilter("thes_uri='".pg_escape_string($uri)."'"); 
  $s->setObjectReturn();
  $s->noViewControl();
  $t=$s->search();
  if ($s->count() == 1) return $s->nextDoc();
  return false;    
  }


/**
 * return localized concept in a language document from concept
 * @param string $dbaccess database coordinates
 * @param int $idc document identificator of concept
 * @param string $lang language to find
 * @return Doc document find - false if not find
 */
function getLangConcept($dbaccess,$idc,$lang) {
  $s=new SearchDoc($dbaccess, "THLANGCONCEPT");
  //  $s->addFilter("thc_thesaurus=".$this->getValue("thc_thesaurus"));
  $s->addFilter("thcl_thconcept='".pg_escape_string($idc)."'"); 
  $s->addFilter("thcl_lang='".pg_escape_string($lang)."'"); 
 
  $s->setObjectReturn();
  $s->noViewControl();
  $t=$s->search();

  if ($s->count() == 1) return $s->nextDoc();
  return false;      
  }

/**
 * return all localized concept document from concept
 * @param string $dbaccess database coordinates
 * @param int $idc document identificator of concept
 * @return array of document values
 */
function getLangConcepts($dbaccess,$idc) {
  $s=new SearchDoc($dbaccess, "THLANGCONCEPT");
  //  $s->addFilter("thc_thesaurus=".$this->getValue("thc_thesaurus"));
  $s->addFilter("thcl_thconcept='".pg_escape_string($idc)."'");  
  $s->noViewControl();
 
  $t=$s->search();
  return $t;   
}

/**
 * return all  concept of thesaurus <= level
 * @param string $dbaccess database coordinates
 * @param int $idt thesaurus identificator of concept
 * @param int $level level : 0 is top level
 * @return array of document values
 */
function getConceptsLevel($dbaccess,$idt,$level) {
  $s=new SearchDoc($dbaccess, "THCONCEPT");
  $s->addFilter("thc_thesaurus=".intval($idt));
  $s->addFilter("thc_level <=".intval($level)); 
  $s->noViewControl();

  $t=$s->search();
  return $t;   
}
/**
 * return number of document matching concept
 */
function getThCardinal($dbaccess,$famid,$thvalue,$aid="") {
  static $fid="";
  static $thoa=false;
  static $amulti="";
  static $th=false;
  $cardinal="$dbaccess $famid,$thid";

  if ($fid != $famid) {
    $fdoc=new_doc($dbaccess,$famid);
    if (! $fdoc->isAlive()) return (sprintf(_("document %s not alive"),$famid));
    $at=$fdoc->getNormalAttributes();
    foreach ($at as $k=>$oa) {
      if (($aid == "") || ($aid==$oa->id)) {
	if ($oa->type=="thesaurus") {
	  $aid=$oa->id;
	  $thid=$oa->format;
	  $fid=$famid;
	  $thoa=$oa;
	  $tho=new_doc($dbaccess,$thid);
	  if ($tho->isAlive()) $th=$tho;
	  else return (sprintf(_("thesaurus %s not alive"),$thid));
	  break;
	}
      }
    }    
    include_once("FDL/Class.SearchDoc.php");  
  }
  if ($th) {
    
 
 
    $s=new SearchDoc($dbaccess,$fid);
    $thsql=$th->getSqlFilter($thoa,$thvalue);

    $s->addFilter($thsql);
    //$s->slice=$slice;
    $s->orderby='';


    $cardinal=$s->onlyCount();
  }
  return $cardinal;
}
?>

