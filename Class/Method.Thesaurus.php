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
      if (count($thnr) == 1) $sql=sprintf("%s ~ '\\\\m%s\\\\M'",$oa->id,intval($thv));
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
      if (count($thnr) == 1) $sql=sprintf("%s = '%s'",$oa->id,intval($thv));
      else $sql=GetSqlCond($thnr,$oa->id,true);
    } else {	
      $sql="single atom";
      $th=new_doc($this->dbaccess, $thv);
      if ($th->isAlive()) {
	$thnr=$th->getRNarrowers();
	$thnr[]=$thv;
	if (count($thnr) == 1) $sql=sprintf("%s = '%s'",$oa->id,intval($thv));
	else $sql=GetSqlCond($thnr,$oa->id,true);
      }
    }
  }
  
    
  
  return $sql;
  }

?>