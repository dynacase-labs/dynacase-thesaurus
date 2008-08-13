<?php
/**
 * View interface to search document from thesaurus
 *
 * @author Anakeen 2008
 * @version $Id: th_search.php,v 1.2 2008/08/13 10:09:32 eric Exp $
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
  //  $thid = GetHttpVars("thid");
  $fid = GetHttpVars("famid");
  $aid = GetHttpVars("aid");
  $multi = GetHttpVars("multi");

  $fdoc=new_doc($dbaccess,$fid);
  if (! $fdoc->isAlive()) $action->exitError(sprintf(_("document %s not alive"),$fid));
  if (! $thid) {
    $at=$fdoc->getNormalAttributes();
    foreach ($at as $k=>$oa) {
      if (($aid == "") || ($aid==$oa->id)) {
	if ($oa->type=="thesaurus") {
	  $aid=$oa->id;
	  $thid=$oa->format;
	  break;
	}
      }
    }    
  }
	      
  
 


  $action->lay->set("multi",$multi);
  $action->lay->set("aid",$aid);
  $action->lay->set("thid",$thid);
  $action->lay->set("famid",$fid);
  
}
?>