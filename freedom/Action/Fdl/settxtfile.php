<?php
/**
 * Update file text which comes from transformation engine
 *
 * @author Anakeen 2007
 * @version $Id: settxtfile.php,v 1.7 2007/06/14 15:50:46 eric Exp $
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
  $index = GetHttpVars("index",-1);
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
	  
	  $doc = new_Doc($dbaccess, $docid);
	  if (! $doc->isAffected()) $err=sprintf(_("cannot see unknow reference %s"),$docid);
	  if ($err=="") {
	    $filename= uniqid("/var/tmp/txt-".$doc->id.'-');
	    //$err=$ot->getTransformation($tid,$filename);
	    $err=$ot->getAndLeaveTransformation($tid,$filename);	    
	    if ($err=="") {
	      $at=$attrid.'_txt';
	      if (file_exists($filename) && $info['status']=='D') {
		if ($index == -1) {
		  $doc->$at=file_get_contents($filename);
		} else {		  
		  if ($doc->AffectColumn(array($at))) {
		    $doc->$at=sep_replace($doc->$at,$index,str_replace("\n"," ",file_get_contents($filename)));
		    
		  }
		  
		}
		$av=$attrid.'_vec';
		$doc->fields[$av]=$av;
		$doc->$av='';

		$doc->fulltext='';
		$doc->fields[$at]=$at;
		$doc->fields['fulltext']='fulltext';
		$err=$doc->modify(true,array('fulltext',$at,$av),true);
		$doc->AddComment(_("update file to text conversion done"),HISTO_NOTICE);
		if (($err=="") && ($doc->locked == -1)) {
		  // propagation in case of auto revision
		  $idl=$doc->latestId();
		  $ldoc=new_Doc($dbaccess, $idl);
		  if ($doc->getValue($attrid) == $ldoc->getValue($attrid)) {
		    $ldoc->$at=$doc->$at;
		    $ldoc->fulltext='';
		    $ldoc->fields[$at]=$at;
		    $ldoc->fields['fulltext']='fulltext';
		    $err=$ldoc->modify(true,array('fulltext',$at),true);		    
		  }
		  
		}

	      } else {
		$err=sprintf(_("output file [%s] not found"),$filename);
	      }
	      @unlink($filename);
	    }
	    $doc->AddComment(_("conversion failed : ").$err,HISTO_NOTICE);
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

  if ($err != '')     $action->lay->template=$err;
  else $action->lay->template="OK : ".sprintf(_("doc %d indexed"),$docid);

}


?>