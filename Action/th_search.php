<?php
/**
 * View interface to search document from thesaurus
 *
 * @author Anakeen 2008
 * @version $Id: th_search.php,v 1.1 2008/08/11 16:31:22 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage THESAURUS
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("THESAURUS/Lib.Thesaurus.php");
/**
 * View search interface
 * @param Action &$action current action
 * @global thid Http var : thesaurus document identificator to use
 * @global famid Http var : family document to search
 */
function th_search(&$action) {
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $thid = GetHttpVars("thid");
  $fid = GetHttpVars("famid");

  $fdoc=new_doc($dbaccess,$fid);
  if (! $fdoc->isAlive()) $action->exitError(sprintf(_("document %s not alive"),$fid));
  if (! $thid) {
    $at=$fdoc->getNormalAttributes();
    foreach ($at as $k=>$oa) {
      if ($oa->type=="thesaurus") {
	$aid=$oa->id;
	$thid=$oa->format;
	break;
      }
    }    
  }
  $th=new_doc($dbaccess,$thid);
  if (! $th->isAlive()) $action->exitError(sprintf(_("thesaurus %s not alive"),$thid));
	      


  $action->lay->set("aid",$aid);
  $action->lay->set("thid",$thid);
  $action->lay->set("famid",$fid);
  
}
?>