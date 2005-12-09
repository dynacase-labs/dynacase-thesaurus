<?php
/**
 * Relation Navigation
 *
 * @author Anakeen 2005
 * @version $Id: rnavigate.php,v 1.1 2005/12/09 17:21:42 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocRel.php");



function rnavigate(&$action) {
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid= GetHttpVars("id");


  $doc = new_Doc($dbaccess, $docid);
  $idocid=$doc->initid;

  $rdoc=new DocRel($dbaccess,$idocid);
  $action->lay->set("Title",$doc->title);
  $trel=$rdoc->getRelations();
  foreach ($trel as $k=>$v) {
    $trel[$k]["iconsrc"]=$doc->getIcon($v["icon"]);
  }

  $action->lay->setBlockData("RELS",$trel);
}



function rnavigate2(&$action) {
  header('Content-type: text/xml; charset=iso8859-1'); 
  rnavigate($action);
}

?>