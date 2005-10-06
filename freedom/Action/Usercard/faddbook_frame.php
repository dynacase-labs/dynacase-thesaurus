<?php
/**
 * Display main interface for address book
 *
 * @author Anakeen 2000 
 * @version $Id: faddbook_frame.php,v 1.2 2005/10/06 13:18:42 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

function faddbook_frame(&$action) {


  $f1=$action->getParam("USERCARD_FIRSTFAM","USER");
  $f2=$action->getParam("USERCARD_SECONDFAM");

  $action->lay->set("F1",$f1);

  $action->lay->set("F2",$f2);        
  $action->lay->set("HasF2",($f2 != ""));

}
?>
