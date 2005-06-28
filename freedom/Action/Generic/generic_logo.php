<?php
/**
 * Display logo
 *
 * @author Anakeen 2000 
 * @version $Id: generic_logo.php,v 1.6 2005/06/28 08:37:46 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("GENERIC/generic_util.php"); 

function generic_logo(&$action) 
{
    $action->lay->Set("apptitle","");

    $famid = getDefFam($action);
    if ($famid > 0) {
      $dbaccess = $action->GetParam("FREEDOM_DB");
      $doc=new_Doc($dbaccess,$famid);
      $action->lay->Set("appicon",$doc->getIcon());
      $action->lay->Set("apptitle",$doc->title);
    }
    



}

?>
