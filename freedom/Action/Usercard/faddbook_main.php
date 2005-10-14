<?php
/**
 * Freedom Address Book
 *
 * @author Anakeen 2000
 * @version $Id: faddbook_main.php,v 1.15 2005/10/14 14:12:54 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
 /**
 */

include_once("FDL/freedom_util.php");
include_once("FDL/Lib.Dir.php");

function faddbook_main(&$action) 
{ 
  global $_POST;

  $rqi_form = array();
  foreach ($_POST as $k => $v) {
    if (substr($k,0,4)=="rqi_") $rqi_form[substr($k,4)] = $v;
  }

  $dbaccess = $action->getParam("FREEDOM_DB");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");

  $pstart = GetHttpVars("sp", 0);
  $action->lay->set("isManager", ($action->parent->Haspermission("USERCARD","USERCARD_MANAGER")==1?true:false));

  $chattr = GetHttpVars("chgAttr", "");
  $chid   = GetHttpVars("chgId", "");
  $chval  = GetHttpVars("chgValue", "");
  if ($chattr!="" && $chid!="") {
    $mdoc = new_Doc($dbaccess, $chid);
    $mdoc->setValue($chattr, $chval);
    $err=$mdoc->Modify();
    if ($err=="") AddWarningMsg($mdoc->title." modifié (".$mdoc->getAttribute($chattr)->labelText." : ".$chval.")");
  }

  // Init page lines
  $lpage = $action->getParam("FADDBOOK_MAINLINE", 25);
  $action->lay->set("linep", $lpage);
  $choicel = array( 10, 25, 50 );
  foreach ($choicel as $k => $v) {
    $tl[] = array( "count" => $v, "init" => ($lpage==$v ? "selected" : ""));
  }
  $action->lay->setBlockData("BLine", $tl);

  $action->lay->set("sp", $pstart);
  $action->lay->set("lp", $lpage);

  $sfullsearch = (GetHttpVars("sfullsearch", "") == "on" ? true : false);

  $sfam = GetHttpVars("dfam", $action->getParam("USERCARD_FIRSTFAM"));
  $action->lay->set("dfam", $sfam);
  $dnfam = new_Doc($dbaccess, $sfam);
  $action->lay->set("famid", $dnfam->id);
  $action->lay->set("famsearch", ucwords(strtolower($dnfam->title)));
  $dfam = createDoc($dbaccess, $sfam,  false);
  $fattr = $dfam->GetAttributes();

  // Get user configuration
  $pc = $action->getParam("FADDBOOK_MAINCOLS", "");
  $ucols = array();
  if ($pc!="") {
    $tccols = explode("|",  $pc);
    foreach ($tccols as $k => $v) {
      if ($v=="") continue;
      $x = explode("%",$v);
      if ($sfam==$x[0]) $ucols[$x[1]] =  1;
    }
  }

  $orderby = "title";

  $cols = 0;
  $filter = array();
  $td = array();
  $sf = "";
  $clabel = ucwords(strtolower($dnfam->title));
  if (isset($rqi_form["__ititle"]) && $rqi_form["__ititle"]!="" && $rqi_form["__ititle"] != $clabel) {
    if ($sfullsearch) $filter[] = "( title ~* '".$rqi_form["__ititle"]."' ) ";
    else $filter[] = "( title ~* '^".$rqi_form["__ititle"]."' ) ";
    $sf = $rqi_form["__ititle"];
  }
  $td[] = array( "ATTimage" => false, "ATTnormal" => true, "id" => "__ititle", "label" => ($sf==""?$clabel:"$sf"), "filter" => ($sf==""?false:true), "firstCol" => true );
  $cols++;

  $vattr = array();
  foreach ($fattr as $k => $v) {
    if ($v->type!="menu" && $v->type!="frame") {
      if (isset($ucols[$v->id]) && $ucols[$v->id]==1) {
	$sf = "";
	$clabel = ucwords(strtolower($v->labelText));
	$vattr[] = $v;
	$attimage = $attnormal = false;
	switch ($v->type) {
	case "image":  $attimage = true; break;
	default:  $attnormal = true;
	}
	if (isset($rqi_form[$v->id]) && $rqi_form[$v->id]!="" && $rqi_form[$v->id] != $clabel) {
	  $filter[] = "( ".$v->id." ~* '".$rqi_form[$v->id]."' ) ";
	  $sf = $rqi_form[$v->id];
	} 
	$td[] = array( "ATTimage" => $attimage, "ATTnormal" => $attnormal, "id" => $v->id, "label" => ($sf==""?$clabel:"$sf"), "filter" => ($sf==""?false:true), "firstCol" => false);
	$cols++;
      }		    
    }
    $action->lay->SetBlockData("COLS", $td);
  }

  $psearch = $pstart * $lpage;
  $fsearch = $psearch + $lpage + 1;
  $cl = $rq=getChildDoc($dbaccess, 0, $psearch, $fsearch, $filter, $action->user->id, "TABLE", $sfam);
  $dline = array(); $il = 0;
  foreach ($cl as $k => $v) {
    if ($il>=$lpage) continue;
    $dcol = array();
    $ddoc=getDocObject($dbaccess,$v);
    $attchange = ($ddoc->Control("edit") == "" ?  true : false);
    $dcol[] = array( "ATTchange" => false, "ATTname" => "", "content" =>  ucwords(strtolower($v["title"])), "ATTimage" => false, "ATTnormal" => true);
    $pzone = (isset($ddoc->faddbook_card)?$ddoc->faddbook_card:$ddoc->defaultview);
    foreach ($vattr as $ka => $va) 
      {
	$attimage = $attnormal = false;
	switch ($va->type) {
	case "image":  $attimage = true; break;
	default:  $attnormal = true;
	}
	$dcol[] = array( "ATTchange" => false, "content" =>  $ddoc->GetHtmlAttrValue($va->id, "faddbook_blanck", false),
			 "cid" => $v["id"], "ATTimage" => $attimage, "ATTnormal" => $attnormal, "fabzone" => $pzone, "ATTname" => $va->id );
      }
    $action->lay->setBlockData("C$il", $dcol);
    $dline[$il]["cid"] = $v["id"];
    $dline[$il]["fabzone"] = $pzone;
    $dline[$il]["canChange"] = $attchange;
    $dline[$il]["fabzone"] = $pzone;
    $dline[$il]["title"] = ucwords(strtolower($v["title"]));
    $dline[$il]["Line"] = $il;
    $dline[$il]["icop"] = Doc::GetIcon($v["icon"]);
    $il++;
  }
  $action->lay->setBlockData("DLines", $dline);
  $action->lay->set("colspan", ($cols+2));
  
  $action->lay->set("NextPage", false);
  $action->lay->set("PrevPage", false);
  if (count($cl)>$lpage) {
    $action->lay->set("NextPage", true);
    $action->lay->set("pnext", ($pstart+1));
  }
  if ($pstart>0) {
    $action->lay->set("PrevPage", true);
    $action->lay->set("sp", ($pstart-1));
    $action->lay->set("pprev", ($pstart-1));
  }

}


?>
