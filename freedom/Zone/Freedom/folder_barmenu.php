<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: folder_barmenu.php,v 1.4 2005/03/23 17:05:19 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: folder_barmenu.php,v 1.4 2005/03/23 17:05:19 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Freedom/folder_barmenu.php,v $
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
function folder_barmenu(&$action) {
  // -----------------------------------
  // Get all the params 
  $nbdoc=GetHttpVars("nbdoc");
  $dirid=GetHttpVars("dirid");
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $dir = new Doc($dbaccess, $dirid);


  $action->lay->set("title",$dir->getTitle());
  $action->lay->set("pds",$dir->urlWhatEncodeSpec(""));  // parameters for searches
  if ($nbdoc > 1)  $action->lay->set("nbdoc",sprintf(_("%d documents found"),$nbdoc));
  else $action->lay->set("nbdoc",sprintf(_("%d document found"),$nbdoc));

  $action->lay->set("dirid",$dirid);

  popupInit("viewmenu",	array('vlist','vicon','vcol'));
  popupInit("toolmenu", array('tobasket','insertbasket','clear','props'));



  popupActive("viewmenu",1,'vlist');
  popupActive("viewmenu",1,'vicon');
  popupActive("viewmenu",1,'vcol');

  // clear only for basket :: too dangerous
  if ($dir->fromid == getFamIdFromName($dbaccess,"BASKET")) {
    popupInvisible("toolmenu",1,'tobasket');
    popupInvisible("toolmenu",1,'insertbasket');
    popupActive("toolmenu",1,'clear');
  } else {
    popupActive("toolmenu",1,'tobasket');
    popupActive("toolmenu",1,'insertbasket');
    popupInvisible("toolmenu",1,'clear');
  }
  popupActive("toolmenu",1,'props');


  popupGen(1);

}
?>
