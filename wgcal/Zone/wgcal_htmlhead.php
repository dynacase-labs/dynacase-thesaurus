<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_htmlhead.php,v 1.3 2005/03/10 10:30:59 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// $Id: wgcal_htmlhead.php,v 1.3 2005/03/10 10:30:59 marc Exp $


include_once('Class.QueryDb.php');
include_once('Class.Application.php');

function wgcal_htmlhead(&$action) {

  global $_SERVER;

  $theme = $action->getParam("WGCAL_U_THEME", "default");
  $action->lay->set("theme", $theme);
  
  if (GetHttpVars("f",0)==1) {
    $r = $action->getParam("WGCAL_U_REFRESH_T", 0);
    if ($r==1) {
      $t[0]["refreshurl"] = "[CORE_STANDURL]&app=WGCAL&action=WGCAL_TOOLBAR&f=1";
      $t[0]["refreshdur"] = "10";
    }
    $action->lay->setBlockData("autorefresh", $t);
  } 
    
}
?>
