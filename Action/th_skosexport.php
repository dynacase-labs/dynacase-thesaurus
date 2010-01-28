<?php
/**
 * Export SKOS thesaurus
 *
 * @author Anakeen 2008
 * @version $Id: th_skosexport.php,v 1.1 2008/09/15 16:08:49 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage THESAURUS
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("FDL/Class.SearchDoc.php");
include_once("THESAURUS/Lib.Thesaurus.php");

function th_skosexport(&$action) {
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $uri = getHttpVars("thuri");
  $docid = getHttpVars("id");

  if (ini_get("max_execution_time") < 180) ini_set("max_execution_time",180); // 3 minutes
   header('Content-type: text/xml; charset=utf-8');
  $action->lay->setEncoding("utf-8");
  $action->lay->set("thuri","none");
  $doc=new_doc($dbaccess, $docid);
  $concepts='';
  if ($doc->isAlive()) {
    $action->lay->set("thuri",$doc->getValue("thes_uri"));
    $docid=$doc->id;


    $s=new SearchDoc($dbaccess,"THCONCEPT");
    $s->addFilter("thc_thesaurus='".$docid."'");
    $t=$s->search();
    $tsa=array();
    foreach ($t as $k=>$th) {



      $concepts.=exportSkosConcept($dbaccess,$th);
    }
  }
  $action->lay->set("concepts",$concepts);
}


function exportSkosConcept($dbaccess,$th) {
  $thlay=new Layout(getLayoutFile("THESAURUS","th_skosconcept.xml"));
  $tsa=array();
  $tsal=array();
  foreach ($th as $k=>$v) {
    $satag=fdl2skos_label($k);
    $multiple=false;;
    if ($satag=='altLabel') $multiple=true;
    if ($multiple) {
      $vs=Doc::_val2array($vl);
      foreach ($vs as $vvs) $tsal[]=array("satag"=>$satag,
					  "saval"=>xml_entity_encode($vvs));
    } else {

      if ($satag && ($v!='')) {
	$tsa[]=array("satag"=>$satag,
		     "saval"=>xml_entity_encode($v));
      }
    }
  }  

  $tbroaders=array();
  if ($th["thc_uribroader"]) {
    $tbr=Doc::_val2array($th["thc_uribroader"]);
    foreach ($tbr as $br) $tbroaders[]=array("broader"=>$br);
  }
  if ($th["thc_idlang"]) {
    $tl=Doc::_val2array($th["thc_idlang"]);

    foreach ($tl as $l) {
      $dl=getTDoc($dbaccess,$l);
      if ($dl) {
	$lang=$dl["thcl_lang"];	

	foreach ($dl as $kl=>$vl) {
	  $satag=fdl2skos_label($kl);
	  if ($satag && ($vl!='')) {
	    $multiple=false;;
	    if ($satag=='altLabel') $multiple=true;
	    if ($multiple) {
	      $vs=Doc::_val2array($vl);
	      foreach ($vs as $vvs) $tsal[]=array("saltag"=>$satag,
						  "sallang"=>$lang,
						  "salval"=>xml_entity_encode($vvs));
	    } else {
	      $tsal[]=array("saltag"=>$satag,
			    "sallang"=>$lang,
			    "salval"=>xml_entity_encode($vl));
	    }
	  }
	}
      }
    }
  }


  $thlay->set("code",$th["thc_label"]);
  $thlay->set("uri",$th["thc_uri"]);
  $thlay->setBlockData("broaders",$tbroaders);
  $thlay->setBlockData("sa",$tsa);
  $thlay->setBlockData("sal",$tsal);
  return $thlay->gen();
}

function fdl2skos_label($l) {
  $satag='';
  switch ($l) {
    case 'thc_altlabel':
      $satag='altLabel';
      break;
    case 'thc_preflabel':
      $satag='prefLabel';
      break;
    case 'thc_definition':
      $satag='definition';
      break;
    case 'thc_editorialnote':
      $satag='editorialNote';
      break;
    case 'thc_example':
      $satag='example';
      break;
    case 'thc_historynote':
      $satag='historyNote';
      break;
    case 'thc_symbol':
      $satag='symbol';
      break;
    case 'thc_scopenote':
      $satag='scopeNote';
      break;
    case 'thc_note':
      $satag='note';
      break;
      
    }
  return $satag;
}

?>