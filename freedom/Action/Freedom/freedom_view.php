<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_view.php,v 1.10 2005/04/13 11:12:06 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */




include_once('FDL/viewfolder.php');



// -----------------------------------
// -----------------------------------
function freedom_view(&$action) {
  // -----------------------------------
  // redirect layout icon if needed

  $prefview = $action->getParam("FREEDOM_VIEW","list");

  switch ($prefview) {
  case "list":
    $action->layout = $action->GetLayoutFile("freedom_list.xml");
    $action->lay = new Layout($action->layout,$action);
  viewfolder($action, false);
  break;
  case "detail":
    $action->layout = $action->GetLayoutFile("freedom_listdetail.xml");
    $action->lay = new Layout($action->layout,$action);
  viewfolder($action, 2);
  break;
  case "icon":
    $action->layout = $action->GetLayoutFile("freedom_icons.xml");
    $action->lay = new Layout($action->layout,$action);
  viewfolder($action, false);
  break;
  case "column":
    $action->layout = $action->GetLayoutFile("freedom_column.xml");
    $action->lay = new Layout($action->layout,$action);
  viewfolder($action, false,true,true);
  break;
    
  }
  
  
}





?>
