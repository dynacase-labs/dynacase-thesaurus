<?php
/**
 * Freedom document manipulation Soap library
 *
 * @author Anakeen 2006
 * @version $Id: Lib.FreedomWSDoc.php,v 1.5 2006/10/18 10:15:49 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM-WEBSERVICES
 */
/**
 */
include_once('WHAT/Class.User.php');
include_once("FDL/Lib.Dir.php");
include_once("FDL/Class.Doc.php");


$dbaccess = "user=anakeen dbname=freedom";

/**
 * Read doc content : properties and attributes
 * @param string $docid
 * @param string $docrev 
 * @return docContent $doc
 */
function  docRead($docid="", $docrev="") {
  global $dbaccess;
  global $action;

  $uid = _getUserFid();

  $doc = new_Doc($dbaccess, $docid);
  if (isset($doc) && $doc->isAlive()) {
    if ($docrev!="") {
      $ldoc = $doc->GetRevisions("TABLE");
      foreach($ldoc as $k => $cdoc) {
	if ($docrev==$cdoc["revision"]) {
	  $ndoc = getDocObject($dbaccess, $cdoc);
	  continue;
	}
      }
    } else {
      $ndoc = &$doc;
    }
    if ($ndoc!==false) {
      $tdo = getTDoc($dbaccess, $ndoc->id);
      $docc = _docObject2docContent($tdo);
    }
  }
  return $docc;
}


/**
 * Search for documents according filters
 * @param tQuery  $query array of ( attr | value )
 * @param integer $start [default = 0]
 * @param integer $slice or 0 for all documents [default = 0]
 * @param string  $famid "" or family identifier (logical name or number) [default = 0]
 * @param string  $state "" state filter [default = ""]
 * @param boolean $allrev set to true to retrieve all revision [default = false]
 * @param boolean $trash  set to true to search in trash [default = false]
 * @param string  $orderby order by attribute [default order by title]
 * @return docList
 */
function docQuery($query=array(), $start=0, $slice=0, $famid="", $state="", $allrev=false, $trash=false, $orderby="title" ) {
  global $dbaccess;
 
  $docs = array();

   $uid = _getUserFid();

   $filter = array();
   if (count($query) > 0) {
     $sl = "";
     foreach ($query as $kq => $vq) {
       $sl .= ($sl==""?"":" AND ") . "(".$vq.")";
     }
     $filter[] = $sl;
   }
   if ($state!="") $filter[] = "state = '".$state."'";
   $tdocs = getChildDoc($dbaccess, 0, $start, ($slice==0?"ALL":$slice),  
			$filter, $uid, "TABLE", 
			$famid, $allrev, $orderby, true, $trash);
   if (count($tdocs)<2) $docs["doc"][] = array(); 
   foreach ($tdocs as $k => $v) $docs["doc"][] = _docObject2docContent($v);
   return  $docs;
}

/**
 * Returns doc history
 * @param string $docid
 * @return docHisto 
 */
function  docGetHistory($docid="", $allrev=false) {
  global $dbaccess;

  $rel = array("release" => array());
  $doc = new_Doc($dbaccess, $docid);
  if (isset($doc) && $doc->isAlive())  $td = $doc->getHisto($allrev);
  foreach ($td as $k => $v) $rel["release"][] = array( "releaseid" => $v['id'],
						       "date" => $v['date'],
						       "who" => $v['uname'],
						       "level" => $v['level'],
						       "code" => $v['code'],
						       "comment" => $v['comment']
						       );
   return $rel;
}

/**
 * Returns Workflow for document : full workflow description and following states
 * @param string $docid
 * @return docWorkflow 
 */
function  docGetWorkflow($docid="") {
  global $dbaccess;

  $workflow = array();
  $doc = new_Doc($dbaccess, $docid);
  if (isset($doc) && $doc->isAlive()) $wkf = $doc->wid;
  if ($wkf!="" && is_numeric($wkf)) {
    $wdoc = new_Doc($dbaccess, $wkf);
    if (isset($wdoc) && $wdoc->isAlive()) {
      $twst = $wdoc->getStates();
      $wst = array();
      foreach ($twst as $k => $w) {
	$wst[] = array( "key" => $k, "value" => $w);
      }
      $fstates = $wdoc->GetFollowingStates();
      $fst = array();      
      foreach ($fstates as $ks => $vs) {
	$slab = (isset($twst[$vs]) ? $twst[$vs] : $vs);
	$fst[] = array( "key" => $vs, "value" => $slab);
      }
      $workflow = array( "descr" => $wdoc->getTitle(), 
			 "docid" => $wdoc->id,
			 "nextStates" => $fst,
			 "allStates" => $wst 
			 );
    }
  }
  return $workflow;
}


/* 
 * --------------------------------------------------------------------------------------------
 * Private function
 * --------------------------------------------------------------------------------------------
 */
function docAPIVersion() {
  @include_once("Lib.Install.php");
  return $version."-".$release;
}

function _getUserFid() {
  global $_SERVER;
  global $action;
  $action->user = new User(); //create user as admin  
  $action->user->setLoginName($_SERVER["PHP_AUTH_USER"]);
  return $action->user->id;
}


function _docObject2docContent($ndoc) {
  foreach ($ndoc as $k => $v)     $rattr[] = array( "key" => $k, "value" => $v );
  return array( "prop" => $propr, "attr" => $rattr) ;
}
?>
