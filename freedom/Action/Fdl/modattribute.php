<?php
/**
 * Specific menu for family
 *
 * @author Anakeen 2000 
 * @version $Id: modattribute.php,v 1.1 2006/05/11 07:15:14 eric Exp $
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
 */
function modattribute(&$action) {
  $docid = GetHttpVars("docid");
  $attrid = GetHttpVars("attrid");
  $value = GetHttpVars("value");
  $dbaccess = $action->GetParam("FREEDOM_DB");


  header('Content-type: text/xml; charset=utf-8'); 

  $mb=microtime();

  $action->lay->set("CODE","OK");
  $action->lay->set("warning","");
 

  
  $doc = new_Doc($dbaccess, $docid);
  if (! $doc->isAffected()) $err=sprintf(_("cannot see unknow reference %s"),$docid);

  $err = $doc->unlock(true); // autounlock
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
	  $err=$doc->setValue($attrid,$value);
	  if ($err == "") {    
	    $err=$doc->modify(); 
	    if ($err == "") $doc->AddComment(sprintf(_("modify [%s] attribute"),$a->labelText));
	  }
	}
	$action->lay->set("thetext",$doc->getHtmlAttrValue($attrid));
      }
    }

  } else {
    $action->lay->set("thetext",$doc->getHtmlAttrValue($attrid));    
  }
  $action->lay->set("warning",utf8_encode($err));
  $action->lay->set("count",1);
  $action->lay->set("delay",microtime_diff(microtime(),$mb));
}


?>