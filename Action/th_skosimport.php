<?php
/**
 * Import SKOS thesaurus
 *
 * @author Anakeen 2000 
 * @version $Id: th_skosimport.php,v 1.1 2008/08/06 15:11:52 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage THESAURUS
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("THESAURUS/Lib.Thesaurus.php");

function th_skosimport(&$action) {
  
  $dbaccess = $action->GetParam("FREEDOM_DB");

  global $_FILES;
  if (ini_get("max_execution_time") < 180) ini_set("max_execution_time",180); // 3 minutes
  
  if (isset($_FILES["skos"])) {
    $filename=$_FILES["skos"]['name'];
    $skosfile=$_FILES["skos"]['tmp_name'];
  } else {
    $filename=GetHttpVars("skos");
    $skosfile=$filename;
  }

  print_r2($_FILES);


  $doc= new DOMDocument();
  $doc->load($skosfile);
  
  $desc=$doc->childNodes->item(0);
  $uri=$desc->getAttribute("rdf:about");
  if (! $uri) $uri="test";
  $th=getThesaurusFromURI($dbaccess,$uri);
  if (! $th) {
    // create it
    $th=createDoc($dbaccess,"THESAURUS");
    $th->setValue("thes_uri",$uri);
    $err=$th->Add();
    print "CREATE THEASURUS $uri<br>\n";
  }
  $thid=$th->id;
  

  $concepts=$desc->childNodes;

  print $concepts->length;

  for( $j=0 ;  $j < $concepts->length; $j++ )  {       
      $nod=$concepts->item($j);//Node j
      $nodename=strtolower($nod->nodeName);
      if ($nodename=="rdf:description") importSkosConcept($dbaccess,$thid,$nod);
      //      $nodevalue=$nod->nodeValue;
        print "$j ) $nodemane<br/>\n";
        
    }
}


function importSkosConcept($dbaccess,$thid,&$node) {
  $tcol=array();
  $uri=$node->getAttribute("rdf:about");
  print "URI:$uri<br/>\n";

  $co=getConceptFromURI($dbaccess,$uri);
  if (! $co) {
    // create it
    $co=createDoc($dbaccess,"THCONCEPT");
    $co->setValue("thc_uri",$uri);
    $co->setValue("thc_thesaurus",$thid);
    $err=$co->Add();
  }
  $ats=$node->childNodes;
  print "Attr#:".$ats->length;
  for( $j=0 ;  $j < $ats->length; $j++ )  {   
    $a=$ats->item($j);
    if ($a->nodeType == XML_TEXT_NODE) continue;
    $lang=$a->getAttribute("xml:lang");
    $nodename=strtolower($a->nodeName);
    $nodevalue=utf8_decode($a->nodeValue);

    switch ($nodename) {
    case "skos:broader":
      $refuri=$a->getAttribute("rdf:resource");
      // $ref=getConceptIdsFromURI($dbaccess,$refuri);
      //$co->setValue("thc_broader",$ref);
      $co->setValue("thc_uribroader",$refuri);
      //      print "broader $refuri : $ref<br>\n";
      break;

    case "skos:narrower":
      break;
    case "skos:related":      
      $refuri=$a->getAttribute("rdf:resource");
      //      $ref=getConceptIdsFromURI($dbaccess,$refuri);
      //$co->setValue("thc_related",$ref);
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
	      print "CERATE THLANGCONCEPT <br>\n";
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

  
}
?>