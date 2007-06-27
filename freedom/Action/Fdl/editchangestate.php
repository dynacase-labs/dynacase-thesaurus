<?php
/**
 * Display interface to change state
 *
 * @author Anakeen 2007
 * @version $Id: editchangestate.php,v 1.3 2007/06/27 10:04:29 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Lib.Dir.php");
include_once("FDL/editutil.php");
include_once("FDL/editcard.php");


/**
 * Display editor to fix a document version
 * @param Action &$action current action
 * @global id Http var : document id 
 * @global nstate Http var : next state id
 */
function editchangestate(&$action) {
  $docid = GetHttpVars("id");
  $nextstate = GetHttpVars("nstate");

  $dbaccess = $action->GetParam("FREEDOM_DB");

  editmode($action);
  $doc=new_doc($dbaccess,$docid);
  if (!$doc->isAlive()) $action->exitError(sprintf(_("Document %s is not alive"),$docid));
  if ($doc->wid > 0) {

    $err = $doc->lock(true); // autolock
    if ($err=="") $action->AddActionDone("LOCKFILE",$doc->id);
  
    $wdoc = new_Doc($dbaccess,$doc->wid);
    $wdoc->Set($doc);
  
    $fstate = $wdoc->GetFollowingStates();  
    foreach ($fstate as $k=>$v) {
      if ($v == $nextstate) {
	$tr=$wdoc->getTransition($doc->state,$v);
	if (is_array($tr["ask"])) {
	  foreach ($tr["ask"] as $ka=>$va) {
	    $oa = $wdoc->getAttribute($va);
	    if ($oa) {
	      $tinputs[]=array("alabel"=>$oa->labelText,
			       "labelclass"=>($oa->needed)?"FREEDOMLabelNeeded":"FREEDOMLabel",
			       "avalue"=>getHtmlInput($wdoc,$oa,"","","",true),
			       "aid"=>$oa->id,
			       "idisplay"=>($oa->visibility=="H")?"none":"");
	      if ($oa->needed) $tneed[$oa->id]=$oa->labelText;
	    }
	  }
	}
	$action->lay->setBlockData("FINPUTS",$tinputs);
      }
    }

    setNeededAttributes($action,$wdoc);

    $action->lay->set("tonewstate",sprintf(_("to the %s state"),_($nextstate)));
    if ( _("To".$nextstate) == "To".$nextstate) $lnextstate=sprintf(_("to %s"),_($nextstate));
    else $lnextstate=_("To".$nextstate);
    $action->lay->set("tostate",ucfirst($lnextstate));
    $action->lay->set("wcolor",	$wdoc->getColor($nextstate));
		      
    $action->lay->set("docid",$doc->id);
    $action->lay->set("thetitle",sprintf(_("Change state to %s"),_($nextstate)));
    $action->lay->set("nextstate",$nextstate);

  }
}