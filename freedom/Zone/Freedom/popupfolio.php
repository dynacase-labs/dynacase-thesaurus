<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: popupfolio.php,v 1.3 2003/08/18 15:47:04 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: popupfolio.php,v 1.3 2003/08/18 15:47:04 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Freedom/popupfolio.php,v $
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

include_once("FDL/Class.Doc.php");
// -----------------------------------
function popupfolio(&$action) {
  // -----------------------------------
  // ------------------------------
  // get all parameters
  $dirid=GetHttpVars("dirid"); // 
  $folioid=GetHttpVars("folioid"); // portfolio id

  $kdiv=1; // only one division

  $dir = new Doc($dbaccess,$dirid);
  include_once("FDL/popup_util.php");
  // ------------------------------------------------------
  // definition of popup menu
  popupInit('popupfolio',  array('newdoc','newgc','newsgc',
				 'cancelf'));


  if ($dir->doctype == "D") {

    Popupactive('popupfolio',$kdiv,'newdoc');
    Popupactive('popupfolio',$kdiv,'newgc');
    Popupactive('popupfolio',$kdiv,'newsgc');
    Popupactive('popupfolio',$kdiv,'cancelf');
  } else {
    Popupinvisible('popupfolio',$kdiv,'newdoc');
    Popupinvisible('popupfolio',$kdiv,'newgc');
    Popupinvisible('popupfolio',$kdiv,'newsgc');
    Popupactive('popupfolio',$kdiv,'cancelf');
  }
  popupGen($kdiv);


  // set dirid to folio if is in search
  if ($dir->doctype=='S') $action->lay->set("dirid",$folioid);
  else $action->lay->set("dirid",$dirid);
  


  setFamidInLayout($action);
}