<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_view.php,v 1.6 2003/08/18 15:47:03 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: freedom_view.php,v 1.6 2003/08/18 15:47:03 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_view.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2001
// O*O  Anakeen development team
//  O   dev@anakeen.com
// ---------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------


include_once('FDL/viewfolder.php');



// -----------------------------------
// -----------------------------------
function freedom_view(&$action) {
  // -----------------------------------
  // redirect layout icon if needed
      
  $prefview = $action->getParam("FREEDOM_VIEW","list");

  switch ($prefview) {
  case "list":
    $action->layout = $action->GetLayoutFile("freedom_list.xml");
    $action->lay = new Layout($action->layout,$action);
  viewfolder($action, false);
  break;
  case "icon":
    $action->layout = $action->GetLayoutFile("freedom_icons.xml");
    $action->lay = new Layout($action->layout,$action);
  viewfolder($action, false);
  break;
  case "column":
    $action->layout = $action->GetLayoutFile("freedom_column.xml");
    $action->lay = new Layout($action->layout,$action);
  viewfolder($action, false);
  break;
    
  }
  
  
}





?>
