<?php
/**
 * progress bar tool
 *
 * @author Anakeen 2000
 * @version $Id: usercard_search.php,v 1.5 2005/05/12 12:05:36 caroline Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */
                                                                                
                                                                                
function usercard_search(&$action) {
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");                                                                               
  $action->lay->set("DETAILZONE", ($detailzone?"none":""));
}
