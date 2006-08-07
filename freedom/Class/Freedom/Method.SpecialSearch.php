<?php
/**
 * Detailled search
 *
 * @author Anakeen 2000 
 * @version $Id: Method.SpecialSearch.php,v 1.2 2006/08/07 10:11:37 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */




var $defaultedit= "FDL:EDITBODYCARD";
var $defaultview= "FDL:VIEWBODYCARD"; 




/**
  * return sql query to search wanted document
  */
function ComputeQuery($keyword="",$famid=-1,$latest="yes",$sensitive=false,$dirid=-1, $subfolder=true) {
    
  

  return true;
}


function getDocList($start=0, $slice="ALL", $qtype="TABLE",$userid="") {
  $phpfile=$this->getValue("se_phpfile");
  $phpfunc=$this->getValue("se_phpfunc");
  if (! @include_once("EXTERNALS/$phpfile")) {
    global $action;
    $action->AddWarningMsg(sprintf(_("php file %s needed for request not found"),"EXTERNALS/$phpfunc"));
    return false;
  }
  $arg=array($start,$slice,$userid);
  $res = call_user_func_array($phpfunc, $arg);

  return($res);

}

/**
 * return true if the search has parameters
 */
function isParameterizable() {
  return false;
}
/**
 * return true if the search need parameters
 */
function needParameters() {
  return false;
}

function isStaticSql() {
  return true;
}

  /**
   * return document includes in search folder
   * @param bool $controlview if false all document are returned else only visible for current user  document are return
   * @param array $filter to add list sql filter for selected document
   * @param int $famid family identificator to restrict search 
   * @return array array of document array
   */
function getContent($controlview=true,$filter=array(),$famid="") {
  if ($controlview) $uid=1;
  return $this->getDocList(0,"ALL",$uid);
}
/**
   * return number of item in this searches
   * @return int -1 if errors
   */
function count() {
  $t=$this->getContent();
  if (is_array($t)) return count($t);
  return -1;
}
?>