<?php
/**
 * Relation Navigation
 *
 * @author Anakeen 2005
 * @version $Id: rnavigate.php,v 1.2 2005/12/16 12:04:46 eric Exp $
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
  $rdoc->sinitid=$idocid;

  $action->lay->set("Title",$doc->title);
  $trel=$rdoc->getRelations();
  foreach ($trel as $k=>$v) {
    $tlay[$v["cinitid"]]=array("iconsrc"=>$doc->getIcon($v["cicon"]),
		  "initid"=>$v["cinitid"],
		  "title"=>$v["ctitle"],
		  "type"=>"To ".$v["type"]);
  }
  $trel=$rdoc->getIRelations();
  foreach ($trel as $k=>$v) {
    $tlay[$v["sinitid"]]=array("iconsrc"=>$doc->getIcon($v["sicon"]),
		  "initid"=>$v["sinitid"],
		  "title"=>$v["stitle"],
		  "type"=>"From ".$v["type"]);
  }

  $action->lay->setBlockData("RELS",$tlay);
}



function rnavigate2(&$action) {
  header('Content-type: text/xml; charset=iso8859-1'); 
  rnavigate($action);
}

?>