<?php
/**
 * Apply document methods
 *
 * @author Anakeen 2000 
 * @version $Id: fdl_method.php,v 1.5 2005/07/29 16:21:33 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Doc.php");
function fdl_method(&$action) 
{
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);
  $method = GetHttpVars("method");


  $doc= new_Doc($dbaccess,$docid);

  
  if ($doc && $doc->isAlive()) {
    
    $err = $doc->control("view");
    if ($err != "") $action->exitError($err);

    if (method_exists ( $doc, $method)) {
      
      $err=call_user_method($method,$doc);
    } else {
      $action->AddWarningMsg(sprintf(_("the method %s does not exist for this document"),$method));
    }
  }

  
  if ($err != "") $action->AddWarningMsg($err);
  
  
  $action->AddLogMsg(sprintf(_("%s has been locked"),$doc->title));
    
  
    
  redirect($action,"FDL","FDL_CARD&id=".$doc->id,$action->GetParam("CORE_STANDURL"));

}



?>
