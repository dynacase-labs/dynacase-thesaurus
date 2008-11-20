<?php
/**
 * Import SKOS thesaurus
 *
 * @author Anakeen 2000 
 * @version $Id: th_skosimport.php,v 1.9 2008/11/20 13:38:13 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage THESAURUS
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("THESAURUS/Lib.Thesaurus.php");
define("MAXIMPORTTIME",600); // 10 minutes
function th_skosimport(&$action) {
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $uri = getHttpVars("thuri");
  $iduri = getHttpVars("_id_thuri");
  $newuri = getHttpVars("newthuri");
  $analyze = (getHttpVars("analyze","yes")=="yes");

  global $_FILES;
  if (ini_get("max_execution_time") < MAXIMPORTTIME) ini_set("max_execution_time",MAXIMPORTTIME);
  $action->lay->set("msg2","");
  if (isset($_FILES["skos"])) {
    $filename=$_FILES["skos"]['name'];
    $skosfile=$_FILES["skos"]['tmp_name'];
  } else {
    $filename=GetHttpVars("skos");
    $skosfile=$filename;
  }

  $doc= new DOMDocument();
  $doc->load($skosfile);
  
  $desc=$doc->childNodes->item(0);

  if ($analyze) { 
    $concepts=$desc->childNodes;

    $action->lay->set("msg",sprintf("%d concepts to import\n",$concepts->length));
    $tr=array();
    for( $j=0 ;  $j < $concepts->length; $j++ )  {       
      $nod=$concepts->item($j);//Node j
      $nodename=strtolower($nod->nodeName);
      if ($nodename=="rdf:description") analyzeSkosConcept($dbaccess,$thid,$nod,$tr);        
    }
    $tul=array();
    foreach ($tr as $k=>$v) {
      $tul[$v['skos:broader']][]=$k;
    }
    //  print_r2($tul);
    $tt=array();
    foreach ($tul as $k=>$v) {
      if ($k) {
	foreach ($v as $kx=>$vx) {
	  //      if (! is_array($tt[$k])) $tt[$k]=array();
	  //print "tt[$k][$vx]<br/>";
	  if (notxy($tt,$k,$vx)) {
	    $tt[]=array($k);
	    $tt[]=array($k,$vx);
	  } else if (noty($tt,$vx)) th_insertafter($tt,$k,$vx);
	  else th_insertbefore($tt,$k,$vx);
	
	}
      }      
    }
    usort($tt,"th_order");
    // print_r2($tr);
    //print '<hr>';
    foreach ($tt as $k=>$v) {
      $id=array_pop($v);
      $tlabel=$tr[$id]['skos:preflabel'];
      $label='';
      if (is_array($tlabel)) foreach ($tlabel as $kl=>$vl) $label.="($kl) $vl - ";
      else $label="--------- NO LABEL --------";
      $tout[]=array("level20"=> (count($v))*20,
		    "level"=> (count($v))*1,
		    "id"=>$id,
		    "title"=>$label);
    }
    $action->lay->setBlockData("TDESC",$tout);
    // print_r2($tt);
  } else {
    if ($iduri) {
      $th=new_doc($dbaccess,$iduri);
      $action->lay->set("msg2", _("UPDATE THESAURUS").' '.$th->title);
    } else {
      if (! $newuri) $newuri=$desc->getAttribute("rdf:about");
      if (! $newuri) $newuri="th_test";
      $th=getThesaurusFromURI($dbaccess,$newuri);
      if (! $th) {
	// create it
	$th=createDoc($dbaccess,"THESAURUS");
	$th->setValue("thes_uri",$newuri);
	$th->name=$newuri;
	$err=$th->Add();
	$action->lay->set("msg2", _("CREATE THESAURUS").' '. $uri);
      }
    }
    $thid=$th->id;  
    $concepts=$desc->childNodes;

    $action->lay->set("msg", sprintf("%d concepts imported\n",$concepts->length));

    for( $j=0 ;  $j < $concepts->length; $j++ )  {       
      $nod=$concepts->item($j);//Node j
      $nodename=strtolower($nod->nodeName);
      if ($nodename=="rdf:description") importSkosConcept($dbaccess,$thid,$nod);        
    }

    // postImport Refreshing
    refreshThConceptFromURI($dbaccess,$thid);
    $th->refreshConcepts();
  }
}
function notxy($t,$x,$y) {
  foreach ($t as $k=>$v) {
    if (in_array($x,$v)) return false;
    if (in_array($y,$v)) return false;
  }
  return true;
}
function noty($t,$y) {
  foreach ($t as $k=>$v) {
    if (in_array($y,$v)) return false;
  }
  return true;
}

// y before x
function th_insertbefore(&$t,$x,$y) {
  foreach ($t as $k=>$v) {
    if (in_array($y,$v)) {
      $t[$k]=array_merge((array)$x,$t[$k] );      
    }
  }
  $t[]=array($x);
}// y after x
function th_insertafter(&$t,$x,$y) {  
  foreach ($t as $k=>$v) {
    if (in_array($x,$v)) {
      $tt1=array();
      foreach ($v as $kv=>$vv) {
	$tt1[]=$vv;
	if ($vv == $x) break;
      }
      $tt1[]=$y;
      $t[]=$tt1;
      break;
    }
  }
}

function th_order($a,$b) {
  $sa=implode("-",$a);
  $sb=implode("-",$b);
  return strcmp($sa,$sb);
}
/**
 * Import a concept
 */
function importSkosConcept($dbaccess,$thid,&$node,$analyze=false) {
  $tcol=array();
  $uri=$node->getAttribute("rdf:about");

  $co=getConceptFromURI($dbaccess,$uri);
  if (! $co) {
    // create it
    $co=createDoc($dbaccess,"THCONCEPT");
    $co->setValue("thc_uri",$uri);
    $co->setValue("thc_thesaurus",$thid);
    $err=$co->Add();
  }
  $ats=$node->childNodes;

  for( $j=0 ;  $j < $ats->length; $j++ )  {   
    $a=$ats->item($j);
    if ($a->nodeType == XML_TEXT_NODE) continue;
    $lang=$a->getAttribute("xml:lang");
    $nodename=strtolower($a->nodeName);
    $nodevalue=utf8_decode($a->nodeValue);

    switch ($nodename) {
    case "rdfs:label":      
      $co->setValue("thc_label",$nodevalue);
      break;
    case "skos:broader":
      $refuri=$a->getAttribute("rdf:resource");
      $co->setValue("thc_uribroader",$refuri);
      break;
    case "skos:narrower":
      break;
    case "skos:related":      
      $refuri=$a->getAttribute("rdf:resource");
      $trel[]=$refuri;
      $co->setValue("thc_urirelated",$trel);
      break;
    default:
      if (preg_match("/skos:(.*)$/",$nodename,$reg)) {
	$aname="thc_".$reg[1];	
	if ($lang) {
	  if (! $tcol[$lang]) {	    
	    $cl=getLangConcept($dbaccess,$co->initid,$lang);
	    if (!$cl) {	      
	      // create it
	     // print "CERATE THLANGCONCEPT <br>\n";
	      $cl=createDoc($dbaccess,"THLANGCONCEPT");
	      $cl->setValue("thcl_lang",$lang);
	      $cl->setValue("thcl_thconcept",$co->initid);
	      $err=$cl->Add();
	    }
	    $tcol[$lang]=$cl;
	  } else {
	    //print "ALREADY SET $lang";
	  }
	  $tcol[$lang]->setValue($aname,$nodevalue);
	} else {
	  $co->setValue($aname,$nodevalue);
	  //	  print "$aname,$nodevalue<br>\n";	  
	}
      }
    }
    //        print "$nodename<br>";
  }
  $err=$co->modify();
  //  $co->postModify();
  foreach ($tcol as $k=>$v) {
    $v->modify();
  }
  return $err;
}

function refreshThConceptFromURI($dbaccess, $thid) {
  
  $s=new SearchDoc($dbaccess,"THCONCEPT");
  $s->addFilter("thc_thesaurus=$thid");
  $s->setObjectReturn();
  $s->search();

  while ($doc=$s->nextDoc()) {
    $doc->refreshFromURI();
    $doc->modify();
  }
}
/**
 * analyze a concept
 */
function analyzeSkosConcept($dbaccess,$thid,&$node,&$tcon) {
  $tcol=array();
  $uri=$node->getAttribute("rdf:about");

  $tcon[$uri]=array();

  $ats=$node->childNodes;

  for( $j=0 ;  $j < $ats->length; $j++ )  {   
    $a=$ats->item($j);
    if ($a->nodeType == XML_TEXT_NODE) continue;
    $lang=$a->getAttribute("xml:lang");
    $nodename=strtolower($a->nodeName);
    $nodevalue=utf8_decode($a->nodeValue);

    switch ($nodename) {
    case "rdfs:label":      
      $tcon[$uri][$nodename]=$nodevalue;
      break;
    case "skos:broader":
      $refuri=$a->getAttribute("rdf:resource");
      $tcon[$uri][$nodename]=$refuri;
      break;
    case "skos:narrower":
      break;
    case "skos:related":      
      $refuri=$a->getAttribute("rdf:resource");
      $tcon[$uri][$nodename][]=$refuri;
      break;
    default:
      if (preg_match("/skos:(.*)$/",$nodename,$reg)) {
	$aname="thc_".$reg[1];	
	if ($lang) {
	  $tcon[$uri][$nodename][$lang]=$nodevalue;
	 
	} else {
	  $tcon[$uri][$nodename][$lang]=$nodevalue;
	}
      }
    }
  }
  
  return $err;
}

?>