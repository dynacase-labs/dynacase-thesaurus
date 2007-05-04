<?php
/**
 * Interface to choose preferences
 *
 * @author Anakeen 2007
 * @version $Id: generic_prefs.php,v 1.1 2007/05/04 10:19:43 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("GENERIC/generic_util.php");
function generic_prefs(&$action) {
  
  $famid  = GetHttpVars("famid");    // family id
  $dirid  = GetHttpVars("dirid");    // last searched
  
  $dbaccess=$action->getParam("FREEDOM_DB");
  $fdoc=new_doc($dbaccess,$famid);
  if (! $fdoc->isAlive()) {
    $action->addWarningMsg(sprintf(_("Family (#%s) not exists"),$famid));
    redirect($action,"GENERIC",
	     "GENERIC_LOGO",
	     $action->GetParam("CORE_STANDURL"));
  }
  
  $action->lay->set("famtitle",$fdoc->title);
  $action->lay->set("famid",$famid);
  $action->lay->set("dirid",$dirid);

  $tabletters=getTabLetter($action,$famid);
  if ($tabletters=='N') $action->lay->set("lettercheck","");
  else $action->lay->set("lettercheck","checked");

  
  $viewmode=getViewMode($action,$famid);
  $splitmode=getSplitMode($action,$famid);

  print_r2("$viewmode - $splitmode");
  $action->lay->set("dispocheck1","");
  $action->lay->set("dispocheck2","");
  $action->lay->set("dispocheck3","");
  $action->lay->set("dispocheck4","");
  if (($viewmode=='abstract') && ($splitmode=='V')) $action->lay->set("dispocheck1","checked");
  if (($viewmode=='column')   && ($splitmode=='H')) $action->lay->set("dispocheck2","checked");
  if (($viewmode=='column')   && ($splitmode=='V')) $action->lay->set("dispocheck3","checked");
  if (($viewmode=='abstract') && ($splitmode=='H')) $action->lay->set("dispocheck4","checked");

}

?>