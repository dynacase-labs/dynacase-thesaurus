<?php
/**
 * PortFolio Methods
 *
 * @author Anakeen 2003
 * @version $Id: Method.PortFolio.php,v 1.8 2004/06/17 14:49:34 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */



function PostCreated() {

  
  if ($this->revision > 0) return;
  // copy all guide-card from default values
  include_once("FDL/Lib.Dir.php");  

  $err="";

  $ddocid = $this->getValue("PFL_IDDEF");


  if ($ddocid > 0) {
    $ddoc = new Doc($this->dbaccess,$ddocid);
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
  $doc = new Doc($this->dbaccess,$docid);
  if ($doc->doctype == "S") {    
	$doc->setValue("SE_IDCFLD",$this->initid);
	$doc->refresh();
	$doc->modify();
  }
}
?>