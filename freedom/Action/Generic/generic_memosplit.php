<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: generic_memosplit.php,v 1.1 2003/10/16 09:38:01 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
function generic_memosplit(&$action) {
  // -----------------------------------

  
  $famid  = GetHttpVars("famid");    // family id
  $split = GetHttpVars("split"); // split H or V

  $tmode= explode(",",$action->getParam("GENE_SPLITMODE"));

  // explode parameters
  while (list($k,$v) = each($tmode)) {
    list($fid,$vmode)=explode("|",$v);
    $tview[$fid]=$vmode;
  }

  switch ($split) {
  case "H":  
  case "V":
    $tview[$famid]=$split;
    // implode parameters to change user preferences
    $tmode=array();
    while (list($k,$v) = each($tview)) {
      if ($k>0) $tmode[]="$k|$v";
    }
    $pmode=implode(",",$tmode);
    
    $action->parent->param->Set("GENE_SPLITMODE",$pmode,PARAM_USER.$action->user->id,$action->parent->id);
   
    break;
    
  }
}
?>
