<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: barmenu.php,v 1.10 2004/03/16 14:12:46 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: barmenu.php,v 1.10 2004/03/16 14:12:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/barmenu.php,v $
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



include_once("FDL/Class.Dir.php");
include_once("FDL/Class.QueryDir.php");
include_once("FDL/freedom_util.php");  

  include_once("FDL/popup_util.php");



// -----------------------------------
function barmenu(&$action) {
  // -----------------------------------
  popupInit("newmenu",    array('newdoc','newfld','newprof','newfam','newwf'));
  popupInit("searchmenu", array( 'newsearch','newdsearch','newsearchfulltext'));
 
  popupInit("helpmenu", array('help','import','importtar'));


  popupActive("newmenu",1,'newdoc'); 
  popupActive("newmenu",1,'newfld'); 
  popupActive("newmenu",1,'newprof');
  if ($action->HasPermission("FREEDOM_MASTER")) {
    popupActive("helpmenu",1,'import'); 
    popupActive("helpmenu",1,'importtar'); 
    popupActive("newmenu",1,'newfam');
    popupActive("newmenu",1,'newwf'); 
  } else {
    popupInvisible("helpmenu",1,'import');
    popupInvisible("helpmenu",1,'importtar');
    popupInvisible("newmenu",1,'newfam');
    popupInvisible("newmenu",1,'newwf'); 
  }
  popupActive("searchmenu",1,'newsearch');
  popupActive("searchmenu",1,'newdsearch');
  if ($action->GetParam("FULLTEXT_SEARCH") == "yes") popupActive("searchmenu",1,'newsearchfulltext');
  else popupInvisible("searchmenu",1,'newsearchfulltext');
 
  popupActive("helpmenu",1,'help');


  popupGen(1);

}
?>
