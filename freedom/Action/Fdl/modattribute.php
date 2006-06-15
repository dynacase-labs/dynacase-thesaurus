<?php
/**
 * Specific menu for family
 *
 * @author Anakeen 2000 
 * @version $Id: modattribute.php,v 1.6 2006/06/15 15:58:49 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/Class.Doc.php");
/**
 * Modify an attribute inline
 * @param Action &$action current action
 * @global docid Http var : document identificator to modify
 * @global attrid Http var : the id of attribute to modify
 * @global value Http var : the new  value for attribute
 * @global stayedit Http var : stay in edition
 */
function modattribute(&$action) {
  $docid = GetHttpVars("docid");
  $attrid = GetHttpVars("attrid");
  $value = GetHttpVars("value");
  $stayedit = (GetHttpVars("stayedit")=="yes");
  $dbaccess = $action->GetParam("FREEDOM_DB");


  header('Content-type: text/xml; charset=utf-8'); 

  $mb=microtime();

  $action->lay->set("CODE","OK");
  $action->lay->set("warning","");
 

  
  $doc = new_Doc($dbaccess, $docid);
  if (! $doc->isAffected()) $err=sprintf(_("cannot see unknow reference %s"),$docid);


  if (! $stayedit) {
    $err = $doc->unlock(true); // autounlock

    if ($err=="") $action->AddActionDone("UNLOCKFILE",$doc->id);
  }
  if ($value != "") {

    if ($err != "") {    
      // test object permission before modify values (no access control on values yet)
      $err=$doc->CanUpdateDoc();
    }


    if ($err=="") {
      $a=$doc->getAttribute($attrid);
      if (! $a)  $err=sprintf(_("unknown attribute %s for document %s"),$attrid,$doc->title);
      if ($err=="") {
	$vis=$a->mvisibility;
	if (strstr("WO", $vis) === false)  $err=sprintf(_("visibility %s does not allow modify attribute %s for document %s"),$vis,$a->labelText,$doc->title);
	if ($err == "") {    
	  if ($a->type == "file") {
	    $err=$doc->SetTextValueInFile($attrid,$value);
	     
	  } else {
	    $err=$doc->setValue($attrid,$value);
	  }
	  if ($err == "") {    
	    $err=$doc->modify(); 
	    if ($err == "") {
	      $action->AddActionDone("MODATTR",$a->id);
	      $doc->AddComment(sprintf(_("modify [%s] attribute"),$a->labelText),HISTO_NOTICE,"MODATTR");
	    }
	  }
	}
	$action->lay->set("thetext",$doc->getHtmlAttrValue($attrid));
      }
    }

  } else {
    if ($attrid) $action->lay->set("thetext",$doc->getHtmlAttrValue($attrid)); 
  }
  $action->lay->set("warning",utf8_encode($err));
  $action->lay->set("count",1);
  $action->lay->set("delay",microtime_diff(microtime(),$mb));

  // notify actions done
  $action->getActionDone($actcode,$actarg);
  $tact=array();
  foreach ($actcode as $k=>$v) {
    $tact[]=array("acode"=>$v,
		  "aarg"=>$actarg[$k]);
  }
  $action->lay->setBlockData("ACTIONS",$tact);
  $action->clearActionDone();
}


?>