<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: foliolist.php,v 1.6 2003/10/09 12:08:42 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: foliolist.php,v 1.6 2003/10/09 12:08:42 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/foliolist.php,v $
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


include_once('FREEDOM/freedom_view.php');



// -----------------------------------
// -----------------------------------
function foliolist(&$action) {
// -----------------------------------
  // Get all the params      
  $dirid=GetHttpVars("dirid"); // directory to see
  $folioid=GetHttpVars("folioid"); // portfolio id
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $filter=array();
  $filter[]="doctype = 'F'";
  $dir = new Doc($dbaccess,$dirid);
  if (($dir->doctype == 'S')&& ($dir->usefor == 'G')){
    // recompute search to restriction to local folder
    $dir->id="";
    $dir->initid="";
    $dir->doctype='T';
    $dir->setValue("SE_IDFLD",$folioid);
    $dir->setValue("SE_SUBLEVEL","1");
    $dir->Add();
    $dir->SpecRefresh();
    $dir->Modify();
    SetHttpVar("dirid",$dir->initid); // redirect dirid to new temporary search
    
  }
  

  $action->parent->SetVolatileParam("FREEDOM_VIEW", "icon");
  viewfolder($action, false,true,
	     100,$filter);
  


}
?>
