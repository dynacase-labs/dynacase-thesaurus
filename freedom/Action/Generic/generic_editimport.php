<?php
/**
 * Import document from CSV file
 *
 * @author Anakeen 2004
 * @version $Id: generic_editimport.php,v 1.11 2004/05/13 16:17:14 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */




include_once("FDL/Class.Dir.php");
include_once("GENERIC/generic_util.php");  

// -----------------------------------
function generic_editimport(&$action) {
  // -----------------------------------

  global $dbaccess;
  
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/selectbox.js");
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $homefld = new Doc( $dbaccess, getDefFld($action));

  


  $stree=getChildCatg($homefld->id, 1,true);

  reset($stree);
  
  $action->lay->SetBlockData("CATG",$stree);
  $action->lay->Set("topdir", getDefFld($action));
  
  $famid = getDefFam($action);


  // spec for csv file
  $doc=new Doc($dbaccess, $famid);

  $action->lay->Set("dtitle",sprintf(_("import <I>%s</I> documents from"),$doc->title));
  $lattr = $doc->GetImportAttributes();
  $format = "DOC;".$doc->id.";0;". getDefFld($action)."; ";

  foreach ($lattr as $k=>$attr) {
    $format .= $attr->labelText." ;";
  }
  $lattr = $doc->GetNormalAttributes();
  foreach ($lattr as $k=>$attr) {
    if ($attr->visibility =="O") continue; // only valuated attribut
    $tkey[]=array("idattr"=>$attr->id,
		  "lattr"=>$attr->labelText);
  }
  $lattr = $doc->GetImportAttributes();
  foreach ($lattr as $k=>$attr) {
    $tcol[]=array("idattr"=>$attr->id,
		  "lattr"=>$attr->labelText);
  }

  $action->lay->SetBlockData("AKEYS1",$tkey);
  $action->lay->SetBlockData("AKEYS2",$tkey);
  $action->lay->SetBlockData("COLUMNS",$tcol);
  $action->lay->Set("format",$format);
  $action->lay->Set("classid",$famid);

}


?>
