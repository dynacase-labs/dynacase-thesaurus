<?php
/**
 * Folder barmenu
 *
 * @author Anakeen 2000 
 * @version $Id: folder_barmenu.php,v 1.13 2007/01/26 16:16:01 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */





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
  $target=GetHttpVars("target"); // target for hyperlinks
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $dir = new_Doc($dbaccess, $dirid);


  $action->lay->Set("wtarget",$target);
  $action->lay->set("title",$dir->getTitle());
  $action->lay->set("pds",$dir->urlWhatEncodeSpec(""));  // parameters for searches
  if ($nbdoc > 1)  $action->lay->set("nbdoc",sprintf(_("%d documents"),$nbdoc));
  else $action->lay->set("nbdoc",sprintf(_("%d document"),$nbdoc));

  $action->lay->set("dirid",$dirid);

  popupInit("viewmenu",	array('vlist','vicon','vcol','vdetail'));
  popupInit("toolmenu", array('tobasket','insertbasket','clear','props','openfolio','applybatch','export','exportprof'));



  popupActive("viewmenu",1,'vlist');
  popupActive("viewmenu",1,'vicon');
  popupActive("viewmenu",1,'vcol');
  popupActive("viewmenu",1,'vdetail');

  // clear only for basket :: too dangerous
  if ($dir->fromid == getFamIdFromName($dbaccess,"BASKET")) {
    popupInvisible("toolmenu",1,'tobasket');
    popupInvisible("toolmenu",1,'insertbasket');
    popupActive("toolmenu",1,'clear');
  } else {
    popupActive("toolmenu",1,'tobasket');	
    if ($dir->defDoctype != 'D') popupInvisible("toolmenu",1,'insertbasket');
    else popupActive("toolmenu",1,'insertbasket');
    popupInvisible("toolmenu",1,'clear');
  }
  popupActive("toolmenu",1,'props');
  popupActive("toolmenu",1,'openfolio'); 
  if ($action->HasPermission("FREEDOM_ADMIN")) {
    popupActive("toolmenu",1,'applybatch');
  }
  if ($action->HasPermission("EXPORT","FDL")) {
    popupActive("toolmenu",1,'export');
    popupActive("toolmenu",1,'exportprof');
  }


  popupGen(1);

}
?>
