<?php
/**
 * PortFolio Methods
 *
 * @author Anakeen 2003
 * @version $Id: Method.PortFolio.php,v 1.14 2007/05/16 15:46:11 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */


  /**
   * Call to create default tabs
   */
function PostCreated() {  
  if ($this->revision > 0) return;
  if (! method_exists($this,"addfile")) return;
  // copy all guide-card from default values
  return $this->CreateDefaultTabs();
}



function ReCreateDefaultTabs() {  
  include_once("FDL/Lib.Dir.php");  
  $child = getChildDir($this->dbaccess,1,$this->initid, false,"TABLE");
  if (count($child) == 0) {
    $err=$this->CreateDefaultTabs();
  }
  return $err;
}
/**
 * Create default tabs based on tabs of PFL_IDDEF document
 * @return string message error (empty if no error)
 */
function CreateDefaultTabs() {

  $err="";

  $ddocid = $this->getValue("PFL_IDDEF");

  if ($ddocid != "") {
    $ddoc = new_Doc($this->dbaccess,$ddocid);
    if ($ddoc->isAffected()) {
      $child = getChildDir($this->dbaccess,$this->userid,$ddoc->initid, false,"TABLE");
      
      foreach($child as $k=>$tdoc) {
	$doc=getDocObject($this->dbaccess,$tdoc);
	$copy=$doc->Copy();
	if (! is_object($copy)) return $copy;
	
	$err.=$this->AddFile($copy->id);
      }
    } else {
      $err=sprintf(_("Error in portfolio : folder %s not exists"),$ddocid);
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
   * @param int $famid family identificator to restrict search 
   * @param bool $insertguide if true merge each content of guide else same as a normal folder
   * @return array array of document array
   */
function getContent($controlview=true,$filter=array(),$famid="",$insertguide=false) {
  $tdoc=Dir::getContent($controlview,$filter,$famid);
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