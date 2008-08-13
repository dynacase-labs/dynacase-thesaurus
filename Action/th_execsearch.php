<?php
/**
 * Execute search document from thesaurus
 *
 * @author Anakeen 2008
 * @version $Id: th_execsearch.php,v 1.2 2008/08/13 10:09:32 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage THESAURUS
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("FDL/Class.SearchDoc.php");
include_once("THESAURUS/Lib.Thesaurus.php");
/**
 * Return document list
 * @param Action &$action current action
 * @global thid Http var : thesaurus document identificator to use
 * @global famid Http var : family document to search
 */
function th_execsearch(&$action) {
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $thid = GetHttpVars("thid");
  $fid = GetHttpVars("famid");
  $aid = GetHttpVars("aid");
  $slice = GetHttpVars("slice",100);
  $thvalue = GetHttpVars("thvalue");

  //search thesaurus attribute search
  $fdoc=new_doc($dbaccess,$fid);
  if (! $fdoc->isAlive()) $action->exitError(sprintf(_("family %s not alive"),$fid));

  $th=new_doc($dbaccess,$thid);
  if (! $th->isAlive()) $action->exitError(sprintf(_("thesaurus %s not alive"),$thid));
  $s=new SearchDoc($dbaccess,$fid);
  $thsql=$th->getSqlFilter($fdoc->getAttribute($aid),$thvalue);

  $s->addFilter($thsql);
  $s->slice=$slice;
  $s->orderby='title';

  $t=$s->Search();
  
  
}
?>