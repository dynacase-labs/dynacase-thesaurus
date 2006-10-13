<?php
/**
 * Freedom document manipulation Soap library
 *
 * @author Anakeen 2006
 * @version $Id: Lib.FreedomWSDoc.php,v 1.1 2006/10/13 14:49:09 marc Exp $
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

  fwsLog("docRead($docid, $docrev)", "I", __FILE__, __LINE__);

  $docattr = array();
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
    $propr = array();
    $rattr = array();
    if ($ndoc!==false) {
      $docc = _doc2docContent($ndoc);
    }
  }
  return $docc;
}


/**
 * Search for documents according filters
 * @param integer $start [default = 0]
 * @param integer $slice or 0 for all documents [default = 0]
 * @param string  $famid "" or family identifier (logical name or number) [default = 0]
 * @param boolean $allrev set to true to retrieve all revision [default = false]
 * @param boolean $trash  set to true to search in trash [default = false]
 * @param string  $orderby order by attribute [default order by title]
 * @return docList
 */
function docQuery($start=0, $slice=0, $famid="", $allrev=false, $trash=false, $orderby="title" ) {
  global $dbaccess;
 
  $docs = array();

   $uid = _getUserFid();
   $tdocs = getChildDoc($dbaccess, 0, $start, ($slice==0?"ALL":$slice),  
			array(), $uid, "TABLE", 
			$famid, $allrev, $orderby, true, $trash);
  foreach ($tdocs as $k => $v) {
    $doc = getDocObject($dbaccess, $v);
    $docs["doc"][] = _doc2docContent($doc);
  }
   return  $docs;
}

/**
 * Read doc content : properties and attributes
 * @param string $docid
 * @return docHisto 
 */
function  docGetHistory($docid="") {
  global $dbaccess;

  $dochisto = array();
  $doc = new_Doc($dbaccess, $docid);

  if (isset($doc) && $doc->isAlive()) {
    $td = $doc->getHisto();
  }
    
  foreach ($td as $k => $v) {
    $dochisto["histo"][] = array( 'releaseid' => $v['id'],
			 'date' => $v['date'],
			 'who' => $v['uname'],
			 'level' => $v['level'],
			 'code' => $v['code'],
			 'comment' => $v['comment']
			 );
  }
//    print_r2($dochisto);
  
  return $dochisto;
}


/* Private function
 */
function _getUserFid() {
  global $_SERVER;
  $user = new User(); //create user as admin  
  $user->setLoginName($_SERVER["PHP_AUTH_USER"]);
  return $user->id;
}


function _doc2docContent($ndoc) {
  $docattr = $ndoc->GetValues();
  foreach ($docattr as $k => $v) {
    $rattr[] = array( "key" => $k, "value" => $v );
  }
  foreach ($ndoc->fields as $k => $v) {
    if ($k!=$v) $propr[] = array( "key" => $v, "value" => $ndoc->$v );
  }
  return array( "prop" => $propr, "attr" => $rattr) ;
}
?>
