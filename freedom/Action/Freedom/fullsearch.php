<?php
/**
 * Full Text Search document
 *
 * @author Anakeen 2007
 * @version $Id: fullsearch.php,v 1.1 2007/04/26 10:06:47 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */




include_once("FDL/Lib.Dir.php");

include_once("FDL/freedom_util.php");  





/**
 * Fulltext Search document 
 * @param Action &$action current action
 * @global keyword Http var : word to search in any values
 * @global famid Http var : restrict to this family identioficator
 * @global viewone Http var : (Y|N) if Y direct view document detail if only one returned
 * @global view Http var : display mode : icon|column|list
 */
function fullsearch(&$action) {

  $famid=GetHttpVars("famid",0);
  $keyword=GetHttpVars("_se_key",GetHttpVars("keyword")); // keyword to search
  $viewone=GetHttpVars("viewone"); // direct view if only one Y|N
  $target=GetHttpVars("target"); // target window when click on document
  $view=GetHttpVars("view"); // display mode : icon|column|list
  $start=GetHttpVars("start",0); // page number

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/resizeimg.js");

  $dbaccess = $action->GetParam("FREEDOM_DB");

  if ($keyword=="") {

    $action->lay = new Layout(getLayoutFile("FREEDOM","fullsearch_empty.xml"),$action);
    return;
  } else {
    $pspell_link = pspell_new("fr","","","iso8859-1",PSPELL_FAST);
    $tkeys=explode(" ",$keyword);
    foreach ($tkeys as $k=>$key) {
      if (!pspell_check($pspell_link, $key)) {
	$suggestions = pspell_suggest($pspell_link, $key);
	$sug=$suggestions[0];
	//foreach ($suggestions as $k=>$suggestion) {  echo "$k : $suggestion\n";  }
	if ($sug) $tkeys[$k]="$key|$sug";
      }    
    }
  }


  $keys='('.implode(")&(",$tkeys).')';
  
  $slice=10;

  $keys=pg_escape_string($keys);
  $sqlfilters[]="fulltext @@ to_tsquery('fr','$keys') ";
  $orderby="rank(fulltext,to_tsquery('fr','$keys')) desc";
  $tdocs=getChildDoc($dbaccess, 0, $start,$slice,$sqlfilters,$action->user->id,"TABLE",$famid,false,$orderby);

  $workdoc=new Doc($dbaccess);
  if ($famid) $famtitle=$workdoc->getTitle($famid);
  else $famtitle="";
  $dbid=getDbid($dbaccess);
  foreach ($tdocs as $k=>$tdoc) {
    $tdocs[$k]["htext"]=highlight_text($dbid,$tdoc["values"],$keys);
    $tdocs[$k]["iconsrc"]=$workdoc->getIcon($tdoc["icon"]);
    $tdocs[$k]["mdate"]=strftime("%a %d %b %Y",$tdoc["revdate"]);
  }

  if ($start > 0) {
    for ($i=0;$i<$start;$i+=$slice) {
      $tpages[]=array("xpage"=>$i/$slice+1,
		      "xstart"=>$i);
    }    
    
    $action->lay->setBlockData("PAGES",$tpages);
  }



  $tclassdoc=GetClassesDoc($dbaccess, $action->user->id,array(1,2),"TABLE");


  foreach ($tclassdoc as $k=>$cdoc) {
    $selectclass[$k]["idcdoc"]=$cdoc["initid"];
    $selectclass[$k]["classname"]=$cdoc["title"];
    $selectclass[$k]["famselect"]=($cdoc["initid"]==$famid)?"selected":"";
  }  
  $action->lay->SetBlockData("SELECTCLASS", $selectclass);

  $action->lay->set("notfirst",($start!=0));
  $action->lay->set("notthenend",count($tdocs) >= $slice);
  $action->lay->set("start",$start);
  $action->lay->set("cpage",$start/$slice+1);
  $action->lay->set("nstart",$start+$slice);
  $action->lay->set("pstart",$start-$slice);
  $action->lay->set("searchtitle",sprintf(_("Search %s"),$keyword));
  $action->lay->set("resulttext",sprintf(_("Results <b>%d</b> - <b>%d</b> for <b>%s</b> %s"),$start+1,$start+$slice,$keyword,$famtitle));
  $action->lay->set("key",$keyword);
  $action->lay->setBlockData("DOCS",$tdocs);
}

function highlight_text($dbid,&$s,$k) {

  if (strlen($s) > 100000) {
    $headline=_("document too big : no highlight");
  } else {
    $s=pg_escape_string($s);

    $result = pg_query($dbid,"select headline('fr','$s',to_tsquery('fr','$k'))");
    //  print "select headline('fr','$s',to_tsquery('fr','$k'))";
    if (pg_numrows ($result) > 0) {
      $arr = pg_fetch_array ($result, 0,PGSQL_ASSOC);
      $headline= $arr["headline"];
    }
  }
  return $headline;   
}

?>