<?php
/**
 * Specific menu for family
 *
 * @author Anakeen 2000 
 * @version $Id: popupdocdetail.php,v 1.19 2007/07/02 13:21:07 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/popupdoc.php");
function popupdocdetail(&$action) {
  $docid = GetHttpVars("id");
  if ($docid == "") $action->exitError(_("No identificator"));
  $popup=getpopupdocdetail($action,$docid);

  
  popupdoc($action,$popup);
  
}
function getpopupdocdetail(&$action,$docid) {
  // define accessibility
  $zone = GetHttpVars("zone"); // special zone

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new_Doc($dbaccess, $docid);

  //  if ($doc->doctype=="C") return; // not for familly


  $tsubmenu=array();

  // -------------------- Menu menu ------------------

  $surl=$action->getParam("CORE_STANDURL");

  $tlink=array("headers"=>array("descr"=>_("Properties"),
				"url"=>"$surl&app=FDL&action=IMPCARD&zone=FDL:VIEWPROPERTIES:T&id=$docid",
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
			       "target"=>"_self",
			       "visibility"=>POPUP_INVISIBLE,
			       "submenu"=>"",
			       "barmenu"=>"false"),
	       "editdoc"=>array( "descr"=>_("Edit"),
				 "url"=>"$surl&app=GENERIC&action=GENERIC_EDIT&rzone=$zone&id=$docid",
				 "confirm"=>"false",
				 "control"=>"false",
				 "tconfirm"=>"",
				 "target"=>"_self",
				 "visibility"=>POPUP_ACTIVE,
				 "submenu"=>"",
				 "barmenu"=>"false"));

  addCvPopup($tlink,$doc);
  addStatesPopup($tlink,$doc);
  $tlink=array_merge($tlink,  array(
	       "delete"=>array( "descr"=>_("Delete"),
				 "url"=>"$surl&app=GENERIC&action=GENERIC_DEL&id=$docid",
				 "confirm"=>"true",
				 "control"=>"false",
				"tconfirm"=>sprintf(_("Sure delete %s ?"),str_replace("'","&rsquo;",$doc->title)),
				 "target"=>"_self",
				 "visibility"=>POPUP_INACTIVE,
				 "submenu"=>"",
				 "barmenu"=>"false"), 
	       "restore"=>array( "descr"=>_("restore"),
				 "url"=>"$surl&app=WORKSPACE&action=WS_RESTOREDOC&id=$docid&reload=Y",
				 "tconfirm"=>"",
				 "confirm"=>"false",
				 "target"=>"_self",
				 "visibility"=>POPUP_INVISIBLE,
				 "submenu"=>"",
				 "barmenu"=>"false"),
	       "editstate"=>array( "descr"=>_("Change state"),
				   "url"=>"$surl&app=FREEDOM&action=FREEDOM_EDITSTATE&id=$docid",
				   "confirm"=>"false",
				   "control"=>"false",
				   "tconfirm"=>"",
				   "target"=>"_self",
				   "visibility"=>POPUP_INVISIBLE,
				   "submenu"=>"",
				   "barmenu"=>"false"),
	       "lockdoc"=>array( "descr"=>_("Lock"),
				 "url"=>"$surl&app=FDL&action=LOCKFILE&id=$docid",
				 "confirm"=>"false",
				 "control"=>"false",
				 "tconfirm"=>"",
				 "target"=>"_self",
				 "visibility"=>POPUP_CTRLACTIVE,
				 "submenu"=>N_("security"), 
				 "barmenu"=>"false"),
	       "unlockdoc"=>array( "descr"=>_("Unlock"),
				   "url"=>"$surl&app=FDL&action=UNLOCKFILE&id=$docid",
				   "confirm"=>"false",
				   "control"=>"false",
				   "tconfirm"=>"",
				   "target"=>"_self",
				   "visibility"=>POPUP_CTRLACTIVE,
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
				   "target"=>"_self",
				   "visibility"=>POPUP_CTRLACTIVE,
				   "submenu"=>"",
				   "barmenu"=>"false"),
	       "access"=>array( "descr"=>_("goaccess"),
				"url"=>"$surl&app=FREEDOM&action=FREEDOM_GACCESS&id=".$doc->profid,
				"confirm"=>"false",
				"control"=>"false",
				"tconfirm"=>"",
				"target"=>"",
				"mwidth"=>800,
				"mheight"=>300,
				"visibility"=>POPUP_ACTIVE,
				"submenu"=>"security",
				"barmenu"=>"false"),
	       "tobasket"=>array( "descr"=>_("Add to basket"),
				  "url"=>"$surl&app=FREEDOM&action=ADDDIRFILE&docid=$docid&dirid=".$action->getParam("FREEDOM_IDBASKET"),
				  "confirm"=>"false",
				  "control"=>"false",
				  "tconfirm"=>"",
				  "target"=>"",
				  "visibility"=>POPUP_CTRLACTIVE,
				  "submenu"=>"",
				  "barmenu"=>"false"),
	       "chgicon"=>array( "descr"=>_("Change icon"),
				  "url"=>"$surl&app=FDL&action=EDITICON&id=$docid",
				  "confirm"=>"false",
				  "control"=>"false",
				  "tconfirm"=>"",
				  "target"=>"_self",
				  "visibility"=>POPUP_INVISIBLE,
				  "submenu"=>"",
				  "barmenu"=>"false"),
	       "addpostit"=>array( "descr"=>_("Add postit"),
				   "jsfunction"=>"postit('$surl&app=GENERIC&action=GENERIC_EDIT&classid=27&pit_title=&pit_idadoc=$docid',50,50,300,200)",
				   "confirm"=>"false",
				   "control"=>"false",
				   "tconfirm"=>"",
				   "target"=>"",
				   "visibility"=>POPUP_ACTIVE,
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
	       "relations"=>array( "descr"=>_("Document relations"),
				    "url"=>"$surl&app=FREEDOM&action=RNAVIGATE&id=$docid",
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
				   "visibility"=>POPUP_INVISIBLE,
				   "submenu"=>"",
				   "barmenu"=>"false")));

  changeMenuVisibility($action,$tlink,$doc);
  


  addFamilyPopup($tlink,$doc);

  return $tlink;
         
}
/**
 * Add control view menu
 */
function addCvPopup(&$tlink,&$doc) {
 
  if ($doc->cvid > 0 )  {

    $surl=getParam("CORE_STANDURL");
    $cud = ($doc->CanEdit() == "");
    $docid=$doc->id;
    $cvdoc = new_Doc($doc->dbaccess, $doc->cvid);
    $cvdoc->set($doc);
    $ti = $cvdoc->getTValue("CV_IDVIEW");
    $tl = $cvdoc->getTValue("CV_LVIEW");
    $tz = $cvdoc->getTValue("CV_ZVIEW");
    $tk = $cvdoc->getTValue("CV_KVIEW");
    $tm = $cvdoc->getTValue("CV_MSKID");
    $td = $cvdoc->getTValue("CV_DISPLAYED");


    $tv=array(); // consult array views
    $te=array(); // edit array views

    if (count($tk) > 0)  {
      foreach ($tk as $k=>$v) {
	if ($td[$k] != "no") {
	  if ($tz[$k] != "") {	  
	    if ($ti[$k]=="") $cvk="CV$k";
	    else $cvk=$ti[$k];
	    if ($v == "VEDIT") {
	      if ($cud) {	    
		if ($cvdoc->control($cvk) == "") {
		  $tv[$cvk] = array("typeview"=>N_("specialedit"),
				    "idview"   => $cvk,
				    "zoneview" => $tz[$k],
				    "txtview"  => $tl[$k]);
		}
	      }
	    } else {      
	      if ($cvdoc->control($cvk) == "") {
		$tv[$cvk] = array("typeview"=>N_("specialview"),
				  "idview"   => $cvk,
				  "zoneview" => $tz[$k],
				  "txtview"  => $tl[$k]);
	      }
	    }
	  }
	}
      }
    } 

    foreach ($tv as $v) {
      $tlink[$v["idview"]]=array( "descr"=>$v["txtview"],
				  "url"=>($v["typeview"]=='specialview')?"$surl&app=FDL&action=FDL_CARD&vid=".$v["idview"]."&id=$docid":"$surl&app=GENERIC&action=GENERIC_EDIT&vid=".$v["idview"]."&id=$docid",
				 "confirm"=>"false",
				 "control"=>"false",
				 "tconfirm"=>"",
				 "target"=>"_self",
				 "visibility"=>POPUP_ACTIVE,
				 "submenu"=>$v["typeview"],
				 "barmenu"=>"false");
    }

  
  }
}

/**
 * Add control view menu
 */
function addStatesPopup(&$tlink,&$doc) {
 
  if ($doc->wid > 0 )  {
      $wdoc = new_Doc($doc->dbaccess,$doc->wid);
      $wdoc->Set($doc);
      $fstate = $wdoc->GetFollowingStates();

    $surl=getParam("CORE_STANDURL");
    $docid=$doc->id;

    foreach ($fstate as $v) {
      $tr=$wdoc->getTransition($doc->state,$v);
      if (is_array($tr["ask"])) {
	$jsf=sprintf("popdoc(event,'$surl&app=FDL&action=EDITCHANGESTATE&id=$docid&nstate=$v','%s',0,40,400,250)",
		     utf8_encode(str_replace("'","&rsquo;",sprintf(_("Change state %s"),_($v)))));
      } else {
	$jsf=sprintf("s=prompt('%s');if (s!=null) subwindow(100,100,'_self','$surl&app=FREEDOM&action=MODSTATE&newstate=$v&id=$docid&comment='+s);",	
		     utf8_encode(str_replace("'","&rsquo;",sprintf(_("Comment for change state to %s"),_($v)))));
      }

      $tlink[$v]=array( "descr"=>ucfirst(_($v)),			
			"jsfunction"=>$jsf,
			"confirm"=>"false",
			"control"=>"false",
			"color"=>$wdoc->getColor($v),
			"tconfirm"=>"",
			"target"=>"_self",
			"visibility"=>POPUP_ACTIVE,
			"submenu"=>"chgstates", #_("chgstates")
			"barmenu"=>"false");
    }

  
  }
}
function addFamilyPopup(&$tlink,&$doc) {
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


}
function changeMenuVisibility(&$action,&$tlink,&$doc) {
   $clf = ($doc->CanLockFile() == "");
  $cuf = ($doc->CanUnLockFile() == "");
  $cud = ($doc->CanUpdateDoc() == "");
  $tlink["toxml"]["visibility"]=POPUP_INVISIBLE;
  //  $tlink["reference"]["visibility"]=POPUP_CTRLACTIVE;

  if (getParam("FREEDOM_IDBASKET") == 0)  $tlink["tobasket"]["visibility"]=POPUP_INVISIBLE;

  if ($doc->locked == $doc->userid) $tlink["lockdoc"]["visibility"]=POPUP_INVISIBLE;
  else if (($doc->locked != $doc->userid) && 
	   $clf) $tlink["lockdoc"]["visibility"]=$tlink["lockdoc"]["visibility"];
  else $tlink["lockdoc"]["visibility"]=POPUP_INVISIBLE;

  if ($doc->isLocked()) {
    if ($cuf) $tlink["unlockdoc"]["visibility"]=POPUP_ACTIVE;
    else $tlink["unlockdoc"]["visibility"]=POPUP_INACTIVE;
  } else $tlink["unlockdoc"]["visibility"]=POPUP_INVISIBLE;

  if (! $doc->isRevisable()) $tlink["revise"]["visibility"]=POPUP_INVISIBLE;
  else if ((($doc->lmodify == 'Y')||($doc->revision==0)) && 
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
    $tlink["chgicon"]["visibility"]=POPUP_CTRLACTIVE;   
  }  else {
    $tlink["editprof"]["visibility"]=POPUP_CTRLINACTIVE;
    $tlink["editdoc"]["visibility"]=POPUP_INACTIVE;      
  }

  if ($doc->locked == -1) { // fixed document
    if ($doc->doctype != 'Z') $tlink["latest"]["visibility"]=POPUP_ACTIVE; 
    else $tlink["restore"]["visibility"]=POPUP_ACTIVE; 
    $tlink["editdoc"]["visibility"]=POPUP_INVISIBLE;
    $tlink["delete"]["visibility"]=POPUP_INVISIBLE;
    $tlink["editprof"]["visibility"]=POPUP_INVISIBLE;
    $tlink["revise"]["visibility"]=POPUP_INVISIBLE;
    $tlink["lockdoc"]["visibility"]=POPUP_INVISIBLE;
  } 


  /*
  if ($doc->locked != -1) {
    if ($doc->wid > 0) {
      $wdoc=new_Doc($doc->dbaccess, $doc->wid);
      if ($wdoc->isAlive()) {
	$wdoc->Set($doc);
	if (count($wdoc->GetFollowingStates()) > 0)  $tlink["editstate"]["visibility"]=POPUP_ACTIVE;
	else $tlink["editstate"]["visibility"]=POPUP_INACTIVE;
      }
    }
    }*/

  if (($doc->wid > 0)|| ($doc->revision > 0))  $tlink["histo"]["visibility"]=POPUP_ACTIVE;

  
  if ($doc->doctype == "F") $tlink["chgicon"]["visibility"]=POPUP_INVISIBLE;

  if ($headers)  $tlink["headers"]["visibility"]=POPUP_INVISIBLE;
  else $tlink["headers"]["visibility"]=POPUP_CTRLACTIVE;


  if ($doc->postitid > 0) $tlink["addpostit"]["visibility"]=POPUP_INVISIBLE;
  else if ($doc->fromid==27) $tlink["addpostit"]["visibility"]=POPUP_CTRLACTIVE; // for post it family
  else $tlink["addpostit"]["visibility"]=POPUP_ACTIVE;

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