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
function th_search(&$action)
{
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $thid = null; //  $thid = $action->getArgument("thid");
    $fid = $action->getArgument("famid");
    $aid = $action->getArgument("aid");
    $multi = $action->getArgument("multi");
    
    $fdoc = new_doc($dbaccess, $fid);
    if (!$fdoc->isAlive()) $action->exitError(sprintf(_("document %s not alive") , $fid));
    if (!$thid) {
        $at = $fdoc->getNormalAttributes();
        foreach ($at as $oa) {
            if (($aid == "") || ($aid == $oa->id)) {
                if ($oa->type == "thesaurus") {
                    $aid = $oa->id;
                    $thid = $oa->format;
                    break;
                }
            }
        }
    }
    
    $action->lay->eSet("multi", $multi);
    $action->lay->eSet("aid", $aid);
    $action->lay->eSet("thid", $thid);
    $action->lay->eSet("famid", $fid);
}
