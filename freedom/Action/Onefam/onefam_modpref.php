<?php
/**
 * validate user or master choosen families
 *
 * @author Anakeen 2000 
 * @version $Id: onefam_modpref.php,v 1.5 2004/06/03 14:47:28 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/Class.Doc.php");

function onefam_modpref(&$action,$idsattr="ONEFAM_IDS") 
{
  $tidsfam = GetHttpVars("idsfam",array()); // preferenced families
  $dbaccess = $action->GetParam("FREEDOM_DB");


  
  $idsfam = $action->GetParam($idsattr);
  $idsfam = implode(",",$tidsfam);

  if ($idsattr=="ONEFAM_IDS")  $action->parent->param->Set($idsattr,$idsfam,PARAM_USER.$action->user->id,$action->parent->id);
  else $action->parent->param->Set($idsattr,$idsfam,PARAM_APP,$action->parent->id);
	  
      
  redirect($action,GetHttpVars("app"),"ONEFAM_LIST");
  
}
function onefam_modmasterpref(&$action) {
  onefam_modpref($action,"ONEFAM_MIDS");  
}

?>
