<?php
/**
 * Specific menu for family
 *
 * @author Anakeen 2000 
 * @version $Id: popupdocdetail.php,v 1.1 2006/04/21 15:11:50 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/popupdoc.php");
// -----------------------------------
function popupdocdetail(&$action) {
  // -----------------------------------
  // define accessibility
  $docid = GetHttpVars("id");
  $abstract = (GetHttpVars("abstract",'N') == "Y");
  $zone = GetHttpVars("zone"); // special zone

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new_Doc($dbaccess, $docid);

  //  if ($doc->doctype=="C") return; // not for familly



  $tsubmenu=array();

  // -------------------- Menu menu ------------------

  $surl=$action->getParam("CORE_STANDURL");

  $tlink=array("headers"=>array("descr"=>_("View headers"),
				"url"=>"$surl&app=FDL&action=FDL_CARD&props=Y&id=$docid",
				"confirm"=>"false",
				"control"=>"false",
				"tconfirm"=>"",
				"target"=>"headers",
				"visibility"=>POPUP_CTRLACTIVE,
				"submenu"=>"",
				"barmenu"=>"false"),
	       "latest"=>array("descr"=>_("View latest"),
			       "url"=>"$surl&app=FDL&action=FDL_CARD&latest=Y&id=$docid",
			       "confirm"=>"false",
			       "control"=>"false",
			       "tconfirm"=>"",
			       "target"=>"latest",
			       "visibility"=>POPUP_INVISIBLE,
			       "submenu"=>"",
			       "barmenu"=>"false"),
	       "editdoc"=>array( "descr"=>_("Edit"),
				 "url"=>"$surl&app=GENERIC&action=GENERIC_EDIT&rzone=$zone&id=$docid",
				 "confirm"=>"false",
				 "control"=>"false",
				 "tconfirm"=>"",
				 "target"=>"",
				 "visibility"=>POPUP_ACTIVE,
				 "submenu"=>"",
				 "barmenu"=>"false"),
	       "delete"=>array( "descr"=>_("Delete"),
				 "url"=>"$surl&app=GENERIC&action=GENERIC_DEL&id=$docid",
				 "confirm"=>"true",
				 "control"=>"false",
				 "tconfirm"=>_("Sure delete ?"),
				 "target"=>"",
				 "visibility"=>POPUP_INACTIVE,
				 "submenu"=>"",
				 "barmenu"=>"false"),
	       "editstate"=>array( "descr"=>_("Change state"),
				   "url"=>"$surl&app=FREEDOM&action=FREEDOM_EDITSTATE&id=&id=$docid",
				   "confirm"=>"false",
				   "control"=>"false",
				   "tconfirm"=>"",
				   "target"=>"",
				   "visibility"=>POPUP_INVISIBLE,
				   "submenu"=>"",
				   "barmenu"=>"false"),
	       "lockdoc"=>array( "descr"=>_("Lock"),
				 "url"=>"$surl&app=FDL&action=LOCKFILE&id=$docid",
				 "confirm"=>"false",
				 "control"=>"false",
				 "tconfirm"=>"",
				 "target"=>"",
				 "visibility"=>POPUP_ACTIVE,
				 "submenu"=>"security",
				 "barmenu"=>"false"),
	       "unlockdoc"=>array( "descr"=>_("Unlock"),
				   "url"=>"$surl&app=FDL&action=UNLOCKFILE&id=$docid",
				   "confirm"=>"false",
				   "control"=>"false",
				   "tconfirm"=>"",
				   "target"=>"",
				   "visibility"=>POPUP_ACTIVE,
				   "submenu"=>"security",
				   "barmenu"=>"false"),
	       "revise"=>array( "descr"=>_("Revise"),
				"url"=>"$surl&app=FREEDOM&action=REVCOMMENT&id=$docid",
				"confirm"=>"false",
				"control"=>"false",
				"tconfirm"=>"",
				"target"=>"",
				"visibility"=>POPUP_INACTIVE,
				"submenu"=>"",
				   "barmenu"=>"false"),
	       "editprof"=>array( "descr"=>_("Change profile"),
				  "url"=>"$surl&app=FREEDOM&action=EDITPROF&id=$docid",
				  "confirm"=>"false",
				  "control"=>"false",
				  "tconfirm"=>"",
				  "target"=>"",
				  "visibility"=>POPUP_ACTIVE,
				  "submenu"=>"security",
				  "barmenu"=>"false"),
	       "histo"=>array( "descr"=>_("History"),
			       "url"=>"$surl&app=FREEDOM&action=HISTO&id=$docid",
			       "confirm"=>"false",
			       "control"=>"false",
			       "tconfirm"=>"",
			       "target"=>"",
			       "visibility"=>POPUP_CTRLACTIVE,
			       "submenu"=>"",
			       "barmenu"=>"false"),
	       "duplicate"=>array( "descr"=>_("Duplicate"),
				   "url"=>"$surl&app=GENERIC&action=GENERIC_DUPLICATE&id=$docid",
				   "confirm"=>"true",
				   "control"=>"false",
				   "tconfirm"=>_("Sure duplicate ?"),
				   "target"=>"",
				   "visibility"=>POPUP_CTRLACTIVE,
				   "submenu"=>"",
				   "barmenu"=>"false"),
	       "access"=>array( "descr"=>_("goaccess"),
				"url"=>"$surl&app=FREEDOM&action=FREEDOM_GACCESS&id=".$doc->profid,
				"confirm"=>"false",
				"control"=>"false",
				"tconfirm"=>"",
				"target"=>"",
				"visibility"=>POPUP_ACTIVE,
				"submenu"=>"security"),
	       "tobasket"=>array( "descr"=>_("Add to basket"),
				  "url"=>"$surl&app=FREEDOM&action=ADDDIRFILE&docid=$docid&dirid=".$action->getParam("FREEDOM_IDBASKET"),
				  "confirm"=>"false",
				  "control"=>"false",
				  "tconfirm"=>"",
				  "target"=>"",
				  "visibility"=>POPUP_CTRLACTIVE,
				  "submenu"=>"",
				  "barmenu"=>"false"),
	       "addpostit"=>array( "descr"=>_("Add postit"),
				   "url"=>"$surl&app=FDL&action=&id=$docid",
				   "confirm"=>"false",
				   "control"=>"false",
				   "tconfirm"=>"",
				   "target"=>"",
				   "visibility"=>POPUP_CTRLACTIVE,
				   "submenu"=>"",
				   "barmenu"=>"false"),
	       "toxml"=>array( "descr"=>_("View XML"),
			       "url"=>"$surl&app=FDL&action=VIEWXML&id=$docid",
			       "confirm"=>"false",
			       "control"=>"false",
			       "tconfirm"=>"",
			       "target"=>"",
			       "visibility"=>POPUP_CTRLACTIVE,
			       "submenu"=>"",
			       "barmenu"=>"false"),
	       "reference"=>array( "descr"=>_("Search linked documents"),
				   "url"=>"$surl&app=GENERIC&action=GENERIC_ISEARCH&id=$docid",
				   "confirm"=>"false",
				   "control"=>"false",
				   "tconfirm"=>"",
				   "target"=>"",
				   "visibility"=>POPUP_CTRLACTIVE,
				   "submenu"=>"",
				   "barmenu"=>"false"));


  changeMenuVisibility($action,$tlink,$doc);

  $lmenu = $doc->GetMenuAttributes();
 

  foreach($lmenu as $k=>$v) {
    
    $confirm=false;
    $control=false;
    if (($v->getOption("onlyglobal")=="yes") && ($doc->doctype!="C")) continue;
    if (($v->getOption("global")!="yes") && ($doc->doctype=="C")) continue;
    if ($v->link[0] == '?') { 
      $v->link=substr($v->link,1);
      $confirm=true;
    }
    if ($v->getOption("lconfirm")=="yes") $confirm=true;
    if ($v->link[0] == 'C') { 
      $v->link=substr($v->link,1);
      $control=true;
    }
    if ($v->getOption("lcontrol")=="yes") $control=true;
    if (ereg('\[(.*)\](.*)', $v->link, $reg)) {      
      $v->link=$reg[2];
      $tlink[$k]["target"] = $reg[1];
    } else {
      $tlink[$k]["target"] = $v->id;
    } 
    if ($v->getOption("ltarget")!="") $tlink[$k]["target"] = $v->getOption("ltarget");
    $tlink[$k]["idlink"] = $v->id;
    $tlink[$k]["descr"] = $v->labelText;
    $tlink[$k]["url"] = addslashes($doc->urlWhatEncode($v->link));
    $tlink[$k]["confirm"]=$confirm?"true":"false";
    $tlink[$k]["control"]=$control;
    $tlink[$k]["tconfirm"]=sprintf(_("Sure %s ?"),addslashes($v->labelText));
    $tlink[$k]["visibility"]=($control)?POPUP_CTRLACTIVE:POPUP_ACTIVE;
    $tlink[$k]["submenu"]=$v->getOption("submenu");
    $tlink[$k]["barmenu"] = ($v->getOption("barmenu")=="yes")?"true":"false";
    if ($v->precond != "") $tlink[$k]["visibility"]=$doc->ApplyMethod($v->precond,POPUP_ACTIVE);
    
  }

  // -------------------- Menu action ------------------
  $lactions=$doc->GetActionAttributes();
  foreach($lactions as $k=>$v) {

      $confirm=false;
      $control=false;
      $v->link=$v->getLink($doc->id);
      if ($v->getOption("lconfirm")=="yes") $confirm=true;
      if ($v->getOption("lcontrol")=="yes") $control=true;

      
      if (ereg('\[(.*)\](.*)', $v->link, $reg)) {      
	$v->link=$reg[2];
	$tlink[$k]["target"] = $reg[1];
      } else {
	$tlink[$k]["target"] = $v->id;
      }
      $tlink[$k]["barmenu"] = ($v->getOption("barmenu")=="yes")?"true":"false";
      $tlink[$k]["idlink"] = $v->id;
      $tlink[$k]["descr"] = $v->labelText;
      $tlink[$k]["url"] = addslashes($doc->urlWhatEncode($v->link));
      $tlink[$k]["confirm"]=$confirm?"true":"false";
      $tlink[$k]["control"]=$control;
      $tlink[$k]["tconfirm"]=sprintf(_("Sure %s ?"),addslashes($v->labelText));
      $tlink[$k]["visibility"]=($control)?POPUP_CTRLACTIVE:POPUP_ACTIVE;
      $tlink[$k]["submenu"]=$v->getOption("submenu");
      
    
  }



         
  popupdoc($action,$tlink,$tsubmenu);
}

function changeMenuVisibility(&$action,&$tlink,&$doc) {
   $clf = ($doc->CanLockFile() == "");
  $cuf = ($doc->CanUnLockFile() == "");
  $cud = ($doc->CanUpdateDoc() == "");
  $tlink["toxml"]["visibility"]=POPUP_INVISIBLE;
  //  $tlink["reference"]["visibility"]=POPUP_CTRLACTIVE;

  if (getParam("FREEDOM_IDBASKET") > 0)  $tlink["tobasket"]["visibility"]=POPUP_CTRLACTIVE;
  else $tlink["tobasket"]["visibility"]=POPUP_INVISIBLE;

  if ($doc->locked == $doc->userid) $tlink["lockdoc"]["visibility"]=POPUP_INVISIBLE;
  else if (($doc->locked != $doc->userid) && 
	   $clf) $tlink["lockdoc"]["visibility"]=POPUP_CTRLACTIVE;
  else $tlink["lockdoc"]["visibility"]=POPUP_INVISIBLE;

  if ($doc->isLocked()) {
    if ($cuf) $tlink["unlockdoc"]["visibility"]=POPUP_ACTIVE;
    else $tlink["unlockdoc"]["visibility"]=POPUP_INACTIVE;
  } else $tlink["unlockdoc"]["visibility"]=POPUP_INVISIBLE;

  if (! $doc->isRevisable()) $tlink["revise"]["visibility"]=POPUP_INVISIBLE;
  else if (($doc->lmodify == 'Y') && 
	   ($cud||$clf)) $tlink["revise"]["visibility"]=POPUP_CTRLACTIVE;
  else $tlink["revise"]["visibility"]=POPUP_CTRLINACTIVE;

  if ($doc->IsControlled() && ($doc->profid > 0) && ($doc->Control("viewacl") == "")) {
    $tlink["access"]["visibility"]=POPUP_CTRLACTIVE;
  } else {
    $tlink["access"]["visibility"]=POPUP_INVISIBLE;
  }

  if ($doc->Control("modifyacl") == "") {
    $tlink["editprof"]["visibility"]=POPUP_CTRLACTIVE;
  } else {
    $tlink["editprof"]["visibility"]=POPUP_CTRLINACTIVE;
  }

  if ($doc->PreDocDelete() == "") {
    $tlink["delete"]["visibility"]=POPUP_ACTIVE; 
  } else {
    $tlink["delete"]["visibility"]=POPUP_INACTIVE; 
  }



  if (($clf)||($cud)) {
    $tlink["editdoc"]["visibility"]=POPUP_ACTIVE;    
  }  else {
    $tlink["editprof"]["visibility"]=POPUP_CTRLINACTIVE;
    $tlink["editdoc"]["visibility"]=POPUP_INACTIVE;      
  }

  if ($doc->locked == -1) { // fixed document
    if ($doc->doctype != 'Z') $tlink["latest"]["visibility"]=POPUP_ACTIVE; 
    $tlink["editdoc"]["visibility"]=POPUP_INVISIBLE;
    $tlink["delete"]["visibility"]=POPUP_INVISIBLE;
    $tlink["editprof"]["visibility"]=POPUP_INVISIBLE;
    $tlink["revise"]["visibility"]=POPUP_INVISIBLE;
    $tlink["lockdoc"]["visibility"]=POPUP_INVISIBLE;
  } 



  if ($doc->locked != -1) {
    if ($doc->wid > 0) {
      $wdoc=new_Doc($doc->dbaccess, $doc->wid);
      if ($wdoc->isAlive()) {
	$wdoc->Set($doc);
	if (count($wdoc->GetFollowingStates()) > 0)  $tlink["editstate"]["visibility"]=POPUP_ACTIVE;
	else $tlink["editstate"]["visibility"]=POPUP_INACTIVE;
      }
    }
  }

  if (($doc->wid > 0)|| ($doc->revision > 0))  $tlink["histo"]["visibility"]=POPUP_ACTIVE;

  



  // if ($doc->doctype == "S") popupInvisible('popupcard',$kdiv,'editdoc'); 

  if ($headers)  $tlink["headers"]["visibility"]=POPUP_INVISIBLE;
  else $tlink["headers"]["visibility"]=POPUP_CTRLACTIVE;


  if ($doc->postitid > 0) $tlink["addpostit"]["visibility"]=POPUP_INVISIBLE;
  else $tlink["addpostit"]["visibility"]=POPUP_CTRLACTIVE;

  if (! $action->parent->Haspermission("FREEDOM","FREEDOM")) {

    // FREEDOM not available
   
    // actions not available
    $tlink["editstate"]["visibility"]=POPUP_INVISIBLE;
    $tlink["revise"]["visibility"]=POPUP_INVISIBLE;
    $tlink["editprof"]["visibility"]=POPUP_INVISIBLE;
    $tlink["access"]["visibility"]=POPUP_INVISIBLE;
    $tlink["tobasket"]["visibility"]=POPUP_INVISIBLE;
  }
  if (! $action->parent->Haspermission("FREEDOM_READ","FREEDOM")) {
    $tlink["histo"]["visibility"]=POPUP_INVISIBLE;
  }
}

?>