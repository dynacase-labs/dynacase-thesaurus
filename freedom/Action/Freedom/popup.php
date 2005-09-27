<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: popup.php,v 1.2 2005/09/27 16:16:50 eric Exp $
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
?>