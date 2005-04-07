<?php
/**
 * popup for portfolio list
 *
 * @author Anakeen 2000 
 * @version $Id: popupfolio.php,v 1.7 2005/04/07 12:15:49 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// 

include_once("FDL/Class.Doc.php");
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
				 'insertbasket','searchinsert'));

  Popupinvisible('popupfolio',$kdiv,'insertbasket');
  Popupinvisible('popupfolio',$kdiv,'searchinsert');
  Popupinvisible('popupfolio',$kdiv,'newdoc');
  Popupinvisible('popupfolio',$kdiv,'newgc');
  Popupinvisible('popupfolio',$kdiv,'newsgc');

  if ($dir->doctype == "D") {
    Popupactive('popupfolio',$kdiv,'newdoc');
    Popupactive('popupfolio',$kdiv,'insertbasket');
    Popupactive('popupfolio',$kdiv,'searchinsert');
    if ($dir->usefor  != "G") {
      Popupactive('popupfolio',$kdiv,'newgc');
      Popupactive('popupfolio',$kdiv,'newsgc');
    }
  } 
  popupGen($kdiv);


  // set dirid to folio if is in search
  if ($dir->doctype=='S') $action->lay->set("dirid",$folioid);
  else $action->lay->set("dirid",$dirid);
  


  setFamidInLayout($action);
}