<?php
/**
 * progress bar tool
 *
 * @author Anakeen 2000 
 * @version $Id: fusers_admin.php,v 1.1 2004/08/12 10:24:44 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

function fusers_admin(&$action) {
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");

}