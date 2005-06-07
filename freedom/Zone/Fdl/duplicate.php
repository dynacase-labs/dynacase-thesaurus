<?php
/**
 * Duplicate a document
 *
 * @author Anakeen 2000 
 * @version $Id: duplicate.php,v 1.13 2005/06/07 16:07:13 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */






include_once("FDL/Class.Dir.php");


// -----------------------------------
function duplicate(&$action, $dirid, $docid,$temporary=false) {
  // -----------------------------------

  
  $dbaccess = $action->GetParam("FREEDOM_DB");

 


  // test if doc with values
  $doc= new Doc($dbaccess, $docid);

  if ($doc->isConfidential())  redirect($action,"FDL","FDL_CONFIDENTIAL&id=".$doc->id);
  
  $cdoc= $doc->getFamDoc();
 
  $err = $cdoc->control('create');
  if ($err != "") $action->exitError(sprintf(_("no privilege to create this kind (%d) of document"),$doc->fromid));


  $values = $doc->getValues();
  if (! is_array($values)) $action->exitError(_("this kind of document cannot be duplicate"));


  // initiate a copy of the doc

  $copy= $doc->copy($temporary);
  if (! is_object($copy)) $action->exitError($copy);
  
  $copy->title = _("duplication of")." ".$copy->title;

  
  if ($err != "") $action->exitError($err);
  

  $copy->SetTitle($copy->title);

  $copy->refresh();
  $copy->postmodify();
  $copy->modify();
  // add to the same folder
  
  if (($dirid > 0) && ($copy->id > 0)) {
    $fld = new Doc($dbaccess, $dirid);

    
    $err = $fld->AddFile($copy->id);
    if ($err != "") {
      $copy->Delete();
      $action->exitError($err);
    }
    
  } 


  $action->AddLogMsg(sprintf(_("new duplicate document is named : %s"),$copy->title));

  return $copy;
  
}


?>
