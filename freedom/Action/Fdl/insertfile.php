<?php
/**
 * Insert rendering file which comes from transformation engine
 *
 * @author Anakeen 2007
 * @version $Id: insertfile.php,v 1.5 2007/11/26 15:04:52 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("FDL/Class.TaskRequest.php");
include_once("TE/Class.TEClient.php");
include_once("Lib.FileMime.php");
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
  $name = GetHttpVars("name");
  $isimage = (GetHttpVars("isimage")!="");
  $dbaccess = $action->GetParam("FREEDOM_DB");

  if (! $tid) $err=_("no task identificator found");
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
	  $err=$vf->Retrieve($vidin, $infoin);
	  $err=$vf->Retrieve($vidout, $infoout);
	  $err=$vf->Save($filename, false , $vidout);
	  $err=$vf->Retrieve($vidout, $infoout); // relaod for mime
	
	  $ext=getExtension($infoout->mime_s);
	  if ($ext=="") $ext=$infoout->teng_lname;
	  //	  print_r($infoout);
		  // print_r($ext);
	  

	  if ($name!="") {
	    $newname=$name;
	  } else {
	    $pp=strrpos($infoin->name,'.');
	    $newname=substr($infoin->name,0,$pp).'.'.$ext;
	  }


	  $vf->Rename($vidout,$newname);
	  $vf->storage->teng_state=1;
	  $vf->storage->modify();
	  
	  
	} else {
	  $vf = newFreeVaultFile($dbaccess);
	  $err=$vf->Retrieve($vidout, $vinfo);

	  if (substr($vinfo->name,0,3)=="---") {
	    $filename= uniqid("/var/tmp/txt-".$vidout.'-');
	    file_put_contents($filename,print_r($info,true));
	    //$vf->rename($vidout,"toto.txt");
	    $vf->Retrieve($vidout, $vinfo);
	    $err=$vf->Save($filename, false , $vidout);
	    @unlink($filename);
	    $basename=_("conversion error").".txt";
	    $vf->Rename($vidout,$basename);
	    $vf->storage->teng_state=-1;
	    $vf->storage->modify();;
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