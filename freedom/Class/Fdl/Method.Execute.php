<?php


  /**
   * execute the action describe in the object
   */
function bgExecute() {
  $cmd= getWshCmd(true);
  $cmd.= " --api=fdl_execute";
  $cmd.= " --docid=".$this->id;
  
  $cmd.= " --userid=".$this->user->id;
  
  system($cmd,$status);
  if ($status==0) AddWarningMsg(sprintf(_("Process %s [%d] executed"),$this->title,$this->id));
  else AddWarningMsg(sprintf(_("Error : Process %s [%d]: status %d"),$this->title,$this->id,$status));
				
  
}


  /**
   * return the wsh command which be send
   */
function bgCommand() {
  $bgapp=$this->getValue("exec_application");
  $bgact=$this->getValue("exec_action");

  $tp= $this->getAValues("exec_t_parameters");
  
  $cmd =  getWshCmd(true);
  $fuid=$this->getValue("exec_iduser");
  $fu=getTDoc($this->dbaccess,$fuid);
  $wuid=$fu["us_whatid"];
  $cmd.= " --userid=$wuid";
  $cmd.= " --app=$bgapp --action=$bgact";

  foreach ($tp as $k=>$v) {
    $b=sprintf(" --%s=\"%s\"",$v["exec_idvar"],str_replace("\"","'",$v["exec_valuevar"]));
    $cmd.=$b;
  }
  return $cmd;
  
}

?>