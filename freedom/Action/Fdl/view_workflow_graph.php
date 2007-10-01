<?php
/**
 * Edition to affect document
 *
 * @author Anakeen 2000 
 * @version $Id: view_workflow_graph.php,v 1.3 2007/10/01 16:52:51 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/Class.WDoc.php");
// -----------------------------------
/**
 * Edition to affect document
 * @param Action &$action current action
 * @global id Http var : document id to affect
 * @global viewdoc Http var : with preview of affect document [Y|N]
 */
function view_workflow_graph(&$action) {
  $docid = GetHttpVars("id"); 
  $viewdoc = (GetHttpVars("viewdoc","N")=="Y"); 
  $type = GetHttpVars("type","simple"); // type of graph
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc=new_doc($dbaccess,$docid);
  $cmd=getWshCmd(false,$action->user->id);

  $filetype="svg";
  if ($action->Read("navigator","")=="EXPLORER") $filetype="png";

  $cmd.="--api=wdoc_graphviz --type=$type --docid=".$doc->id;
  $svgfile="img-cache/w$type-".$action->getParam("CORE_LANG")."-".$doc->id.".$filetype";
  $dest=DEFAULT_PUBDIR."/$svgfile";
  $cmd .= "| dot -T$filetype> $dest";

  system($cmd);
  //  print_r2( $cmd);

  header("location:$svgfile");
  exit;
  
}