<?php
/**
 * Modify a document
 *
 * @author Anakeen 2000 
 * @version $Id: generic_mod.php,v 1.31 2007/05/10 13:01:26 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */




include_once("FDL/modcard.php");

include_once("FDL/Class.DocFam.php");
include_once("FDL/Class.Dir.php");


// -----------------------------------
function generic_mod(&$action) {
  // -----------------------------------

  // Get all the params      
  $dirid=GetHttpVars("dirid",0);
  $docid=GetHttpVars("id",0); 
  $catgid=GetHttpVars("catgid",0); 
  $retedit=(GetHttpVars("retedit","N")=="Y"); // true  if return need edition
  $noredirect=(GetHttpVars("noredirect")); // true  if return need edition
  $rzone = GetHttpVars("rzone"); // special zone when finish edition

  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  $err = modcard($action, $ndocid); // ndocid change if new doc

  if ($err != "")  $action->AddWarningMsg($err);
  else {   
  
    $doc= new_Doc($dbaccess, $ndocid);
    if ($docid > 0) AddLogMsg(sprintf(_("%s has been modified"),$doc->title));

    if ($docid == 0) { // new file => add in a folder
   
      AddLogMsg(sprintf(_("%s has been created"),$doc->title));
   
      $cdoc = $doc->getFamDoc();


      //if (($cdoc->dfldid>0) && ($dirid==0))  $dirid=$cdoc->dfldid;// we not insert in defaut folder
    

      if ($dirid > 0) {
	$fld = new_Doc($dbaccess, $dirid);
	if (method_exists($fld,"AddFile")) {
	   $err=$fld->AddFile($doc->id); 
	  if ($err != "") {
	    $action->AddLogMsg($err);
	  } else {
	    if (($doc->doctype=='D')|| ($doc->doctype=='S')) $action->AddActionDone("ADDFOLDER",$fld->initid);
	    else $action->AddActionDone("ADDFILE",$fld->initid);
	  }
	}
      }     
    } 
  }
  
  
  if ($ndocid==0) {
    redirect($action,GetHttpVars("redirect_app","GENERIC"),
	     GetHttpVars("redirect_act","GENERIC_LOGO"),
	     $action->GetParam("CORE_STANDURL"));
  }


  if ($noredirect) {
    $action->lay->set("id",$ndocid);
    return;
  }
  if ($retedit) {
    redirect($action,GetHttpVars("redirect_app","GENERIC"),
	     GetHttpVars("redirect_act","GENERIC_EDIT&id=$ndocid"),
	     $action->GetParam("CORE_STANDURL"));
  } else {
  
    if ($rzone != "") $zone="&zone=$rzone";
    else $zone="";
    // $action->register("reload$ndocid","Y"); // to reload cached client file
    redirect($action,GetHttpVars("redirect_app","FDL"),
	     GetHttpVars("redirect_act","FDL_CARD$zone&refreshfld=Y&id=$ndocid"),
	     $action->GetParam("CORE_STANDURL"));
  }
  
}


?>
