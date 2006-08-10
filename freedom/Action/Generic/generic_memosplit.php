<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: generic_memosplit.php,v 1.4 2006/08/10 08:45:10 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
function generic_memosplit(&$action) {
  $split = GetHttpVars("split"); // split H or V
  generic_memo($action,"GENE_SPLITMODE",$split);
}

function generic_memosearch(&$action) {
  $split = GetHttpVars("psearchid"); // preferential user search
  generic_memo($action,"GENE_PREFSEARCH",$split);
}


function generic_memo(&$action,$attrid,$value) {
  // -----------------------------------

  $famid  = GetHttpVars("famid");    // family id

  $tmode= explode(",",$action->getParam($attrid));

  // explode parameters
  while (list($k,$v) = each($tmode)) {
    list($fid,$vmode)=explode("|",$v);
    $tview[$fid]=$vmode;
  }

    $tview[$famid]=$value;
    // implode parameters to change user preferences
    $tmode=array();
    while (list($k,$v) = each($tview)) {
      if ($k>0) $tmode[]="$k|$v";
    }
    $pmode=implode(",",$tmode);
    
    $action->parent->param->Set($attrid,$pmode,PARAM_USER.$action->user->id,$action->parent->id);
    $action->parent->session->close();
   
}


?>
