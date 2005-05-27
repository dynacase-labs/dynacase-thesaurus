<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: todo.php,v 1.4 2005/05/27 15:03:28 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage
 */
 /**
 */
include_once('Lib.WGCal.php');
include_once('FDL/Lib.Dir.php');
include_once('FDL/popup_util.php');

function todo(&$action) {

  $todoshort = 25;

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $standalone = GetHttpVars("S", 0);
  $todoviewday = GetHttpVars("dtodo", -1);
  $todowarn = $action->getParam("WGCAL_U_TODOWARN", 2);

  $all =  explode("|", $action->GetParam("WGCAL_U_TOOLSSTATE", ""));
  $state = array();
  $td = array();
  $action->lay->set("vtodo", "");
  foreach ($all as $k => $v) {
    $t = explode("%",$v);
    $state[$t[0]] = $t[1];
    if ($t[0] == 'todo') $action->lay->set("vtodo", ($t[1]==0?"none":""));
  }

  $today = time();

  $filter = array();
  $filter[] = "todo_idowner=".$action->user->fid;
  $start = date2db(0,true);
  if ($todoviewday>0) {
    $stop = date2db(time()+($todoviewday * 24 * 3600),true);
    $filter[] = "todo_date < '".$stop."'";
  } 

  $orderby = $action->GetParam("WGCAL_U_TODOORDER", "desc");
  $todos = getChildDoc($dbaccess, 0, 0, "ALL", $filter, $action->user->id, "TABLE", "TODO", false, "todo_date ".$orderby, true);

  $td = array(); $itd = 0;
  foreach ($todos as $k => $v) {
    $td[$itd]["rgTodo"] = $k;
    $td[$itd]["idTodo"] = $v["id"];
    $td[$itd]["sTextTodo"] = (strlen($v["todo_title"])>$todoshort ? substr($v["todo_title"],0,$todoshort)."..." : $v["todo_title"]);
    $td[$itd]["jsTextTodo"] = str_replace("'", "\'", $td[$itd]["sTextTodo"]);
    $td[$itd]["lTextTodo"] = substr($v["todo_date"],0,11)." : ".$v["todo_title"];
    $td[$itd]["dateTodo"] = substr($v["todo_date"],0,5);

    $cdate = dbdate2ts($v["todo_date"]);
    $td[$itd]["warning"] = false;
    $td[$itd]["alert"] = false;
    
    if ($cdate<$today) $td[$itd]["alert"] = true;
    else if ($cdate<($today+($todowarn*24*3600))) $td[$itd]["warning"] = true;

    $itd++;
  }
  $action->lay->setBlockData("TodoList", $td);
  $action->lay->set("todocount", count($todos));
  $action->lay->set("Todos", count($todos)>0);
  $action->lay->set("standalone", ($standalone==0?false:true));

  
}


?>
