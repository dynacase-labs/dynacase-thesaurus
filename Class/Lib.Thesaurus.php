<?php

/**
 * thesaurus Library
 *
 * @author Anakeen 2008
 * @version $Id: Lib.Thesaurus.php,v 1.1 2008/08/06 15:11:52 eric Exp $
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
 

  $t=$s->search();
  return $t;   
}

?>

