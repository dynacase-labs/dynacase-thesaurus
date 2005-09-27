<?php
/**
 * View Document History
 *
 * @author Anakeen 2000 
 * @version $Id: viewhisto.php,v 1.13 2005/09/27 16:16:50 eric Exp $
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
  $comment = GetHttpVars("comment",_("no comment"));

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");

  $doc= new_Doc($dbaccess,$docid);
  $action->lay->Set("title",$doc->title);
  $action->lay->Set("target",$target);
  $action->lay->Set("VIEWAPP",$viewapp);
  $action->lay->Set("VIEWACT",$viewact);

  $ldoc = $doc->GetRevisions("TABLE");

  $trdoc= array();
  while(list($k,$zdoc) = each($ldoc)) {
    $rdoc=getDocObject($dbaccess,$zdoc);
    $owner = new User("", $rdoc->owner);
    $trdoc[$k]["owner"]= $owner->firstname." ".$owner->lastname;
    $trdoc[$k]["revision"]= $rdoc->revision;
    $trdoc[$k]["state"]= ($rdoc->state=="")?"":(($rdoc->locked==-1)?_($rdoc->state):_("current"));
    $trdoc[$k]["COMMENT"]="COMMENT$k";
    $tc = explode("\n",$rdoc->comment);
    $tlc = array();
    $kc=0; // index comment
    foreach ($tc as $vc) {
      if (ereg("([^\[]*)\[([^]]*)\](.*)",$vc,$reg)) {
	$kc++;
	if (ereg("([0-9]{1,2})/([0-9]{1,2})/([0-9]{1,4}) ([0-2]{0,1}[0-9]):([0-5]{0,1}[0-9])", 
		 $reg[1], $regt)) {   
	  $stime=strftime ("%a %d %b %Y %H:%M",mktime($regt[4],$regt[5],$regt[6],$regt[2],$regt[1],$regt[3]));
	} else $stime=$reg[1];

	$tlc[$kc]=array("cdate"=>$stime,
			"cauthor"=>$reg[2],
			"ccomment"=>$reg[3]);
      } else {
	$tlc[$kc]["ccomment"].="<BR>".$vc;
	if (! isset($tlc[$kc]["cdate"])) {
	  $tlc[$kc]["cdate"]="";
	  $tlc[$kc]["cauthor"]="";
	}
      }
      
    }
    $action->lay->SetBlockData("COMMENT$k",$tlc);

    $trdoc[$k]["comment"]= nl2br(htmlentities($rdoc->comment));
    $trdoc[$k]["id"]= $rdoc->id;
    $trdoc[$k]["divid"]= $k;

    if ($action->GetParam("CORE_LANG") == "fr_FR") { // date format depend of locale
      setlocale (LC_TIME, "fr_FR");
      $trdoc[$k]["date"]= strftime ("%a %d %b %Y %H:%M",$rdoc->revdate);
    } else {
      $trdoc[$k]["date"]= strftime ("%x<BR>%T",$rdoc->revdate);
    
    
    }
  }

  $action->lay->SetBlockData("TABLEBODY",$trdoc);
  // js : manage icons
  $licon = new Layout($action->GetLayoutFile("manageicon.js"),$action);
  $licon->Set("nbdiv",1);
  $action->parent->AddJsCode($licon->gen());
}

?>
