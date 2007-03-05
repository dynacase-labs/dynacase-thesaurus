<?php
/**
 * View logo
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_logo.php,v 1.5 2007/03/05 16:11:21 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */




function freedom_logo(&$action) 
{
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/resizeimg.js");
  $action->lay->Set("appicon",$action->GetImageUrl($action->parent->icon));
  $action->lay->Set("apptitle",$action->parent->description);

}

?>
