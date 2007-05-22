<?php
/**
 * Full Text Search document
 *
 * @author Anakeen 2007
 * @version $Id: fullsearch.php,v 1.8 2007/05/22 16:05:29 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */




include_once("FDL/Lib.Dir.php");
include_once("FDL/Class.DocSearch.php");

include_once("FDL/freedom_util.php");  





/**
 * Fulltext Search document 
 * @param Action &$action current action
 * @global keyword Http var : word to search in any values
 * @global famid Http var : restrict to this family identioficator
 * @global start Http var : page number 
 */
function fullsearch(&$action) {

  $famid=GetHttpVars("famid",0);
  $keyword=GetHttpVars("_se_key",GetHttpVars("keyword")); // keyword to search
  $target=GetHttpVars("target"); // target window when click on document
  $start=GetHttpVars("start",0); // page number

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/resizeimg.js");

  $dbaccess = $action->GetParam("FREEDOM_DB");

  if ($keyword=="") {

    $action->lay = new Layout(getLayoutFile("FREEDOM","fullsearch_empty.xml"),$action);
    return;
  } else {    
    DocSearch::getFullSqlFilters($keyword,$sqlfilters,$orderby,$keys);
    $slice=10;
    $tdocs=getChildDoc($dbaccess, 0, $start,$slice,$sqlfilters,$action->user->id,"TABLE",$famid,false,$orderby);

    $workdoc=new Doc($dbaccess);
    if ($famid) $famtitle=$workdoc->getTitle($famid);
    else $famtitle="";
    $dbid=getDbid($dbaccess);
    foreach ($tdocs as $k=>$tdoc) {
      $tdocs[$k]["htext"]=nl2br(wordwrap(nobr(highlight_text($dbid,$tdoc["values"],$keys),80)));
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
    $action->lay->set("resulttext",sprintf(_("Results <b>%d</b> - <b>%d</b> for <b>%s</b> %s"),((count($tdocs)+$start)==0)?0:$start+1,$start+count($tdocs),$keyword,$famtitle));
    $action->lay->set("key",str_replace("\"","&quot;",$keyword));
    $action->lay->setBlockData("DOCS",$tdocs);
  }



/**
 * return part of text where are found keywords
 * Due to unaccent fulltext vectorisation need to transpose original text with highlight text done by headline tsearch2 sql function
 * @param resource $dbid database access
 * @param string original text
 * @param string keywords
 * @return string HTML text with <b> tags
 */
function highlight_text($dbid,&$s,$k) {

  if (strlen($s) > 100000) {
    $headline=_("document too big : no highlight");
  } else {
    $s=strtr($s, "£", " ");


    $result = pg_query($dbid,"select headline('fr','".pg_escape_string(unaccent($s))."',to_tsquery('fr','$k'))");
    //  print "select headline('fr','$s',to_tsquery('fr','$k'))";
    if (pg_numrows ($result) > 0) {
      $arr = pg_fetch_array ($result, 0,PGSQL_ASSOC);
      $headline= $arr["headline"];
    }



    $pos=strpos($headline,'<b>');

  
    //    print "<hr> POSBEG:".$pos;
    if ($pos !== false) {
      $sw=(str_replace(array("<b>","</b>","  "),array('','',' '),$headline));
      $offset=strpos(unaccent($s),$sw);

      //if (! $offset)   print "\n<hr> SEARCH:[$sw] in [".unaccent($s)."]\n";
      //    print "<br> OFFSET:".$offset;
      $before=20; // 20 characters before;
      if (($pos+$offset) < $before) $p0=0;
      else $p0=$pos+$offset-$before;
      $h=substr($s,$p0,$pos+$offset-$p0); // begin of text
      $possp=strpos($h,' ');
      if ($possp > 0) $h=substr($h,$possp); // first word

      $pe=strpos($headline,'</b>',$pos);    
      
      if ($pe > 0) {
	$h.="<b>";
	$h.=substr($s,$pos+$offset,$pe-$pos-3);	
	$h.="</b>";
      }
      //      print "<br> POS:$pos [ $pos : $pe ]";
      $pos=$pe+1;
      $i=1;
      // 7 is strlen('<b></b>');

      while ($pe>0) {
	$pb=strpos($headline,'<b>',$pos);   
	$pe=strpos($headline,'</b>',$pos);
	//	print "<br> POS:$pos [ $pb : $pe ]";
	if (($pe)&&($pb<$pe)) {
	  $pb--;
	  $pe; //
	  $h.=substr($s,$pos-4-(7*($i-1))+$offset,$pb-$pos-3);
	  $h.="<b>";
	  $h.=substr($s,$pb-(7*$i)+$offset,$pe-$pb-3);
	  $h.="</b>";
	  $pos=$pe+1;
	  $i++;
	} else {
	  $cur=$pos-(7*$i)+3+$offset;
	  if (($cur-$offset) > 150) $pend=30;
	  else $pend=180-$cur+$offset;
	  $send=substr($s,$cur,$pend);
	  $possp=strrpos($send,' ');
	  $send=substr($send,0,$possp);
	  $pe=0;
	  $h.=$send;
	  //  print "<br> POSEND: $cur $pend";
	}
	
      }
      //print "<br>[$headline]";
	
      return $h;

    }
    
  }
  return $headline;   
}
function nobr($text)
{
  return  strtr(preg_replace('/<br\\s*?\/??>/i', '', $text),"\n\t£","  -");
}
?>