<?php
/**
 * HTML Header
 *
 * @author Anakeen 2006
 * @version $Id: htmlhead.php,v 1.2 2006/04/03 14:56:26 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 */
 /**
 */



function htmlhead(&$action) {
  $title = GetHttpVars("title");
  $action->lay->set("TITLE", $title);
}
?>
