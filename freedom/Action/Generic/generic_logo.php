<?php
/**
 * Display logo
 *
 * @author Anakeen 2000 
 * @version $Id: generic_logo.php,v 1.5 2004/05/13 16:17:14 eric Exp $
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
      $doc=new Doc($dbaccess,$famid);
      $action->lay->Set("appicon",$doc->getIcon());
      $action->lay->Set("apptitle",$doc->title);
    }
    



}

?>
