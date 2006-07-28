<?php

function mytagdoc($start,$slice,$tag,$userid=0) {
  include_once("FDL/Class.DocUTag.php");
  include_once("FDL/Lib.Dir.php");
  $dbaccess=getParam("FREEDOM_DB");
  if ($userid==0) $uid=getUserId();
  else $uid=$userid;
  $q=new QueryDb($dbaccess,"DocUTag");
  $q->AddQuery("uid=$uid");
  $q->AddQuery("tag='$tag'");
  $lq=$q->Query(0,1000,"TABLE");
  $lid=array();
  if ($q->nb > 0) {
    foreach($lq as $k=>$v) {
      $lid[$v["initid"]]=$v["id"];
    }
  }

  
  //print Doc::getTimeDate(0,true);
  $ltdoc=getDocsFromIds($dbaccess,$lid);
  // print "\nc=".count($ltdoc)."\n";
  //print Doc::getTimeDate(0,true);
  //  print_r2($ltdoc);

  return $ltdoc;

}


/**
 * function use for specialised search
 * return all document tagged TOVIEWDOC for current user
 * 
 * @param int $start start cursor
 * @param int $slice offset ("ALL" means no limit)
 * @param int $userid user system identificator (NOT USE in this function)
 * @param string return type "TABLE" only is allowed
 */
function mytoviewdoc($start="0", $slice="ALL",$userid=0,$qtype="TABLE") {
  return mytagdoc($start,$slice,"TOVIEW");
}


/**
 * function use for specialised search
 * return all document tagged  for current user
 * 
 * @param int $start start cursor
 * @param int $slice offset ("ALL" means no limit)
 * @param int $userid user system identificator (NOT USE in this function)
 * @param string return type "TABLE" only is allowed
 */
function myaffecteddoc($start="0", $slice="ALL",$userid=0,$qtype="TABLE") {
  return mytagdoc($start,$slice,"AFFECTED");
}

?>