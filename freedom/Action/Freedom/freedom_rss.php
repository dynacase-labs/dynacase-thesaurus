<?php
/**
 * RSS syndication on a folder (search, folders, report....)
 *
 * @author Anakeen 2003
 * @version $Id: freedom_rss.php,v 1.2 2006/11/27 09:55:35 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

include_once("FDL/freedom_util.php");
include_once("FDL/Lib.Dir.php");

function freedom_rss(&$action) {

  $id = GetHttpVars("id", 0);
  $dhtml = (GetHttpVars("dh", 1)==1?true:false);
  $action->lay->set("html", $dhtml);
  $lim = GetHttpVars("lim", 100);

  $dbaccess = $action->GetParam("FREEDOM_DB");

  header('Content-type: text/xml; charset=utf-8');
  $action->lay->setEncoding("utf-8");
  
  $baseurl=__xmlentities($action->GetParam("CORE_BASEURL"));
  $action->lay->set("baseurl", $baseurl);

  $standurl=__xmlentities($action->GetParam("CORE_STANDURL"));
  $action->lay->set("standurl", $standurl);

  $action->lay->set("server", getparam("CORE_ABSURL")); 

  $cssf = getparam("CORE_STANDURL")."&app=CORE&action=CORE_CSS&session=".$action->session->id."&layout=FDL:RSS.CSS";
  $action->lay->set("rsscss", $cssf); 

  $rsslink = $baseurl.__xmlentities("app=FDL&action=FDL_CARD&props=N&abstract=N&id=".$id);
  $action->lay->set("rsslink", $rsslink);
  $action->lay->set("copy", "Copyright 2006 Anakeen");
  $action->lay->set("lang", substr(getParam("CORE_LANG"),0,2));
  $action->lay->set("datepub", strftime("%a, %d %b %Y %H:%M:%S %z",time()));
  $action->lay->set("ttl", 60);
  $action->lay->set("category", "Freedom documents");
  $action->lay->set("generator", "Freedom version ".$action->parent->getParam("VERSION"));


  $doc = new_Doc($dbaccess,$id);
  $action->lay->set("lastbuild", strftime("%a, %d %b %Y %H:%M:%S %z",$doc->revdate));

  // Check right for doc access
  if ($doc->defDoctype=='S') $aclctrl="execute";
  else $aclctrl="open";
  if (($err=$doc->Control($aclctrl)) != "") {
    $action->log->error($err);
    return; 
  }

  if ($doc->doctype!='S' && $doc->doctype!='D') {

    $ldoc = array(getTDoc($dbaccess, $id));

  } else {

    $filter = array();
    $famid = "";
    $report = ($doc->fromid==getIdFromName($dbaccess,"REPORT") ? true : false );
    $items = array();
    $order = $doc->getValue("REP_IDSORT","title");
    $order .= " ".$doc->getValue("REP_ORDERSORT");
    $ldoc = getChildDoc($dbaccess, 
			$doc->id, 
			0, 
			$lim, 
			$filter,
			$action->user->id,
			"TABLE",
			$famid, 
			false,
			$order );
  }
 
  if ($report) {
    $tmpdoc=createDoc($dbaccess, getIdFromName($dbaccess,"REPORT"), false);
    $fdoc=createDoc($dbaccess, $doc->getValue("SE_FAMID"), false);
    $lattr=$fdoc->GetNormalAttributes();
    $tcol1=array();
    foreach ($lattr as $k => $v) {
      $tcol1[$v->id] = array( "colid"=>$v->id,
			      "collabel"=>$v->labelText,
			      "rightfornumber"=>($v->type == "money")?"right":"left");
    }
    $tinternals = $tmpdoc->_getInternals();
    foreach ($tinternals as $k => $v) {
      $tcol1[$k]=array( "colid"=>$k,
			"collabel"=>$v,
			"rightfornumber"=>"left");
    }
    $tcolshown = array();
    $tcols = $doc->getTValue("REP_IDCOLS");
    foreach ($tcols as $k => $v) {
      $tcolshown[$v] = $tcol1[$v];   
    }
 }

  $action->lay->set("rssname", $doc->getTitle()."  -".count($ldoc)."-");

  $lines = array();
  foreach ($ldoc as $kdoc => $vdoc) {
    $zdoc = getDocObject($dbaccess,$vdoc);
    $descr = '';
    
    $items[$zdoc->id] = array( "title" => "",
			       "link" => $baseurl.__xmlentities("app=FDL&action=FDL_CARD&props=N&abstract=N&id=".$zdoc->id),
			       "descr" => "",
			       "revdate" => strftime("%a, %d %b %Y %H:%M:%S %z",$zdoc->revdate),
			       "id" => $zdoc->id,
			       "category" => getFamTitle($zdoc->fromid),
			       "author" => getMailAddr($zdoc->owner, true),
			       "rssname" => $doc->getTitle,
			       "rsslink" => $rsslink,
			       "report" => $report,
			       );
    if ($report) {
      $lines[$zdoc->id] = array();
      $i = 0;
      foreach ($tcolshown as $kc => $vc) {
	if ($vdoc[$kc] == "") $lines[$zdoc->id][] = array("attr" => $vc["collabel"], "val" => "" );
	else {
	  switch ($kc) {
	  case "revdate" :
	    $cval = strftime ("%x %T",$vdoc[$kc]);
	    break;
	  case "state" :
	    $cval = _($vdoc[$kc]);
	    break;
	  default:
	    $cval = $zdoc->getHtmlValue($lattr[$kc],$vdoc[$kc],"",false);
	    if ($lattr[$kc]->type == "image") $cval="<img width=\"30px\" src=\"$cval\">";
	  }
	  $cval = __xmlentities($cval);
	  if ($i==0) {
	    $items[$zdoc->id]["title"] = ($cval); 
	    $i++; 
	  } else $lines[$zdoc->id][] = array("attr" => $vc["collabel"], "val" => ($cval));
	}
      }
      
    } else {
      $items[$zdoc->id]["descr"] = ($dhtml ? __xmlentities($zdoc->viewdoc("FDL:VIEWTHUMBCARD")) : "..." );
      $items[$zdoc->id]["title"] = __xmlentities($zdoc->getTitle());
    }      
  }
  $action->lay->setBlockData("Items", $items);
  $action->lay->set("report", $report);
  if ($report) {
    foreach ($lines as $kl => $vl) {
      $action->lay->setBlockData("lines".$kl, $vl);
    }
  }

}

function __xmlentities($string) {
  return preg_replace(array('/&/', '/"/', "/'/", '/</', '/>/'), array('&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '&apos;' ), $string);
}

function getFamTitle($id) {
  global $action;
  $t = getTDoc(getParam("FREEDOM_DB"), $id);
  if (isset($t["title"])) return $t["title"];
  return "Family $id";
}


?>