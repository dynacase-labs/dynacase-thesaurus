<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: popup.php,v 1.1 2005/04/05 17:29:38 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

function popup(&$action) {
  $folio=GetHttpVars("folio");

  if ($folio) {
    $action->lay->set("ofolio","&folio=$folio");
  } else {
    $action->lay->set("ofolio","");
  }
}