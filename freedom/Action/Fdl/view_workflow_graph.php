<?php
/**
 * Edition to affect document
 *
 * @author Anakeen 2000 
 * @version $Id: view_workflow_graph.php,v 1.5 2008/03/11 11:25:04 eric Exp $
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
 * @global type Http var : type of graph
 * @global format Http var : file format pnh or svg
 * @global orient Http var :orientation TB (TopBottom)  or LR (LeftRight)
 * @global size Http var : global size of graph
 */
function view_workflow_graph(&$action) {
  $docid = GetHttpVars("id"); 
  $type = GetHttpVars("type","simple"); // type of graph
  $format = GetHttpVars("format","svg"); // type of graph
  $orient = GetHttpVars("orient","LR"); // type of graph
  $size = GetHttpVars("size","10"); // size of graph
  $ratio = GetHttpVars("ratio","auto"); // ratio of graph
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc=new_doc($dbaccess,$docid);
  $cmd=getWshCmd(false,$action->user->id);

 

  $cmd.="--api=wdoc_graphviz --size=$size --ratio=$ratio --type=$type --orient=$orient --docid=".$doc->id;
  $svgfile="img-cache/w$type-".$action->getParam("CORE_LANG")."-".$doc->id.".$format";
  $dest=DEFAULT_PUBDIR."/$svgfile";
  if ($format == "dot")   $cmd .= ">  $dest";
  else $cmd .= "| dot -T$format> $dest";

  system($cmd);
  //  print_r2( $cmd);

  header("location:$svgfile");
  exit;
  
}