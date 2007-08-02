<?php
/**
 * popup for portfolio list
 *
 * @author Anakeen 2000 
 * @version $Id: popupfolio.php,v 1.11 2007/08/02 14:19:10 eric Exp $
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

  $dir = new_Doc($dbaccess,$dirid);
  $sub=$dir->getAuthorizedFamilies();

  $insertgc=true;
  $insertsgc=true;
  if (!$dir->norestrict)  {
    $keys=array_keys($sub);

    $insertgc=(in_array(18,$keys));
    $insertsgc=(in_array(19,$keys));
  }

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
    if ($dir->control("modify") == "") {
      Popupactive('popupfolio',$kdiv,'insertbasket');
      Popupactive('popupfolio',$kdiv,'searchinsert');
    }
    if ($dir->usefor  != "G") {
      if ($insertgc) popupactive('popupfolio',$kdiv,'newgc');
      if ($insertsgc) popupactive('popupfolio',$kdiv,'newsgc');
    }
  } 
  popupGen($kdiv);


  // set dirid to folio if is in search
  if ($dir->doctype=='S') $action->lay->set("dirid",$folioid);
  else $action->lay->set("dirid",$dirid);
  


  setFamidInLayout($action);
}
?>