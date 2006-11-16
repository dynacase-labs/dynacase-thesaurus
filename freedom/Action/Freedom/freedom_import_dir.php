<?php
/**
 * Import directory with document descriptions
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_import_dir.php,v 1.4 2006/11/16 16:42:05 eric Exp $
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
  
 
  $wsh=getWshCmd(true);

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
  
  $maxsplit=$action->getParam("FDL_SPLITSIZE",4000000);
  $cmd[] = "metasend  -b -S $maxsplit  -F \"freedom\" -t \"$to$bcc\" -s \"$subject\"  -m \"text/html\" -e \"quoted-printable\" -f  $rfile";
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




?>
