<?php
/**
 * Update file text which comes from transformation engine
 *
 * @author Anakeen 2007
 * @version $Id: settxtfile.php,v 1.2 2007/06/01 13:39:31 eric Exp $
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
function settxtfile(&$action) {
  $docid = GetHttpVars("docid");
  $attrid = GetHttpVars("attrid");
  $tid = GetHttpVars("tid");
  $dbaccess = $action->GetParam("FREEDOM_DB");

  if (! $tid) $$err=_("no task identificator found");
  else {
    $ot=new TransformationEngine();
    $err=$ot->getInfo($tid,$info);
    print "<hr>";
    print_r2($info);
    if ($err=="") {
      $tr=new TaskRequest($dbaccess,$tid);
      if ($tr->isAffected()) {

	$outfile=$info["outfile"];
	$status=$info["status"];
	if (($status=='D') && ($outfile != '')) {
	  
	  $doc = new_Doc($dbaccess, $docid);
	  if (! $doc->isAffected()) $err=sprintf(_("cannot see unknow reference %s"),$docid);
	  if ($err=="") {
	    $filename= uniqid("/var/tmp/txt-".$doc->id.'-');
	    $err=$ot->getTransformation($tid,$filename,$info);
	    if ($err=="") {
	      $at=$attrid.'_txt';
	      if (file_exists($filename) && $info['status']=='D') {
		$doc->$at=file_get_contents($filename);
		$doc->fulltext='';
		$doc->fields[$at]=$at;
		$doc->fields['fulltext']='fulltext';
		$err=$doc->modify(false,array('fulltext',$at));
	      } else {
		$err=sprintf(_("output file [%s] not found"),$filename);
	      }
	      @unlink($filename);
	    }
	  } else {
	    $err=sprintf(_("document [%s] not found"),$docid);
	  }
	} else {
	  $err=sprintf(_("task %s is not done correctly"),$tid);
	}
      } else {
	$err=sprintf(_("task %s is not recorded"),$tid);
      }

    }
  }

  if ($err != '') $action->lay->template=$err;

}


?>