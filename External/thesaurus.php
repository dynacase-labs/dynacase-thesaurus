<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package THESAURUS
*/

function getThConcept($dbaccess, $thesid, $name = '')
{
    $s1 = microtime(true);
    include_once ("FDL/Class.SearchDoc.php");
    
    $s2 = microtime(true);
    $s = new SearchDoc($dbaccess, "THCONCEPT");
    $s->addFilter("thc_thesaurus='" . intval($thesid) . "'");
    //  $s->setObjectReturn();
    if ($name) $s->addFilter("title ~* '" . pg_escape_string($name) . "'");;
    $t = $s->search();
    $s3 = sprintf("%.03f", microtime(true) - $s2);
    $tr = array();
    foreach ($t as $v) {
        if ($v) {
            $tr[] = array(
                htmlspecialchars($v["title"], ENT_QUOTES),
                $v["id"],
                $v["title"]
            );
        }
    }
    /*
    while ($doc = $s->nextDoc()) {
    $tr[] = array($doc->title, $doc->initid, $doc->title);      
    }
    */
    
    $tr[] = array(
        microtime(true) - $s1 . "[$s3]",
        "-",
        "-"
    );
    return $tr;
}
