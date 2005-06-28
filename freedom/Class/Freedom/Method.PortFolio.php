<?php
/**
 * PortFolio Methods
 *
 * @author Anakeen 2003
 * @version $Id: Method.PortFolio.php,v 1.11 2005/06/28 08:37:46 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */



function PostCreated() {

  
  if ($this->revision > 0) return;
  if (! method_exists($this,"addfile")) return;
  // copy all guide-card from default values
  include_once("FDL/Lib.Dir.php");  

  $err="";

  $ddocid = $this->getValue("PFL_IDDEF");


  if ($ddocid > 0) {
    $ddoc = new_Doc($this->dbaccess,$ddocid);
    $child = getChildDir($this->dbaccess,$this->userid,$ddoc->initid, false,"LIST");


    reset($child);
    while (list($k,$doc) = each($child)) {
      //if ($doc->usefor == "G") {
	$doc->getMoreValues();
	$copy=$doc->Copy();
	if (! is_object($copy)) return $copy;

	$err.=$this->AddFile($copy->id);

	//      }
    }
  }
  return $err;
}

function postInsertDoc($docid,$multiple=false) { 
  $doc = new_Doc($this->dbaccess,$docid);
  if ($doc->doctype == "S") {    
	$doc->setValue("SE_IDCFLD",$this->initid);
	$doc->refresh();
	$doc->modify();
  }
}  

/**
   * return document includes in portfolio an in each of its guide or searched inside portfolio
   * @param bool $controlview if false all document are returned else only visible for current user  document are return
   * @param array $filter to add list sql filter for selected document
   * @param bool $insertguide if true merge each content of guide else same as a normal folder
   * @return array array of document array
   */
function getContent($controlview=true,$filter=array(),$insertguide=false) {
  $tdoc=Dir::getContent($controlview,$filter);
  if ($insertguide) {
    $todoc=array();
    foreach ($tdoc as $k=>$v) {
      if (($v["doctype"] == "D")||($v["doctype"] == "S")) {
	$dir=new_Doc($this->dbaccess,$v["id"]);
	$todoc=array_merge($todoc,$dir->getContent($controlview,$filter));
	unset($tdoc[$k]);
      }
    }
    if (count($todoc)) {
      // array unique
      $todoc=array_merge($tdoc,$todoc);
      $tdoc=array();
      foreach ($todoc as $k=>$v) {
	$tdoc[$v["id"]]=$v;
      }      
    }

  }
  return $tdoc;
    
}
?>