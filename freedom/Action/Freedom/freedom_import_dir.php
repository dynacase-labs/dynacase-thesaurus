<?php
/**
 * Import directory with document descriptions
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_import_dir.php,v 1.1 2004/03/16 14:12:46 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */


include_once("FDL/import_tar.php");






function freedom_import_dir(&$action) {

  $to = GetHttpVars("to"); 
  $filename = GetHttpVars("filename"); 
  
  $wsh = "nice -n +10 ";
  $wsh .= $action->GetParam("CORE_PUBDIR")."/wsh.php";

  global $_GET,$_POST;
  $targs=array_merge($_GET,$_POST);
  $args="";
  foreach ($targs  as $k=>$v) {
    if (($k != "action") && ($k != "app"))
      $args .= " --$k=\"$v\"";
  }
 
  $rfile=uniqid("/tmp/bgtar");

  $cmd[] = "$wsh --userid={$action->user->id} --app=FREEDOM --action=FREEDOM_ANA_TAR --htmlmode=Y $args >$rfile ";

  
  $subject=sprintf(_("result of archive import  %s"), $filename);
  $from=getMailAddr($action->user->id);
  if ($from == "")  $from = $action->user->login;
  $bcc ="";
  
  $bcc .="\\nReturn-Path:$from";
  $cmd[] = "export LANG=C";
  $cmd[] = "metasend  -b -S 4000000  -F \"freedom\" -t \"$to$bcc\" -s \"$subject\"  -m \"text/html\" -e \"quoted-printable\" -f  $rfile";
  // $cmd[]="/bin/rm -f $file.?";

  $scmd="(";
  $scmd.=implode(";",$cmd);
  
  //  $scmd .= ")";
  
  $scmd .= ") 2>&1 > /dev/null &";


  bgexec($cmd, $result, $err);  
 


  if ($err == 0) 
    $action->lay->set("text", sprintf(_("Import %s is in progress. When update will be finished an email to &lt;%s&gt; will be sended with result rapport"), $filename , $to));
  else
    $action->lay->set("text", sprintf(_("update of %s catalogue has failed,"), $filename ));
		      

  
}


/**
 * exec list of unix command in background
 * @param array $tcmd unix command strings
 */
function bgexec($tcmd,&$result,&$err) {
  $foutname = uniqid("/tmp/bgexec");
  $fout = fopen($foutname,"w+");
  fwrite($fout,"#!/bin/bash\n");
  foreach ($tcmd as $v) {
    fwrite($fout,"$v\n");
  }
  fclose($fout);
  chmod($foutname,0700);


  //  if (session_id()) session_write_close(); // necessary to close if not background cmd 
  exec("exec nohup $foutname > /dev/null 2>&1 &",$result,$err); 
  //if (session_id()) @session_start();
}

?>
