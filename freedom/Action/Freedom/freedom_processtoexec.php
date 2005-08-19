<?php
/**
 * Interface to create new execution from batch
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_processtoexec.php,v 1.1 2005/08/19 16:14:50 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

include_once("FDL/Class.DocSearch.php");
include_once("FDL/freedom_util.php");  
include_once("GENERIC/generic_util.php");  


/**
 * Interface to edit new process
 * @param Action &$action current action
 * @global id Http var : document identificator for process document
 */
function freedom_processtoexec(&$action) {
  // -----------------------------------
   

  // Get all the params      
  $docid=GetHttpVars("id"); // id doc to search
  $action->lay->set("docid",$docid);

  
  $dbaccess = $action->GetParam("FREEDOM_DB");
        
  $doc = new_Doc($dbaccess, $docid);
  if ($doc->isAlive()) {
    $la=$doc->GetActionAttributes();
    
    $oa=current($la);
    $ta["exec_application"]=$oa->wapplication;
    $ta["exec_idref"]=$doc->id;
    $ta["exec_ref"]=$doc->title." (".$oa->labelText.")";

    $p=explode('&',$oa->waction);
    $ta["exec_action"]=current($p);
    next($p);
    $tp=array("id"=>$doc->id);
    while (list($k,$v) = each($p)) {
      list($var,$value)=explode("=",$v);
      $tp[$var]=$value;
    }
    $ta["exec_idvar"]=implode("\n",array_keys($tp)); 
    $ta["exec_valuevar"]=implode("\n",$tp);   
    
    $url="";
    foreach ($ta as $k=>$v) {
      $url.="&$k=".urlencode($v);
    }
    
  }
  $action->lay->set("url",$url);
}


?>