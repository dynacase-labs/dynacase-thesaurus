<?php
/**
 * Form to edit or create a document
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_edit.php,v 1.26 2004/10/13 09:45:02 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

include_once("FDL/Class.WDoc.php");

include_once("Class.QueryDb.php");
include_once("FDL/freedom_util.php");
include_once("FDL/Lib.Dir.php");
include_once("VAULT/Class.VaultFile.php");

/**
 * Edit or create a document
 * @param Action &$action current action
 * @global id Http var : document identificator to édit (empty means create)
 * @global classid Http var : family identificator use for create
 * @global dirid Http var : folder identificator to add when create
 * @global usefor Http var : set to  "D" for edit default values
 * @global onlysubfam Http var : to show in family list only sub family of classid
 */
function freedom_edit(&$action) {
  // -----------------------------------

  // Get All Parameters
  $docid = GetHttpVars("id",0);        // document to edit
  $classid = GetHttpVars("classid",0); // use when new doc or change class
  $dirid = GetHttpVars("dirid",0); // directory to place doc if new doc
  $usefor = GetHttpVars("usefor"); // default values for a document
  $onlysubfam = GetHttpVars("onlysubfam"); // restricy to sub fam of


  // Set the globals elements
  $dbaccess = $action->GetParam("FREEDOM_DB");
  if (! is_numeric($classid))  $classid = getFamIdFromName($dbaccess,$classid);
   
  if ($docid > 0) {
    $doc= new Doc($dbaccess,$docid);
    if (! $doc->isAlive()) $action->exitError(sprintf(_("document id %d not found"),$docid));
    $cdoc =  $doc->getFamDoc();
    $tclassdoc[$doc->fromid] = array("id"=> $cdoc->id,
				     "title"=>$cdoc->title);
  } else {
    // new document select special classes
    if ($dirid > 0) {
      $dir = new Doc($dbaccess, $dirid);
      if (method_exists($dir,"isAuthorized")) {

	
	if ($dir->isAuthorized($classid)) { 
	  // verify if classid is possible
	  if ($dir->norestrict) $tclassdoc=GetClassesDoc($dbaccess, $action->user->id,$classid,"TABLE");
	  else $tclassdoc=$dir->getAuthorizedFamilies();
	} else  {
	  $tclassdoc=$dir->getAuthorizedFamilies();
	  $first = current($tclassdoc);
	  $classid = $first["id"];
	  setHttpVar("classid",$classid); // propagate to subzones
	}
      }
      else {
	$tclassdoc = GetClassesDoc($dbaccess, $action->user->id,$classid,"TABLE");
      }
    } else {

      if ($onlysubfam) {
	
	if (! is_numeric($onlysubfam))  $onlysubfam = getFamIdFromName($dbaccess,$onlysubfam);
	$cdoc = new Doc($dbaccess,$onlysubfam);
	$tclassdoc = $cdoc->GetChildFam();
	$first = current($tclassdoc);
	if ($classid=="") $classid = $first["id"];
	setHttpVar("classid",$classid); // propagate to subzones
      } else    $tclassdoc = GetClassesDoc($dbaccess, $action->user->id,$classid,"TABLE");
    }

  }

  // when modification 
  if (($classid == 0) && ($docid != 0) ) $classid=$doc->fromid;

  
    

  // build list of class document

  $selectclass=array();
  
 
  while (list($k,$cdoc)= each ($tclassdoc)) {
    $selectclass[$k]["idcdoc"]=$cdoc["id"];
    $selectclass[$k]["classname"]=$cdoc["title"];
    $selectclass[$k]["selected"]="";
  }

  // add no inherit for class document
  if (($docid > 0) && ($doc->doctype=="C")) {
      $selectclass[$k+1]["idcdoc"]="0";
      $selectclass[$k+1]["classname"]=_("no document type");
  }

  if ($docid == 0)
    {
      switch ($classid) {
	case 2:
	  $action->lay->Set("TITLE", _("new directory"));
	  $action->lay->Set("refreshfld", "yes");
	break;
	case 3:	  
	case 4:	  
	  $action->lay->Set("TITLE", _("new profile"));
	break;
      default:
	$action->lay->Set("TITLE", _("new document"));
      }
      if ($usefor=="D") $action->lay->Set("TITLE", _("default values"));
      $action->lay->Set("editaction", $action->text("create"));
      if ($classid > 0) {
	$doc=createDoc($dbaccess,$classid); // the doc inherit from chosen class
	if ($doc === false) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document"),$classid));
      }
      // selected the current class document
      while (list($k,$cdoc)= each ($selectclass)) {	
	if ($classid == $selectclass[$k]["idcdoc"]) {	  
	  $selectclass[$k]["selected"]="selected";
	}
      }
    }
  else
    {     
      if (! $doc->isAlive()) $action->ExitError(_("document not referenced"));

      $err = $doc->lock(true); // autolock
      if ($err != "")   $action->ExitError($err);
  

      $action->lay->Set("TITLE", $doc->title);
      $action->lay->Set("editaction", $action->text("Validate"));
      
      // selected the current class document
      while (list($k,$cdoc)= each ($selectclass)) {	
	if ($doc->fromid == $selectclass[$k]["idcdoc"]) {	  
	  $selectclass[$k]["selected"]="selected";
	}
      }
    }
    
  $action->lay->Set("iconsrc", $doc->geticon());
 
  // compute the changed state
  $tstate= array();
  if ($doc->wid > 0) {
    $wdoc = new Doc($dbaccess,$doc->wid);
    $wdoc->Set($doc);
    $fstate = $wdoc->GetFollowingStates();
    $action->lay->Set("initstatevalue",$doc->state );
    while (list($k, $v) = each($fstate)) {
      $tstate[$k]["statevalue"] = $v;
      $tstate[$k]["statename"] = _($v);
    }
  }
  $action->lay->SetBlockData("NEWSTATE", $tstate);

  

  $action->lay->Set("id", $docid);
  $action->lay->Set("dirid", $dirid);
  $action->lay->Set("onlysubfam", $onlysubfam);
  if ($docid > 0) $action->lay->Set("doctype", $doc->doctype);


  // sort by classname
  uasort($selectclass, "cmpselect");
  $action->lay->SetBlockData("SELECTCLASS", $selectclass);

  // control view of special constraint button
  $action->lay->Set("boverdisplay", "none");
  
  if (GetHttpVars("viewconstraint")=="Y") {
    $action->lay->Set("bconsdisplay", "none");
    if ($action->user->id==1) $action->lay->Set("boverdisplay", ""); // only admin can do this
    
  } else {
    // verify if at least on attribute constraint
    
    $action->lay->Set("bconsdisplay", "none");
    $listattr = $doc->GetNormalAttributes();
    foreach ($listattr as $k => $v) {
      if ($v->phpconstraint != "")  {
	$action->lay->Set("bconsdisplay", "");
	break;
      }
    }
  }
 
    

}
function cmpselect ($a, $b) {
  return strcasecmp($a["classname"], $b["classname"]);
}


?>
