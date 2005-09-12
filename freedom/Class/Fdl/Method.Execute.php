<?php
/**
 * Method for processes family
 *
 * @author Anakeen 2005
 * @version $Id: Method.Execute.php,v 1.5 2005/09/12 16:33:55 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

private $execuserid;
  /**
   * execute the action describe in the object
   * @return int shell status (0 means OK).
   */
function bgExecute($comment="") {
  $cmd= getWshCmd(true);
  $cmd.= " --api=fdl_execute";
  $cmd.= " --docid=".$this->id;
  
  $cmd.= " --userid=".$this->userid;
  if ($comment != "") $cmd.= " --comment=".base64_encode($comment); // prevent hack
 
  system($cmd,$status);
  if ($status==0) AddWarningMsg(sprintf(_("Process %s [%d] executed"),$this->title,$this->id));
  else AddWarningMsg(sprintf(_("Error : Process %s [%d]: status %d"),$this->title,$this->id,$status));
  return $status;
  
}


  /**
   * return the wsh command which be send
   */
function bgCommand($masteruserid=false) {
  $bgapp=$this->getValue("exec_application");
  $bgact=$this->getValue("exec_action");

  $tp= $this->getAValues("exec_t_parameters");
  
  $cmd =  getWshCmd(true);
  if ($masteruserid) {
    $fuid=$this->getValue("exec_iduser");
    $fu=getTDoc($this->dbaccess,$fuid);
    $wuid=$fu["us_whatid"];
    $this->execuserid=$fuid;
  } else {
    $wuid=$this->userid;
    $this->execuserid=$this->getUserId();
  }
  $cmd.= " --userid=$wuid";
  $cmd.= " --app=$bgapp --action=$bgact";
  
  foreach ($tp as $k=>$v) {
    $b=sprintf(" --%s=\"%s\"",$v["exec_idvar"],str_replace("\"","'",$v["exec_valuevar"]));
    $cmd.=$b;
  }
  return $cmd;
  
}

/**
 * return the document user id for the next execution
 * @return string
 */
function getExecUserID() {
  return $this->execuserid;
}
function getNextExecDate() {
  $ndh=$this->getValue("exec_handnextdate");
  if ($ndh=="") $ndh=" ";

  return $ndh;
}
function getPrevExecDate() {
  if ($this->revision > 0) {
    $pid=$this->latestId(true);
    $td=getTDoc($this->dbaccess,$pid);
    $ndh=getv($td,"exec_date");

    return $ndh;
  }  
}

function isLatestExec() {
  if ($this->locked == -1) return MENU_INVISIBLE;
  return  MENU_ACTIVE;
}
?>