<?php
/**
 * Folio List Containt
 *
 * @author Anakeen 2003
 * @version $Id: foliolist.php,v 1.9 2004/06/17 14:53:07 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */



include_once('FREEDOM/freedom_view.php');


/**
 * View a containt of portfolio separator
 * @param Action &$action current action
 * @global dirid Http var : separator identificator to see
 * @global folioid Http var : portfolio of separator
 */
function foliolist(&$action) {
  // Get all the params      
  $dirid=GetHttpVars("dirid"); // directory to see
  $folioid=GetHttpVars("folioid"); // portfolio id
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $filter=array();
  $filter[]="doctype = 'F'";
  $dir = new Doc($dbaccess,$dirid);
  if (($dir->doctype == 'S')) {
    if ($dir->usefor == 'G'){
      // recompute search to restriction to local folder
      // only for filters
      $dir->id="";
      $dir->initid="";
      $dir->doctype='T';
      $dir->setValue("SE_IDFLD",$folioid);
      $dir->setValue("SE_SUBLEVEL","1");
      $dir->Add();
      $dir->SpecRefresh();
      $dir->Modify();
      SetHttpVar("dirid",$dir->initid); // redirect dirid to new temporary search
    
    } else {
      // recompute search to add current father folder
      //     $dir->id="";
      //     $dir->initid="";
      //     $dir->doctype='T';
      if (($folioid > 0) && ($dir->getValue("SE_IDCFLD")!=$folioid)) {
	$dir->setValue("SE_IDCFLD",$folioid);
	//     $dir->Add();
	$dir->SpecRefresh();
	$dir->Modify();
      }
      //    SetHttpVar("dirid",$dir->initid); // redirect dirid to new temporary search
    
      
    }
  }

  

  $action->parent->SetVolatileParam("FREEDOM_VIEW", "icon");
  viewfolder($action, false,true,false,
	     100,$filter);
  


}
?>
