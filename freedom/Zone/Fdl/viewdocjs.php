<?php
/**
 * javascript utilities for view document
 *
 * @author Anakeen 2005
 * @version $Id: viewdocjs.php,v 1.1 2005/03/04 17:18:47 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



function viewdocjs(&$action) {
  
  setHeaderCache();
  // set default geo for mini view
  $mgeo = $action->GetParam("MVIEW_GEO");
  if (ereg("([0-9]+)\+([0-9]+)\+([0-9]+)x([0-9]+)",$mgeo,$reg)) {   
    $action->lay->set("mgeox",intval($reg[1]));
    $action->lay->set("mgeoy",intval($reg[2]));
    $action->lay->set("mgeow",intval($reg[3]));
    $action->lay->set("mgeoh",intval($reg[4]));
  } else {
    $action->lay->set("mgeox","250");
    $action->lay->set("mgeoy","210");
    $action->lay->set("mgeow","300");
    $action->lay->set("mgeoh","200");
  }
}