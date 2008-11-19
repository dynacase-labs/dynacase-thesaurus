<?php

  /**
   * return sql filter to search document
   * @param DocAttribute $oa attribute identificator where do the search
   * @param int $thv value of concept to search
   * @return string sql filter
   */
function getSqlFilter($oa,$thv) {
  $sql="no $thv";
  $multi=($oa->getOption("multiple")=="yes");
  if ($multi) {
    if (is_array($thv)) {
      $sql="multi array";
      $thnr=array();
      foreach ($thv as $k=>$thid) {	
	$th=new_doc($this->dbaccess, $thid);
	if ($th->isAlive()) {
	  $thnr=array_merge($thnr,$th->getRNarrowers());
	  $thnr[]=$thid;
	}
      }
      if (count($thnr) == 1) $sql=sprintf("%s ~ '\\\\m%s\\\\M'",$oa->id,intval($thnr[0]));
      else $sql=$oa->id." ~ '\\\\m(".pg_escape_string(implode('|',$thnr)).")\\\\M'";
    } else {
	
      $sql="multi atom";
      $th=new_doc($this->dbaccess, $thv);
      if ($th->isAlive()) {
	$thnr=$th->getRNarrowers();
	$thnr[]=$thv;
	if (count($thnr) == 1) $sql=sprintf("%s ~ '\\\\m%s\\\\M'",$oa->id,intval($thv));
	else $sql=$oa->id." ~ '\\\\m(".pg_escape_string(implode('|',$thnr)).")\\\\M'";
      }
    }
  } else {
    if (is_array($thv)) {
      $sql="single array";
      $thnr=array();
      foreach ($thv as $k=>$thid) {	
	$th=new_doc($this->dbaccess, $thid);
	if ($th->isAlive()) {
	  $thnr=array_merge($thnr,$th->getRNarrowers());
	  $thnr[]=$thid;
	}	
      }
      if (count($thnr) == 1) $sql=sprintf("%s = '%s'",$oa->id,$thnr[0]);
      else $sql=GetSqlCond($thnr,$oa->id);
    } else {	
      $sql="single atom";
      $th=new_doc($this->dbaccess, $thv);
      if ($th->isAlive()) {
	$thnr=$th->getRNarrowers();
	$thnr[]=$thv;
	if (count($thnr) == 1) $sql=sprintf("%s = '%s'",$oa->id,intval($thv));
	else $sql=GetSqlCond($thnr,$oa->id);
      }
    }
  }
  
    
  
  return $sql;
  }

/**
 * refresh relations from uri
 */
function refreshConcepts() {
  include_once("FDL/Class.SearchDoc.php");
  define("MAXIMPORTTIME",600); // 10 minutes
  if (ini_get("max_execution_time") < MAXIMPORTTIME) ini_set("max_execution_time",MAXIMPORTTIME);
  $s=new SearchDoc($this->dbaccess,"THCONCEPT");
  $s->addFilter("thc_thesaurus=".$this->initid);
  $s->setObjectReturn();
  $s->search();
  while ($doc=$s->nextDoc()) {
    $doc->recomputeNarrower();
    $doc->setValue("thc_title",$doc->getLangTitle());
    $doc->refresh();
    $doc->modify();
  }

  $s->search();
  while ($doc=$s->nextDoc()) {
    $doc->setValue("thc_level",$doc->getLevel());   
    $doc->modify();
  }
}

?>