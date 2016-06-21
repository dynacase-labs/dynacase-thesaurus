<?php
/*
 * Execute search document from thesaurus
 *
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package THESAURUS
*/

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.SearchDoc.php");
include_once ("THESAURUS/Lib.Thesaurus.php");
/**
 * Return document list
 * @param Action &$action current action
 * @global string $thid Http var : thesaurus document identificator to use
 * @global string $famid Http var : family document to search
 */
function th_execsearch(&$action)
{
    $dbaccess = $action->dbaccess;
    $thid = $action->getArgument("thid");
    $fid = $action->getArgument("famid");
    $aid = $action->getArgument("aid");
    $slice = $action->getArgument("slice", 100);
    $thvalue = $action->getArgument("thvalue");
    //search thesaurus attribute search
    $fdoc = new_doc($dbaccess, $fid);
    if (!$fdoc->isAlive()) $action->exitError(sprintf(_("family %s not alive") , $fid));
    /**
     * @var _THESAURUS $th
     */
    $th = new_doc($dbaccess, $thid);
    if (!$th->isAlive()) $action->exitError(sprintf(_("thesaurus %s not alive") , $thid));
    $s = new SearchDoc($dbaccess, $fid);
    $thsql = $th->getSqlFilter($fdoc->getAttribute($aid) , $thvalue);
    
    $s->addFilter($thsql);
    $s->slice = $slice;
    $s->orderby = 'title';
    /*
    $s->setDebugMode();
    $t=$s->Search();
    
    $di=$s->getDebugInfo();
    print_r2( $s->getDebugInfo());
    print $s->getOriginalQuery();
    */
    /**
     * @var DocSearch $se
     */
    $se = createTmpDoc($dbaccess, "SEARCH");
    $se->setValue("ba_title", sprintf(_("Thesaurus results")));
    $se->add();
    $se->addStaticQuery($s->getOriginalQuery());
    
    redirect($action, "FREEDOM", "FREEDOM_VIEW&dirid=" . $se->id);
}
