<?php
/**
 * progress bar tool
 *
 * @author Anakeen 2000 
 * @version $Id: fusers_admin.php,v 1.2 2006/04/03 14:56:26 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage FUSERS
 */
 /**
 */

function fusers_admin(&$action) {
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");

}