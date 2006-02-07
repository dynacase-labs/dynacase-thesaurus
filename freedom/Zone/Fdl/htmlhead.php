<?php
/**
 * HTML Header
 *
 * @author Anakeen 2006
 * @version $Id: htmlhead.php,v 1.1 2006/02/07 14:51:55 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */



function htmlhead(&$action) {
  $title = GetHttpVars("title");
  $action->lay->set("TITLE", $title);
}
?>
