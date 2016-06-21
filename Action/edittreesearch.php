<?php
/*
 * View interface to search document from thesaurus
 *
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package THESAURUS
*/

include_once ("FDL/Class.Doc.php");
include_once ("THESAURUS/Lib.Thesaurus.php");
/**
 * View search interface
 * @param Action &$action current action
 * @global string $thid Http var : thesaurus document identificator to use
 * @global string $famid Http var : family document to search
 */
function edittreesearch(Action & $action)
{
    $dbaccess = $action->dbaccess;
    $thid = $action->getArgument("thid");
    $filter = $action->getArgument("filter");
    $fid = $action->getArgument("famid");
    $aid = strtolower($action->getArgument("aid"));
    $multi = ($action->getArgument("multi") == "yes") ? 'multi' : false;
    $level = $action->getArgument("level", 2);
    $iname = $action->getArgument("inputname", "thvalue");
    $conid = $action->getArgument("conid");
    
    $lang = strtolower(strtok(getParam("CORE_LANG") , '_'));
    $error = "";
    $b1 = microtime(true);
    
    if ($conid) {
        $con = new_doc($dbaccess, $conid);
        if (!$con->isAlive()) $action->exitError(sprintf(_("document %s not alive") , $conid));
        $thid = $con->getrawValue("thc_thesaurus");
    }
    
    $fdoc = new_doc($dbaccess, $fid);
    if (!$thid) {
        if (!$fdoc->isAlive()) $action->exitError(sprintf(_("document %s not alive") , $fid));
        $at = $fdoc->getNormalAttributes();
        foreach ($at as $oa) {
            if ($oa->type == "thesaurus") {
                $aid = $oa->id;
                $thid = $oa->format;
                break;
            }
        }
    }
    
    $th = new_doc($dbaccess, $thid);
    if (!$th->isAlive()) $action->exitError(sprintf(_("thesaurus %s not alive") , $thid));
    
    if ($conid) $t = getChildConcepts($dbaccess, $conid);
    else $t = getConceptsLevel($dbaccess, $th->initid, $level);
    
    $b2 = microtime(true);
    
    if ($conid) {
        $child = getUltree($t, $conid, $filter, $childgood, $lang, $fdoc->id, $aid, $dbaccess);
        $action->lay->template = '[child]';
    } else {
        $child = getUltree($t, "", $filter, $childgood, $lang, $fdoc->id, $aid, $dbaccess);
    }
    $action->lay->set("first", true);
    $action->lay->set("child", $child);
    $action->lay->eSet("aid", $aid);
    $action->lay->set("multi", $multi);
    $action->lay->set("ymulti", $multi ? "yes" : "no");
    
    $action->lay->set("time", sprintf("%0.3f [%.03f]", $b2 - $b1, microtime(true) - $b1));
    
    $action->lay->eSet("thid", $thid);
    $action->lay->eSet("famid", $fid);
    $action->lay->eSet("iname", $iname);
    $action->lay->eSet("error", $error);
}

function getThLabelLang($v, $lang)
{
    $tlang = Doc::rawValueToArray($v["thc_lang"]);
    $tll = Doc::rawValueToArray($v["thc_langlabel"]);
    
    $kgood = - 1;
    
    foreach ($tlang as $k => $v) {
        if ($tlang[$k] == $lang) {
            $kgood = $k;
            break;
        }
    }
    
    return (isset($tll[$kgood])) ? $tll[$kgood] : $tll[0];
}

function getUltree(&$t, $initid, $filter, &$oneisgood, $lang, $famid, $aid, $dbaccess)
{
    
    $lay = new Layout(getLayoutFile("THESAURUS", "editsubtreesearch.xml"));
    $b = array();
    $oneisgood = false;
    foreach ($t as $v) {
        if ($v["thc_broader"] == $initid) {
            $label = getThLabelLang($v, $lang);
            $isgood = (($filter == "") || (preg_match("/$filter/i", $v["thc_label"] . $label, $reg)));
            $oneisgood|= $isgood;
            if ($filter) {
                $child = getUltree($t, $v["initid"], $filter, $childgood, $lang, $famid, $aid, $dbaccess);
                if ($child != "") $child = '<ul>' . $child . '</ul>';
            } else {
                $child = "";
                $childgood = hasChildConcepts($dbaccess, $v["initid"]);
                $isgood = true;
            }
            
            if ($childgood || $isgood) $cardinal = getThCardinal($dbaccess, $famid, $v["initid"], $aid);
            else $cardinal = "nc";
            
            $oneisgood|= $childgood;
            $b[] = array(
                "title" => htmlspecialchars($v["thc_label"], ENT_QUOTES) ,
                "desc" => htmlspecialchars($label, ENT_QUOTES) ,
                "conid" => $v["initid"],
                "isfiltergood" => $isgood,
                "ischildgoodnos" => $childgood,
                "nosee" => (!$childgood) && (!$isgood) ,
                "openit" => ($childgood) && (!$isgood) ,
                "child" => $child,
                "filter" => ($filter != "") ,
                "cardinal" => htmlspecialchars($cardinal, ENT_QUOTES)
            );
        }
    }
    if (count($b) == 0) {
        return "";
    }
    $lay->setBlockData("LIs", $b);
    return $lay->gen();
}
