<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: todo.php,v 1.1 2005/03/30 10:04:41 marc Exp $
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
  $todoviewday = GetHttpVars("dtodo", -1);
  $todowarn = $action->getParam("WGCAL_U_TODOWARN", 2);

  $today = time();

  $filter = array();
  $filter[] = "todo_idowner=".$action->user->fid;
  $start = date2db(0,true);
  if ($todoviewday>0) {
    $stop = date2db(time()+($todoviewday * 24 * 3600),true);
    $filter[] = "todo_date < '".$stop."'";
  } 

  $todos = getChildDoc($dbaccess, 0, 0, "ALL", $filter, $action->user->id, "TABLE", "TODO", false, "todo_date", true);

  $td = array(); $itd = 0;
  foreach ($todos as $k => $v) {
    $td[$itd]["rgTodo"] = $k;
    $td[$itd]["idTodo"] = $v["id"];
    $td[$itd]["sTextTodo"] = (strlen($v["todo_title"])>$todoshort ? substr($v["todo_title"],0,$todoshort)."..." : $v["todo_title"]);
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
  $action->lay->set("Todos", count($todos)>0);

  
}


?>
