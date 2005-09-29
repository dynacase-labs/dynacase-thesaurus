<?php
/**
 * Freedom Address Book
 *
 * @author Anakeen 2000
 * @version $Id: faddbook_main.php,v 1.2 2005/09/29 16:29:12 marc Exp $
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
  global $_GET,$_POST,$ZONE_ARGS;

  $rqi_form = array();
  foreach ($_POST as $k => $v) {
    if (substr($k,0,4)=="rqi_") $rqi_form[substr($k,4)] = $v;
  }

  $dbaccess = $action->getParam("FREEDOM_DB");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");

  $pstart = GetHttpVars("sp", 0);

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


  $dfam = createDoc($dbaccess, "USER",  false);
  $fattr = $dfam->GetAttributes();

  // Get user configuration
  $pc = $action->getParam("FADDBOOK_MAINCOLS", "");
  $ucols = array();
  if ($pc!="") {
    $tccols = explode("|",  $pc);
    foreach ($tccols as $k => $v) {
      if ($v=="") continue;
      $x = explode("%",$v);
      $ucols[$x[0]] = array( "l" => $x[1], "r" => $x[2] );
    }
  }
    

  $orderby = "title";

  $cols = 0;
  $filter = array();
  $td = array();
  $sf = "";
  $clabel = ucwords(strtolower("personne"));
  if (isset($rqi_form["__ititle"]) && $rqi_form["__ititle"]!="" && $rqi_form["__ititle"] != $clabel) {
    $filter[] = "( title ~* '".$rqi_form["__ititle"]."' ) ";
    $sf = $rqi_form["__ititle"];
  }
  $td[] = array( "id" => "__ititle", "label" => ($sf==""?$clabel:"$sf"), "filter" => ($sf==""?false:true) );
  $cols++;

  $vattr = array();
  foreach ($fattr as $k => $v) {
    if ($v->type!="menu" && $v->type!="frame") {
      if (isset($ucols[$v->id]) && isset($ucols[$v->id]["l"]) && $ucols[$v->id]["l"]==1) {
	$sf = "";
	$clabel = ucwords(strtolower($v->labelText));
	$vattr[] = $v->id;
	if (isset($rqi_form[$v->id]) && $rqi_form[$v->id]!="" && $rqi_form[$v->id] != $clabel) {
	  $filter[] = "( ".$v->id." ~* '".$rqi_form[$v->id]."' ) ";
	  $sf = $rqi_form[$v->id];
	} 
	$td[] = array( "id" => $v->id, "label" => ($sf==""?$clabel:"$sf"), "filter" => ($sf==""?false:true) );
	$cols++;
      }		    
    }
    $action->lay->SetBlockData("COLS", $td);
  }
//  getChildDoc  (string $dbaccess, array $dirid, [string $start = "0"], [string $slice = "ALL"], [array $sqlfilters = array()], [int $userid = 1], [string $qtype = "LIST"], [int $fromid = ""], [bool $distinct = false], [string $orderby = "title"], [bool $latest = true])

  $psearch = $pstart * $lpage;
  $fsearch = $psearch + $lpage + 1;
  $cl = $rq=getChildDoc($dbaccess, 0, $psearch, $fsearch, $filter, $action->user->id, "TABLE", "USER", true);
  $dline = array(); $il = 0;
  foreach ($cl as $k => $v) {
    if ($il>=$lpage) continue;
    $dcol = array();
    $dcol[] = array( "content" =>  ucwords(strtolower($v["title"])));
    foreach ($vattr as $ka => $va) $dcol[] = array( "content" => $v[$va], "cid" => $v["id"]);
    $dline[$il]["cid"] = $v["id"];
    $dline[$il]["title"] = ucwords(strtolower($v["title"]));
    $dline[$il]["Line"] = $il;
    $dline[$il]["icop"] = Doc::GetIcon($v["icon"]);
    $action->lay->setBlockData("C$il", $dcol);
    $il++;
  }
  $action->lay->setBlockData("DLines", $dline);
  $action->lay->set("colspan", $cols+1);
  
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
