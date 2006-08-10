<?php
/**
 * View Document History
 *
 * @author Anakeen 2000 
 * @version $Id: viewhisto.php,v 1.18 2006/08/10 15:08:44 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */




include_once("FDL/Class.Doc.php");
function viewhisto(&$action) 
{
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);
  $target = GetHttpVars("target","doc_properties");
  $viewapp = GetHttpVars("viewapp","FDL");
  $viewact = GetHttpVars("viewact","FDL_CARD");
  $target = GetHttpVars("target","doc_properties");
  $viewrev = (GetHttpVars("viewrev","Y")=="Y");
  $comment = GetHttpVars("comment",_("no comment"));

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/common.js");

  $doc= new_Doc($dbaccess,$docid);
  $action->lay->Set("title",$doc->title);
  $action->lay->Set("target",$target);
  $action->lay->Set("VIEWAPP",$viewapp);
  $action->lay->Set("VIEWACT",$viewact);
  $action->lay->Set("VIEWREV",$viewrev);
  $action->lay->Set("STATE",($doc->state != ""));

  $ldoc = $doc->GetRevisions("TABLE");

  $trdoc= array();
  $tversion=array();
  $iversion=0;
  foreach($ldoc as $k=>$zdoc) {
    $rdoc=getDocObject($dbaccess,$zdoc);
    $owner = new User("", $rdoc->owner);
    $trdoc[$k]["owner"]= $owner->firstname." ".$owner->lastname;
    $trdoc[$k]["revision"]= $rdoc->revision;
    $trdoc[$k]["version"]= $rdoc->version;
    $state=$rdoc->getState();
    $trdoc[$k]["state"]= ($state=="")?"":(($rdoc->locked==-1)?_($state):_("current"));
    if ($action->GetParam("CORE_LANG") == "fr_FR") { // date format depend of locale
      setlocale (LC_TIME, "fr_FR");
      $trdoc[$k]["date"]= strftime ("%a %d %b %Y %H:%M",$rdoc->revdate);
    } else {
      $trdoc[$k]["date"]= strftime ("%x<BR>%T",$rdoc->revdate);        
    }

    // special table for versions
    if (! in_array($rdoc->version, array_keys($tversion))) {
      $tversion[$rdoc->version]="vtr".$iversion++;
      $trdoc[$k]["cversion"]= true;
    } else {
      $trdoc[$k]["cversion"]= false;
    }
    $trdoc[$k]["vername"]= $tversion[$rdoc->version];


    $trdoc[$k]["COMMENT"]="COMMENT$k";

    $tc=$rdoc->getHisto();
    $tlc = array();
    $kc=0; // index comment
    foreach ($tc as $vc) {

      $stime=$vc["date"];
      /*	if (ereg("([0-9]{1,2})/([0-9]{1,2})/([0-9]{1,4}) ([0-2]{0,1}[0-9]):([0-5]{0,1}[0-9])", 
		 $reg[1], $regt)) {   
	  $stime=strftime ("%a %d %b %Y %H:%M",mktime($regt[4],$regt[5],$regt[6],$regt[2],$regt[1],$regt[3]));
	} else $stime=$reg[1];
      */
	$tlc[]=array("cdate"=>$stime,
		     "cauthor"=>$vc["uname"],
		     "ccomment"=>nl2br(htmlentities($vc["comment"])));            
    }
    $action->lay->SetBlockData("COMMENT$k",$tlc);

    $trdoc[$k]["id"]= $rdoc->id;
    $trdoc[$k]["divid"]= $k;

    
  }
  // not display detail display 
  $action->lay->Set("viewdiff",(count($ldoc)>1));
  $action->lay->Set("nodetail",($iversion>1));
  $action->lay->SetBlockData("TABLEBODY",$trdoc);
 
}

?>
