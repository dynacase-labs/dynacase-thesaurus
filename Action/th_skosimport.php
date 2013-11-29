<?php
/*
 * Import SKOS thesaurus
 *
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package THESAURUS
*/

include_once ("FDL/Class.Doc.php");
include_once ("THESAURUS/Lib.Thesaurus.php");
define("MAXIMPORTTIME", 600); // 10 minutes
function th_skosimport(Action & $action)
{
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $uri = getHttpVars("thuri");
    $iduri = getHttpVars("_id_thuri");
    $newuri = getHttpVars("newthuri");
    $analyze = (getHttpVars("analyze", "yes") == "yes");
    
    global $_FILES;
    setMaxExecutionTimeTo(MAXIMPORTTIME);
    $action->lay->set("msg2", "");
    if (isset($_FILES["skos"])) {
        $skosfile = $_FILES["skos"]['tmp_name'];
    } else {
        $skosfile = GetHttpVars("skos");
    }
    
    $doc = new DOMDocument();
    $doc->load($skosfile);
    /**
     * @var DOMElement $desc
     */
    $desc = $doc->childNodes->item(0);
    
    if ($analyze) {
        $concepts = $desc->childNodes;
        
        $action->lay->set("msg", sprintf("%d concepts to import\n", $concepts->length));
        $tr = array();
        for ($j = 0; $j < $concepts->length; $j++) {
            $nod = $concepts->item($j); //Node j
            $nodename = strtolower($nod->nodeName);
            if (($nodename == "rdf:description") || ($nodename == "skos:concept")) {
                analyzeSkosConcept($dbaccess, null, $nod, $tr);
            }
        }
        $tul = array();
        foreach ($tr as $k => $v) {
            if (!empty($v['skos:broader'])) {
                $tul[$v['skos:broader']][] = $k;
            }
        }
        //  print_r2($tul);
        $tt = array();
        foreach ($tul as $k => $v) {
            if ($k) {
                foreach ($v as $vx) {
                    //      if (! is_array($tt[$k])) $tt[$k]=array();
                    //print "tt[$k][$vx]<br/>";
                    if (notxy($tt, $k, $vx)) {
                        $tt[] = array(
                            $k
                        );
                        $tt[] = array(
                            $k,
                            $vx
                        );
                    } else if (noty($tt, $vx)) th_insertafter($tt, $k, $vx);
                    else th_insertbefore($tt, $k, $vx);
                }
            }
        }
        usort($tt, "th_order");
        // print_r2($tr);
        //print '<hr>';
        $tout = array();
        foreach ($tt as $v) {
            $id = array_pop($v);
            $tlabel = $tr[$id]['skos:preflabel'];
            $label = '';
            if (is_array($tlabel)) foreach ($tlabel as $kl => $vl) $label.= "($kl) $vl - ";
            else $label = "--------- NO LABEL --------";
            $tout[] = array(
                "level20" => (count($v)) * 20,
                "level" => (count($v)) * 1,
                "id" => $id,
                "title" => $label
            );
        }
        $action->lay->setBlockData("TDESC", $tout);
        // print_r2($tt);
        
    } else {
        if ($iduri) {
            /**
             * @var \Dcp\Family\THESAURUS $th
             */
            $th = new_doc($dbaccess, $iduri);
            $action->lay->set("msg2", _("UPDATE THESAURUS") . ' ' . $th->title);
        } else {
            if (!$newuri) $newuri = $desc->getAttribute("rdf:about");
            if (!$newuri) $newuri = "th_test";
            $th = getThesaurusFromURI($dbaccess, $newuri);
            if (!$th) {
                // create it
                $th = createDoc($dbaccess, "THESAURUS");
                $th->setValue("thes_uri", $newuri);
                $th->name = $newuri;
                $th->Add();
                $action->lay->set("msg2", _("CREATE THESAURUS") . ' ' . $uri);
            }
        }
        $thid = $th->id;
        $concepts = $desc->childNodes;
        
        $action->lay->set("msg", sprintf("%d concepts imported\n", $concepts->length));
        
        for ($j = 0; $j < $concepts->length; $j++) {
            $nod = $concepts->item($j); //Node j
            $nodename = strtolower($nod->nodeName);
            
            if (($nodename == "rdf:description") || ($nodename == "skos:concept")) {
                importSkosConcept($dbaccess, $thid, $nod);
            }
        }
        // postImport Refreshing
        refreshThConceptFromURI($dbaccess, $thid);
        $th->refreshConcepts();
    }
}
function notxy($t, $x, $y)
{
    foreach ($t as $v) {
        if (in_array($x, $v)) return false;
        if (in_array($y, $v)) return false;
    }
    return true;
}
function noty($t, $y)
{
    foreach ($t as $v) {
        if (in_array($y, $v)) return false;
    }
    return true;
}
// y before x
function th_insertbefore(&$t, $x, $y)
{
    foreach ($t as $k => $v) {
        if (in_array($y, $v)) {
            $t[$k] = array_merge((array)$x, $t[$k]);
        }
    }
    $t[] = array(
        $x
    );
} // y after x
function th_insertafter(&$t, $x, $y)
{
    foreach ($t as $v) {
        if (in_array($x, $v)) {
            $tt1 = array();
            foreach ($v as $vv) {
                $tt1[] = $vv;
                if ($vv == $x) break;
            }
            $tt1[] = $y;
            $t[] = $tt1;
            break;
        }
    }
}

function th_order($a, $b)
{
    $sa = implode("-", $a);
    $sb = implode("-", $b);
    return strcmp($sa, $sb);
}
/**
 * Import a concept
 * @param string $dbaccess
 * @param string $thid
 * @param DOMElement $node
 * @param bool $analyze
 * @return string
 */
function importSkosConcept($dbaccess, $thid, &$node, $analyze = false)
{
    /**
     * @var Doc[] $tcol
     */
    $tcol = array();
    $uri = decodeRef($node->getAttribute("rdf:about"));
    $co = getConceptFromURI($dbaccess, $uri);
    if (!$co) {
        // create it
        $co = createDoc($dbaccess, "THCONCEPT");
        $co->setValue("thc_uri", $uri);
        $co->setValue("thc_thesaurus", $thid);
        $co->Add();
    }
    $ats = $node->childNodes;
    
    for ($j = 0; $j < $ats->length; $j++) {
        /**
         * @var DOMElement $a
         */
        $a = $ats->item($j);
        if ($a->nodeType == XML_TEXT_NODE) continue;
        $lang = $a->getAttribute("xml:lang");
        $nodename = strtolower($a->nodeName);
        $nodevalue = $a->nodeValue;
        
        switch ($nodename) {
            case "rdfs:label":
                $co->setValue("thc_label", $nodevalue);
                break;

            case "skos:broader":
                $refuri = decodeRef($a->getAttribute("rdf:resource"));
                $co->setValue("thc_uribroader", $refuri);
                break;

            case "skos:narrower":
                break;

            case "skos:related":
                $refuri = decodeRef($a->getAttribute("rdf:resource"));
                $trel[] = $refuri;
                $co->setValue("thc_urirelated", $trel);
                break;

            default:
                if (preg_match("/skos:(.*)$/", $nodename, $reg)) {
                    $aname = "thc_" . $reg[1];
                    if ($lang) {
                        if (empty($tcol[$lang])) {
                            $cl = getLangConcept($dbaccess, $co->initid, $lang);
                            if (!$cl) {
                                // create it
                                // print "CERATE THLANGCONCEPT <br>\n";
                                $cl = createDoc($dbaccess, "THLANGCONCEPT");
                                $cl->setValue("thcl_lang", $lang);
                                $cl->setValue("thcl_thconcept", $co->initid);
                                $cl->Add();
                            }
                            $tcol[$lang] = $cl;
                        } else {
                            //print "ALREADY SET $lang";
                            
                        }
                        $tcol[$lang]->setValue($aname, $nodevalue);
                    } else {
                        $co->setValue($aname, $nodevalue);
                        //	  print "$aname,$nodevalue<br>\n";
                        
                    }
                }
        }
        //        print "$nodename<br>";
        
    }
    $err = $co->modify();
    //  $co->postModify();
    foreach ($tcol as $v) {
        $v->modify();
    }
    return $err;
}

function refreshThConceptFromURI($dbaccess, $thid)
{
    
    $s = new SearchDoc($dbaccess, "THCONCEPT");
    $s->addFilter("thc_thesaurus='" . $thid . "'");
    $s->setObjectReturn();
    $s->search();
    /**
     * @var \Dcp\Family\THCONCEPT $doc
     */
    while ($doc = $s->getNextDoc()) {
        $doc->refreshFromURI();
        $doc->modify();
    }
}

function decodeRef($s)
{
    $s = urldecode($s);
    if (!seems_utf8($s)) {
        $s = utf8_encode($s);
    }
    return $s;
}
/**
 * analyze a concept
 * @param string $dbaccess
 * @param string $thid
 * @param DomElement $node
 * @param array $tcon
 * @return string
 */
function analyzeSkosConcept($dbaccess, $thid, &$node, &$tcon)
{
    $err = '';
    $uri = $node->getAttribute("rdf:about");
    
    $uri = decodeRef($uri);
    $tcon[$uri] = array();
    
    $ats = $node->childNodes;
    
    for ($j = 0; $j < $ats->length; $j++) {
        /**
         * @var DomElement $a
         */
        $a = $ats->item($j);
        if ($a->nodeType == XML_TEXT_NODE) continue;
        $lang = $a->getAttribute("xml:lang");
        $nodename = strtolower($a->nodeName);
        $nodevalue = $a->nodeValue;
        
        switch ($nodename) {
            case "rdfs:label":
                $tcon[$uri][$nodename] = $nodevalue;
                break;

            case "skos:broader":
                $refuri = $a->getAttribute("rdf:resource");
                $refuri = decodeRef($refuri);
                $tcon[$uri][$nodename] = $refuri;
                break;

            case "skos:narrower":
                break;

            case "skos:related":
                $refuri = $a->getAttribute("rdf:resource");
                $refuri = decodeRef($refuri);
                $tcon[$uri][$nodename][] = $refuri;
                break;

            default:
                if (preg_match("/skos:(.*)$/", $nodename, $reg)) {
                    if ($lang) {
                        $tcon[$uri][$nodename][$lang] = $nodevalue;
                    } else {
                        $tcon[$uri][$nodename][$lang] = $nodevalue;
                    }
                }
        }
    }
    
    return $err;
}
