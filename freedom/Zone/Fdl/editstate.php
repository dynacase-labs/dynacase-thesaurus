<?php
/**
 * State document edition
 *
 * @author Anakeen 2000 
 * @version $Id: editstate.php,v 1.13 2004/10/19 16:09:00 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

include_once("FDL/Class.WDoc.php");

include_once("Class.QueryDb.php");
include_once("FDL/freedom_util.php");
include_once("FDL/editutil.php");
include_once("FDL/editcard.php");

// -----------------------------------
function editstate(&$action) {
  //print "<HR>EDITCARD<HR>";
  // Get All Parameters
  $docid = GetHttpVars("id",0);        // document to edit
  $classid = GetHttpVars("classid",0); // use when new doc or change class
  $dirid = GetHttpVars("dirid",0); // directory to place doc if new doc
  $usefor = GetHttpVars("usefor"); // special uses
  $wneed = (GetHttpVars("wneed","N")=="Y"); // with needed js
	  
	  
  editmode($action);
  $dbaccess = $action->GetParam("FREEDOM_DB");  
  if (! is_numeric($classid))  $classid = getFamIdFromName($dbaccess,$classid);
  // ------------------------------------------------------
  //  new or modify ?
  if ($docid == 0)    {		
    if ($classid > 0) {
      $doc=createDoc($dbaccess,$classid); // the doc inherit from chosen class
      if (! $doc) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document"),$classid));
      $doc->id=$classid;
    }
	
  }  else    {      
	
	
    // when modification 
    $doc= new Doc($dbaccess,$docid);
	
  }
  $action->lay->set("tstates","");
  $action->lay->set("ttransid","");
  $action->lay->set("askes","");
  $action->lay->Set("Wattrnid","");
  $action->lay->Set("Wattrntitle","");
  if (($usefor!="D")&&($usefor!="Q")) {
  
    if ($doc->wid > 0) {
      // compute the changed state
      $wdoc = new Doc($dbaccess,$doc->wid);
      $wdoc->Set($doc);

      $fstate = $wdoc->GetFollowingStates();
      $tjsstate=array();
      $tjstransid=array();
      $tjsaskes=array();
      $action->lay->Set("initstatevalue",$doc->state );
      $action->lay->Set("initstatename", $action->text($doc->state) );
      $tstate= array();
      $taskes=array();
      if (isset($wdoc->autonext[$doc->state])) $dstate=$wdoc->autonext[$doc->state];
      $action->lay->Set("dstate",""); // default state
      foreach ($fstate as $k=>$v) {
	$tr=$wdoc->getTransition($doc->state,$v);
	$tk=$tr["id"];
	$tstate[$k]["statevalue"] = $v;
	if ($v==$dstate) {
	  $tstate[$k]["checked"]="selected";
	  $action->lay->Set("dstate",$dstate);
	  $tstate[$k]["dsubmit"]="dsubmit";
	} else {
	  $tstate[$k]["checked"]="";
	  $tstate[$k]["dsubmit"]="state";
	}

	$tstate[$k]["statename"] = _($v);
	$tstate[$k]["tostatename"] =ucfirst( _("To".$v));
	$tstate[$k]["transid"] = $tk;
	if (is_array($tr["ask"]))  $tjsaskes[] = "['".implode("','",$tr["ask"])."']";
	else $tjsaskes[] = "[]";
	$taskes= array_merge($taskes,$tr["ask"]);
	$tjsstate[]=$v;
	$tjstransid[]=$tk;
      }
      $action->lay->set("tstates","'".implode("','",$tjsstate)."'");
      $action->lay->set("ttransid","'".implode("','",$tjstransid)."'");
      $action->lay->set("askes","".strtolower(implode(",",$tjsaskes))."");
      $action->lay->SetBlockData("NEWSTATE", $tstate);
      if ($wdoc->viewlist=="button")$action->lay->SetBlockData("BUTTONSTATE", array(0=>array("boo")));
      else $action->lay->SetBlockData("LISTSTATE", array(0=>array("boo")));
      $task=array();
      $tneed=array();
      $tinputs=array();
      $taskes=array_unique($taskes);
      foreach ($taskes as $ka=>$va) {
	$oa = $wdoc->getAttribute($va);
	if ($oa) {
	  $tinputs[]=array("alabel"=>$oa->labelText,
			   "labelclass"=>($oa->needed)?"FREEDOMLabelNeeded":"FREEDOMLabel",
			   "avalue"=>getHtmlInput($wdoc,$oa,""),
			   "aid"=>$oa->id,
			   "idisplay"=>($oa->visibility=="H")?"none":"");
	  if ($oa->needed) $tneed[$oa->id]=$oa->labelText;
	}
      }
      $action->lay->SetBlockData("FINPUTS",$tinputs);
      $action->lay->Set("Wattrntitle",	 "'".implode("','",$tneed)."'");
      $action->lay->Set("Wattrnid",	 "'".implode("','",array_keys($tneed))."'");
      
    } else {	
      $fdoc = $doc->getFamDoc();

      if ($fdoc->schar == "R") {
	$action->lay->SetBlockData("COMMENT", array(0=>array("boo")));
      }
    
    }
    if ($wneed) setNeededAttributes($action,$doc);
  }
        
}
?>
