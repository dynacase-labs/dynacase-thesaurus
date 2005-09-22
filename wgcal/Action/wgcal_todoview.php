<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: wgcal_todoview.php,v 1.1 2005/09/22 08:22:11 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage
 */
 /**
 */
include_once('Lib.wTools.php');
include_once('Lib.WGCal.php');
include_once('FDL/Lib.Dir.php');
include_once('FDL/popup_util.php');

function wgcal_todoview(&$action) {

  $todoshort = 25;

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $todoviewday = $action->getParam("WGCAL_U_TODODAYS", -1);
  $todowarn = $action->getParam("WGCAL_U_TODOWARN", 2);

  setToolsLayout($action, 'todo');

  $today = time();

  $filter = array();
  $filter[] = "todo_idowner=".$action->user->fid;
  $start = w_datets2db(0,true);
  if ($todoviewday>0) {
    $stop = w_datets2db(time()+($todoviewday * 24 * 3600),true);
    $filter[] = "todo_date < '".$stop."'";
  } 
  $orderby = $action->GetParam("WGCAL_U_TODOORDER", "desc");
  $todos = getChildDoc($dbaccess, 0, 0, "ALL", $filter, $action->user->id, "TABLE", "TODO", false, "todo_date ".$orderby, true);

  $td = array(); $itd = 0;
  foreach ($todos as $k => $v) {
    $td[$itd]["rgTodo"] = $k;
    $td[$itd]["idTodo"] = $v["id"];
    $td[$itd]["colorTodo"] = "transparent";
    $td[$itd]["sTextTodo"] = (strlen($v["todo_title"])>$todoshort ? substr($v["todo_title"],0,$todoshort)."..." : $v["todo_title"]);
    $td[$itd]["jsTextTodo"] = addslashes($td[$itd]["sTextTodo"]);
    $td[$itd]["lTextTodo"] = "[".w_strftime(w_dbdate2ts($v["todo_date"]),WD_FMT_DAYFTEXT)."] ".$v["todo_title"];
    $td[$itd]["dateTodo"] = w_strftime(w_dbdate2ts($v["todo_date"]),WD_FMT_DAYSTEXT);

    $cdate = w_dbdate2ts($v["todo_date"]);
    $td[$itd]["warning"] = false;
    $td[$itd]["alert"] = false;
    
    if ($cdate<$today) {
      $td[$itd]["alert"] = true;
      $td[$itd]["colorTodo"] = "red";
    } else if ($cdate<($today+($todowarn*24*3600))) {
      $td[$itd]["warning"] = true;
      $td[$itd]["colorTodo"] = "orange";
    } else {
      $td[$itd]["colorTodo"] = "#00ff00";
    }
    $itd++;
  }
  $action->lay->setBlockData("TodoList", $td);
  $action->lay->set("todocount", count($todos));
  $action->lay->set("Todos", count($todos)>0);

}


?>
