<?php
/**
 * Display edition interface
 *
 * @author Anakeen 2000 
 * @version $Id: generic_edit.php,v 1.35 2005/06/07 13:33:03 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */




include_once("FDL/Class.Doc.php");

include_once("Class.QueryDb.php");
include_once("GENERIC/generic_util.php"); 

// -----------------------------------
function generic_edit(&$action) {
  // -----------------------------------

  // Get All Parameters
  $docid = GetHttpVars("id",0);        // document to edit
  $classid = GetHttpVars("classid",getDefFam($action)); // use when new doc or change class

  $dirid = GetHttpVars("dirid",0); // directory to place doc if new doc
  $usefor = GetHttpVars("usefor"); // default values for a document
  $zonebodycard = GetHttpVars("zone"); // define view action

  $vid = GetHttpVars("vid"); // special controlled view

  $action->lay->Set("vid", $vid);
  // Set the globals elements
  $dbaccess = $action->GetParam("FREEDOM_DB");
   


  if ($docid == 0)
    {
    if ($classid > 0) {
      $cdoc= new Doc($dbaccess,$classid);
      if ($cdoc->control('create') != "") $action->exitError(sprintf(_("no privilege to create this kind (%s) of document"),$cdoc->title));
      if ($cdoc->control('icreate') != "") $action->exitError(sprintf(_("no privilege to create interactivaly this kind (%s) of document"),$cdoc->title));
      $action->lay->Set("TITLE", sprintf(_("creation %s"),$cdoc->title));
    } else {
      $action->lay->Set("TITLE",_("new card"));
    }
    if ($usefor=="D") $action->lay->Set("TITLE", _("default values"));
    if ($usefor=="Q") $action->lay->Set("TITLE", _("parameters values"));
    
      $action->lay->Set("editaction", $action->text("Create"));
      $doc= createDoc($dbaccess,$classid);
      if (! $doc) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document"),$classid));
      if ($usefor!="") $doc->doctype='T';
    }
  else
    {    

      $doc= new Doc ($dbaccess,$docid);
     
      $err = $doc->lock(true); // autolock
      if ($err != "")   $action->ExitError($err);

      $classid = $doc->fromid;
      if (! $doc->isAlive()) $action->ExitError(_("document not referenced"));
  

      $action->lay->Set("TITLE", $doc->title);
      $action->lay->Set("editaction", _("Save"));
    }
    
  $action->lay->Set("STITLE",addslashes($action->lay->get("TITLE"))); 
  if ($zonebodycard == "") {
    if (($vid != "") && ($doc->cvid > 0)) {
      // special controlled view
      $cvdoc= new Doc($dbaccess, $doc->cvid);
      $cvdoc->set($doc);
      $err = $cvdoc->control($vid); // control special view
      if ($err != "") $action->exitError($err);
      $tview = $cvdoc->getView($vid);
      $doc->setMask($tview["CV_MSKID"]);
      if ($zonebodycard == "") $zonebodycard=$tview["CV_ZVIEW"];
    }
  }


  if ($zonebodycard == "") $zonebodycard = $doc->defaultedit;
  $action->lay->Set("HEAD", (! ereg("[A-Z]+:[^:]+:[T|S|U]", $zonebodycard, $reg)));
  $action->lay->Set("FOOT", (! ereg("[A-Z]+:[^:]+:[S|U]", $zonebodycard, $reg)));
  $action->lay->Set("NOFORM", (ereg("[A-Z]+:[^:]+:U", $zonebodycard, $reg)));

  $action->lay->Set("iconsrc", $doc->geticon());
  
  if ($doc->fromid > 0) {
    $fdoc= $doc->getFamDoc();
    $action->lay->Set("FTITLE", $fdoc->title);
  } else {
    $action->lay->Set("FTITLE", _("no family"));
  }
  

  $action->lay->Set("id", $docid);
  $action->lay->Set("dirid", $dirid);

  // control view of special constraint button
  $action->lay->Set("boverdisplay", "none");
  
  if (GetHttpVars("viewconstraint")=="Y") {
    $action->lay->Set("bconsdisplay", "");
    if ($action->user->id==1) {
      $action->lay->SetBlockData("INPUTCONSTRAINT",array(array("zou")));
      $action->lay->Set("boverdisplay", ""); // only admin can do this
    }
    
  } else {
    // verify if at least on attribute constraint
    
    $action->lay->Set("bconsdisplay", "none");
    /*
    $listattr = $doc->GetNormalAttributes();
    foreach ($listattr as $k => $v) {
      if ($v->phpconstraint != "")  {
	$action->lay->Set("bconsdisplay", "");
	break;
      }
    }
    */
  }
  $action->lay->set("tablefoot","tableborder");
  $action->lay->set("tablehead","tableborder");
  $action->lay->set("ddivfoot","none");
  if ($action->Read("navigator","")=="NETSCAPE") {
    if (ereg("rv:([0-9.]+).*",$_SERVER['HTTP_USER_AGENT'],$reg)) {
      if (floatval($reg[1] >= 1.6)) {
	$action->lay->set("ddivfoot","");
	$action->lay->set("tablefoot","tablefoot");
	$action->lay->set("tablehead","tablehead");	
      }
    }
    
  } 
  $taction=array();
  
  $listattr = $doc->GetActionAttributes();
  foreach ($listattr as $k => $v) {
    if ($v["visibility"] != "H") {
      $taction[$k]=array("wadesc"=>$v->labelText,
			 "walabel"=>ucfirst($v->labelText),
			 "waction"=>$v->waction,
			 "wapplication"=>$v->wapplication);
    }
  }

  $action->lay->setBlockData("WACTION",$taction);

  // information propagation
  $action->lay->Set("classid", $classid);
  $action->lay->Set("dirid", $dirid);
  $action->lay->Set("id", $docid);
    

}
?>
