<?php
/**
 * add item and return html input of an attribute
 *
 * @author Anakeen 2008
 * @version $Id: addenumitem.php,v 1.1 2008/06/16 16:32:35 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/editutil.php");


/**
 * Display editor to fix a document version
 * @param Action &$action current action
 * @global docid Http var : document id 
 * @global aid Http var : attribute id
 */
function addenumitem(&$action) {
  $docid = GetHttpVars("docid");
  $attrid = GetHttpVars("aid");
  $key = GetHttpVars("key");

  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  $action->lay->template="addenumitem $docid $attrid <b>$key</b>";
  $doc=new_doc($dbaccess,$docid);
  if ($doc->isAlive()) {
    $action->lay->template="addenumitem/2 $docid $attrid <b>$key</b>";
    $oa=$doc->getAttribute($attrid);
    if ($oa) {
      $oa->addEnum($dbaccess,$key,$key);
      
      $i=getHtmlInput($doc,$oa,$key);
      $action->lay->template=$i;
    }
  }
}
?>