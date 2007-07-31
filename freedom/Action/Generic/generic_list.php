<?php
/**
 * View set of documents of same family
 *
 * @author Anakeen 2000 
 * @version $Id: generic_list.php,v 1.33 2007/07/31 13:49:44 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */




include_once("FDL/viewfolder.php");
include_once("GENERIC/generic_util.php");



function generic_list(&$action) {
  // Set the globals elements
  // Get all the params      
  $dirid=GetHttpVars("dirid"); // directory to see
  $catgid=GetHttpVars("catg", $dirid); // category
  $startpage=GetHttpVars("page","0"); // page to see
  $tab=GetHttpVars("tab","0"); // tab to see 1 for ABC, 2 for DEF, ...
  $onglet=GetHttpVars("onglet"); // if you want onglet
  $famid=GetHttpVars("famid"); // family restriction
  $clearkey=(GetHttpVars("clearkey","N")=="Y"); // delete last user key search
  setHttpVar("target","finfo$famid" );
  if (!($famid > 0)) $famid = getDefFam($action);

  $column=generic_viewmode($action,$famid); // choose the good view mode

  if ($onglet) {
    $wonglet=($onglet!='Y');
  } else {
    $wonglet=(getTabLetter($action,$famid)=='Y');
  }


  $dbaccess = $action->GetParam("FREEDOM_DB");
  if ($dirid) {
    $dir = new_Doc($dbaccess,$dirid);
    $action->lay->Set("pds",$dir->urlWhatEncodeSpec(""));
    $action->lay->Set("fldtitle",$dir->getTitle());
  } else {    
    $action->lay->Set("fldtitle",_("precise search"));
    $action->lay->Set("pds","");
  }

  $action->lay->Set("dirid",$dirid);
  $action->lay->Set("tab",$tab);
  $action->lay->Set("catg",$catgid);
  $action->lay->Set("famid",$famid);
  $mode=getSearchMode($action,$famid);
  $action->lay->Set("FULLMODE",($mode=="FULL"));
  $slice = $action->GetParam("CARD_SLICE_LIST",5);
  //  $action->lay->Set("next",$start+$slice);
  //$action->lay->Set("prev",$start-$slice);

  $action->lay->Set("nexticon",""); 
  $action->lay->Set("previcon",""); 


  $aorder = getDefUSort($action,""); 
  setHttpVar("sqlorder",$aorder);
  if ($famid > 0) {
    if ($aorder != "title") { // test if attribute order exist
      $ndoc = createDoc($dbaccess, $famid,false);
      if ($aorder[0]=="-") $aorder=substr($aorder,1);
      if (in_array($aorder,$ndoc->fields))    setHttpVar("sqlorder",getDefUSort($action,"") );
    }
  }
  getFamilySearches($action,$dbaccess,$famid);
  if ($dirid) {
    
    $only=(getInherit($action,$famid)=="N");
    if (viewfolder($action, true, false,$column,$slice,array(),($only)?-(abs($famid)):abs($famid)) == $slice) {
      // can see next
      $action->lay->Set("nexticon",$action->GetIcon("next.png",N_("next"),16)); 
    }
  }
  if ($startpage > 0) {
    // can see prev
    $action->lay->Set("previcon",$action->GetIcon("prev.png",N_("prev"),16)); 
  }
  
  if ($dirid && $wonglet) {
    // hightlight the selected part (ABC, DEF, ...)
    $onglet=array(array("onglabel"=> "A B C",
			"ongdir" => "1"),
		  array("onglabel"=> "D E F",
			"ongdir" => "2"),
		  array("onglabel"=> "G H I",
			"ongdir" => "3"),
		  array("onglabel"=> "J K L",
			"ongdir" => "4"),
		  array("onglabel"=> "M N O",
			"ongdir" => "5"),
		  array("onglabel"=> "P Q R S",
			"ongdir" => "6"),
		  array("onglabel"=> "T U V",
			"ongdir" => "7"),
		  array("onglabel"=> "W X Y Z",
			"ongdir" => "8"),
		  array("onglabel"=> "A - Z",
			"ongdir" => "0"));

  
    while (list($k, $v) = each ($onglet)) {
      if ($v["ongdir"] == $tab) $onglet[$k]["ongclass"]="onglets";
      else $onglet[$k]["ongclass"]="onglet";
      $onglet[$k]["onglabel"] = str_replace(" ","<BR>",$v["onglabel"]);
    }

    $action->lay->SetBlockData("ONGLET", $onglet);
  }
  
  $action->lay->Set("onglet", $wonglet?"Y":"N");

  if ($clearkey)  $action->parent->param->Set("GENE_LATESTTXTSEARCH",
					      setUkey($action,$famid,$keyword),PARAM_USER.$action->user->id,
					      $action->parent->id);
  $action->lay->set("tkey",getDefUKey($action));
}



function generic_viewmode(&$action,$famid) {
    $prefview = getHttpVars("gview");

  $tmode= explode(",",$action->getParam("GENE_VIEWMODE"));

  // explode parameters
  while (list($k,$v) = each($tmode)) {
    list($fid,$vmode)=explode("|",$v);
    $tview[$fid]=$vmode;
  }
  switch ($prefview) {
  case "column":  
  case "abstract":
    $tview[$famid]=$prefview;
    // implode parameters to change user preferences
    $tmode=array();
    while (list($k,$v) = each($tview)) {
      if ($k>0) $tmode[]="$k|$v";
    }
    $pmode=implode(",",$tmode);
    $action->parent->param->Set("GENE_VIEWMODE",$pmode,PARAM_USER.$action->user->id,$action->parent->id);

    break;
    
  }

  switch ($tview[$famid]) {
  case "column":
    $action->layout = $action->GetLayoutFile("generic_listv.xml");
    $action->lay = new Layout($action->layout,$action);
    //    $column=true;
    $column=2;
    break;
  
  case "abstract":

  default:
    $action->layout = $action->GetLayoutFile("generic_list.xml");
    $action->lay = new Layout($action->layout,$action);
    $column=false;
 
    break;
    
  }
  return $column;
}

function getFamilySearches($action,$dbaccess,$famid) {

  // search searches in primary folder
  $fdoc = new_Doc($dbaccess,$famid);
  $dirid=GetHttpVars("dirid"); // search
  $catgid=GetHttpVars("catg", $dirid); // primary directory
  if ($catgid==0) $catgid=$dirid;
  if ($fdoc->dfldid > 0) {
    $homefld = new_Doc( $dbaccess, $fdoc->dfldid);
    $stree=array();
    if ($homefld->id > 0)   $stree=getChildDoc($dbaccess,$homefld->id,"0","ALL",array(),$action->user->id,"TABLE",5);  

    $streeSearch = array();
    foreach($stree as $k=>$v) {
      if (($v["doctype"] == "S" )&&($v["fromid"] != $fdoc->id) ) {
	$streeSearch[$v["id"]] = $v;
	$streeSearch[$v["id"]]["selected"]=($v["id"]==$catgid)?"selected":"";
      } 
    }
  }

  $action->lay->set("ONESEARCH",(count($streeSearch)>0));
  
  $action->lay->SetBlockData("SYSSEARCH",$streeSearch);
  // search user searches for family
  $filter[]="owner=".$action->user->id;
  $filter[]="se_famid='$famid'";
  $filter[]="usefor!='G'";
  $filter[]="se_memo='yes'";
  $action->lay->set("MSEARCH",false);
  $stree=getChildDoc($dbaccess,"0","0","ALL",$filter,$action->user->id,"TABLE",5);
  $streeSearch = array();
  foreach ($stree as $k=>$v) {
    if (!isset($streeSearch[$v["id"]]))    $streeSearch [$v["id"]] = $v;
    $streeSearch[$v["id"]]["selected"]=($v["id"]==$catgid)?"selected":"";
  }
  $action->lay->set("MSEARCH",(count($stree) > 0));
  $action->lay->SetBlockData("USERSEARCH",$streeSearch);
  if (count($streeSearch)>0) $action->lay->set("ONESEARCH",true);
  

  
}

?>
