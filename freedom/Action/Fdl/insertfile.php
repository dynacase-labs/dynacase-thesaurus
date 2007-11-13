<?php
/**
 * Insert rendering file which comes from transformation engine
 *
 * @author Anakeen 2007
 * @version $Id: insertfile.php,v 1.1 2007/11/13 16:37:08 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("FDL/Class.TaskRequest.php");
include_once("TE/Class.TEClient.php");
/**
 * Modify the attrid_txt attribute
 * @param Action &$action current action
 * @global docid Http var : document identificator to modify
 * @global attrid Http var : the id of attribute to modify
 * @global index Http var : the range in case of array
 * @global tid Http var : task identificator
 * 
 */
function insertfile(&$action) {
  $vidin=GetHttpVars("vidin");
  $vidout=GetHttpVars("vidout");
  $tid = GetHttpVars("tid");
  $dbaccess = $action->GetParam("FREEDOM_DB");

  if (! $tid) $$err=_("no task identificator found");
  else {
    $ot=new TransformationEngine($action->getParam("TE_HOST"),$action->getParam("TE_PORT"));

    $err=$ot->getInfo($tid,$info);
    if ($err=="") {
      $tr=new TaskRequest($dbaccess,$tid);
      if ($tr->isAffected()) {

	
	$outfile=$info["outfile"];
	$status=$info["status"];
	if (($status=='D') && ($outfile != '')) {
	  $filename= uniqid("/var/tmp/txt-".$vidout.'-');
	  //$err=$ot->getTransformation($tid,$filename);
	  $err=$ot->getAndLeaveTransformation($tid,$filename);

	  $vf = newFreeVaultFile($dbaccess);
	  $err=$vf->Retrieve($vidout, $info);
	  $err=$vf->Save($filename, false , $vidout);
	
	  if (substr($info->name,0,3)=="---") {
	    $basename=substr($info->name,3);
	    $vf->Rename($vidout,$basename);
	  }
	  
	} else {
	  $vf = newFreeVaultFile($dbaccess);
	  $err=$vf->Retrieve($vidout, $info);
	  if (substr($info->name,0,3)=="---") {
	    $basename="###".substr($info->name,3);
	    $vf->Rename($vidout,$basename);
	  }
	}
	
      } else {
	$err=sprintf(_("task %s is not recorded"),$tid);
      }

    }
  }

  if ($err != '')     $action->lay->template=$err;
  else $action->lay->template="OK : ".sprintf(_("vid %d stored"),$vidout);

}


?>