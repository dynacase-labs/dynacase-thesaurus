<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: refreshdir.php,v 1.3 2003/08/18 15:47:03 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: refreshdir.php,v 1.3 2003/08/18 15:47:03 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/refreshdir.php,v $
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
// $Log: refreshdir.php,v $
// Revision 1.3  2003/08/18 15:47:03  eric
// phpdoc
//
// Revision 1.2  2002/06/19 12:32:29  eric
// modif des permissions : intégration de rq sql hasviewpermission
//
// Revision 1.1  2002/02/05 16:34:07  eric
// decoupage pour FREEDOM-LIB
//
// Revision 1.2  2001/11/28 13:40:10  eric
// home directory
//
// Revision 1.1  2001/11/09 09:41:14  eric
// gestion documentaire
//
//
// ---------------------------------------------------------------
include_once("FDL/Class.Doc.php");
include_once("FDL/Class.QueryDir.php");



// -----------------------------------
// -----------------------------------
function refreshdir(&$action) {
// -----------------------------------

  $action->log->start();
  // Set the globals elements


  $baseurl=$action->GetParam("CORE_BASEURL");
  $standurl=$action->GetParam("CORE_STANDURL");
  $dbaccess = $action->GetParam("FREEDOM_DB");

  // Get all the params      
  $dirid=GetHttpVars("dirid"); // directory to refresh
  




  $oqd = new QueryDir($dbaccess);
  $oqd->RefreshDir($dirid);
  redirect($action,GetHttpVars("app"),"FREEDOM_VIEW&dirid=$dirid");
}
?>
