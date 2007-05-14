<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: wgcal_todoview.php,v 1.11 2007/05/14 13:27:46 marc Exp $
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


  $action->parent->AddJsRef("jscalendar/Layout/calendar.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-fr.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-setup.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_todo.js");
  $action->parent->AddCssRef("FDL:POPUP.CSS",true);

  $todoshort = 100;

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $todoviewday = $action->getParam("WGCAL_U_TODODAYS", -1);
  $todowarn = $action->getParam("WGCAL_U_TODOWARN", 2);
  $orderby = $action->GetParam("WGCAL_U_TODOORDER", "desc");


  $action->lay->set("lmode", false);
  if (GetHttpVars("mo", "")=="L") {
    header('Content-type: text/xml; charset=utf-8');
    $action->lay->setEncoding("utf-8");
    $action->lay->set("lmode", true);
    $todoviewday= 0;
    $orderby = "asc";
  }

  setToolsLayout($action, 'todo');

  $today = time();

  $filter = array();
  $filter[] = "todo_idowner=".$action->user->fid;
  $start = w_datets2db(0,true);
  if ($todoviewday>0) {
    $stop = w_datets2db(time()+($todoviewday * 24 * 3600),true);
    $filter[] = "todo_date < '".$stop."'";
  } 
  $todos = getChildDoc($dbaccess, 0, 0, "ALL", $filter, $action->user->id, "TABLE", "TODO", false, "todo_date ".$orderby, true);

  $td = array(); $itd = 0;
  foreach ($todos as $k => $v) {
    $td[$itd]["rgTodo"] = $k;
    $td[$itd]["idTodo"] = $v["id"];
    $td[$itd]["colorTodo"] = "transparent";
    $td[$itd]["textTodo"] = $v["todo_title"];
    $td[$itd]["sTextTodo"] = (strlen($v["todo_title"])>$todoshort ? substr($v["todo_title"],0,$todoshort)."..." : $v["todo_title"]);
    $td[$itd]["jsTextTodo"] = addslashes($td[$itd]["sTextTodo"]);
    $td[$itd]["lTextTodo"] = "[".w_strftime(w_dbdate2ts($v["todo_date"]),WD_FMT_DAYFTEXT)."] ".$v["todo_title"];
    $td[$itd]["dateTodo"] = strftime("%d %b", w_dbdate2ts($v["todo_date"]));
    $td[$itd]["dateTodoL"] = strftime("%d %b %Y", w_dbdate2ts($v["todo_date"]));

    $cdate = w_dbdate2ts($v["todo_date"]);
    $td[$itd]["warning"] = false;
    $td[$itd]["alert"] = false;
    
    $td[$itd]["warn"] = true;
    if ($cdate<$today) {
      $td[$itd]["alert"] = true; 
      $td[$itd]["colorTodo"] = "red";
      $td[$itd]["rcolor"] = "#FF0000";
    } else if ($cdate<($today+($todowarn*24*3600))) {
      $td[$itd]["warning"] = true;
      $td[$itd]["colorTodo"] = "orange";
      $td[$itd]["rcolor"] = "#ffd16e";
    } else {
      $td[$itd]["colorTodo"] = "#00ff00";
      $td[$itd]["warn"] = false;
      $td[$itd]["rcolor"] = "#cdf3c2";
    }
    $itd++;
  }
  $action->lay->setBlockData("TodoList", $td);
  $action->lay->set("todocount", count($todos));
  $action->lay->set("Todos", count($todos)>0);
  $action->lay->set("uptime", strftime("%H:%M %d/%m/%Y", time()));

  $action->lay->set("TOTO", false);
}


?>
