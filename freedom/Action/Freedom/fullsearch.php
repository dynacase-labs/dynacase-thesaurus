<?php
/**
 * Full Text Search document
 *
 * @author Anakeen 2007
 * @version $Id: fullsearch.php,v 1.15 2007/08/14 17:49:37 eric Exp $
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
 * @global dirid Http var : search identificator
 */
function fullsearch(&$action) {

  $famid=GetHttpVars("famid",0);
  $keyword=GetHttpVars("_se_key",GetHttpVars("keyword")); // keyword to search
  $target=GetHttpVars("target"); // target window when click on document
  $start=GetHttpVars("start",0); // page number
  $dirid=GetHttpVars("dirid",0); // special search

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/resizeimg.js");

  $dbaccess = $action->GetParam("FREEDOM_DB");

  if (($keyword=="")&&($dirid==0)) {

    $action->lay = new Layout(getLayoutFile("FREEDOM","fullsearch_empty.xml"),$action);
    return;
  } else {    
    $sqlfilters=array();
    if ($keywords) {
      if ($keyword[0]=='~') {
	$sqlfilters[]="svalues ~* '".pg_escape_string(substr($keyword,1))."'";
      } else {
	DocSearch::getFullSqlFilters($keyword,$sqlfilters,$orderby,$keys);
      }
    }
    $slice=10;
    $tdocs=getChildDoc($dbaccess, $dirid, $start,$slice,$sqlfilters,$action->user->id,"TABLE",$famid,false,$orderby);

    $workdoc=new Doc($dbaccess);
    if ($famid) $famtitle=$workdoc->getTitle($famid);
    else $famtitle="";
    $dbid=getDbid($dbaccess);
    foreach ($tdocs as $k=>$tdoc) {
      $tdoc["values"].=getFileTxt($dbid,$tdoc);
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
    $action->lay->set("viewform",($dirid == ""));
    $action->lay->set("dirid",$dirid);
    if ($dirid != "") {
      $sdoc=new_doc($dbaccess,$dirid);
      $action->lay->set("searchtitle",$sdoc->title);    
      $action->lay->set("dirid",$sdoc->id);        
    }
  }

/**
 * return file text values from  _txt column
 */
function getFileTxt($dbid,&$tdoc) {
 
  $sqlselect='svalues';
  $sqlfrom='doc'.$tdoc["fromid"];
  $sqlwhere='id='.$tdoc["id"];

  $result = pg_query($dbid,"select $sqlselect from $sqlfrom where $sqlwhere ;");
  //  print "select headline('fr','$s',to_tsquery('fr','$k'))";
  if (pg_numrows ($result) > 0) {
    $arr = pg_fetch_array ($result, 0,PGSQL_ASSOC);    
    return implode(' - ',$arr);
  }
  
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

  if ((strlen($s)/1024) > getParam("FULLTEXT_HIGHTLIGHTSIZE",200)) {
    $headline=sprintf(_("document too big (%dKo): no highlight"),(strlen($s)/1024));
  } else {
    $s=strtr($s, "£", " ");

    $result = pg_query($dbid,"select headline('fr','".pg_escape_string(unaccent($s))."',to_tsquery('fr','$k'))");
    //  print "select headline('fr','$s',to_tsquery('fr','$k'))";
    if (pg_numrows ($result) > 0) {
      $arr = pg_fetch_array ($result, 0,PGSQL_ASSOC);
      $headline= $arr["headline"];
    }

    // $headline=str_replace('  ',' ',$headline);
    $headline=preg_replace('/[ ]+ /', ' ',$headline);
    $headline=str_replace(array(" \r","\n ","æ","Æ"),array('',"\n",'ae','AE'),$headline);
    $pos=strpos($headline,'<b>');

  
    //    print "<hr> POSBEG:".$pos;
    if ($pos !== false) {
      // OE not in iso8859-1
      $sw=(str_replace(array("<b>","</b>"),array('',''),$headline));
      $s=preg_replace('/[ ]+ /', ' ',$s);
      $s=preg_replace('/<[a-z][^>]+>/', '',$s);
      $s=str_replace(array("æ","Æ","<br />","\r"),array('ae','AE','',''),$s);
      $offset=strpos(unaccent($s),$sw);
    
      if ($offset===false) return $headline; // case mismatch in characters

      /*  if (! $offset)   print "\n<hr> SEARCH:[$sw] in [".unaccent($s)."]\n";
       print "<br> OFFSET:".$offset."--".substr($s,$offset,10)."--".substr(unaccent($s),$offset,10);
       print "<br>\nS[".str_replace(array(" ","\n","\r"),array(".","-CR-","-LF-"),unaccent($s))."]\n";
       print "W[".str_replace(array(" ","\n","\r"),array(".","-CR-","-LF-"),$sw)."]\n\n";
       print "H[".str_replace(array(" ","\n","\r"),array(".","-CR-","-LF-"),$headline)."]\n<br>\n";
      // print "\n[$s]\n";
      //print "[".unaccent($s)."]\n";*/

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