<?php
/**
 * Display doucment explorer
 *
 * @author Anakeen 2006
 * @version $Id: prototree.php,v 1.1 2008/04/04 14:46:55 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage FDL
 */
 /**
 */




/**
 * Add branch in folder tree
 * @param Action &$action current action
 * @param array $tree : tree definition
 */
function prototree(&$action,$tree,$postact=array()) {
  header('Content-type: text/xml; charset=utf-8'); 
  $action->lay = new Layout(getLayoutFile("FDL","prototree.xml"),$action);
  $action->lay->setEncoding("utf-8");


  $action->lay->set("count",count($tree));
  $action->lay->set("code","OK");
  $action->lay->set("warning","");
  $pulid=uniqid("ul");
  foreach ($tree as $k=>$v) {
    $tree[$k]["ulid"]=$pulid.$k;
    if (! isset($v["selecturl"])) $tree[$k]["selecturl"]=false;
    if (! isset($v["selectjs"])) $tree[$k]["selectjs"]=false;
  }
  $action->lay->setBlockData("TREE",$tree);
  $action->lay->setBlockData("ACTIONS",$postact);
  }
?>