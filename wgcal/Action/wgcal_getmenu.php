<?php
/**
 * Get event producter popup menu
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_getmenu.php,v 1.1 2006/04/17 11:15:16 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Dir.php");


/**
 * get menu for event producer in JS (like mcalmenu needed)
 * @param Action &$action current action
 * @global id Http Var : document identificator 
 * @global latest Http var : (Y|N|L|P) if Y force view latest revision, L : latest fixed revision, P : previous revision
 */
function wgcal_getmenu(&$action) {
  // -----------------------------------
  include_once("WGCAL/Lib.wTools.php");
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id");
  $dbg   = GetHttpVars("dbg", 0);

  if ($docid=="") {
    $action->lay->set("status", -1);
    $action->lay->set("statustext", _("document reference no set"));
    return;
  }
   
  if (! is_numeric($docid)) $docid=getIdFromName($dbaccess,$docid);

  if (intval($docid) == 0) {
    $action->lay->set("status", -1);
    $action->lay->set("statustext", _("unknow logical reference")." : ".$docid);
    return;
  }
    
  $doc = new_Doc($dbaccess, $docid);
  if (! $doc->isAffected()) {
    $action->lay->set("status", -1);
    $action->lay->set("statustext", sprintf(_("cannot see unknow reference %s"),$docid));
    return;
  }

  if (method_exists($doc, "getAgendaMenu")) {
    $tmenu = $doc->getAgendaMenu();
    if (!is_array($tmenu)) {
      $action->lay->set("status", -1);
      $action->lay->set("statustext", "Menu generation error [$tmenu]");
    } else {
      $action->lay->setBlockData("menuitem", $tmenu);
      $action->lay->set("status", 0);
      $action->lay->set("statustext", addslashes($doc->getTitle()));
    }
  } else {
    $action->lay->set("status", 1);
    $action->lay->set("statustext", _("no menu defined for Doc #".$docid));
  }

  setThemeValue();

  $action->lay->set("dbgpre", "");
  $action->lay->set("dbgpost", "");  
  if ($dbg==1) {
   $action->lay->set("dbgpre", "<pre>");
   $action->lay->set("dbgpost", "</prev>");  
  }
}
?>
