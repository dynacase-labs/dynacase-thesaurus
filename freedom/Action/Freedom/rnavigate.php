<?php
/**
 * Relation Navigation
 *
 * @author Anakeen 2005
 * @version $Id: rnavigate.php,v 1.3 2007/07/02 13:21:07 eric Exp $
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
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/common.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");


  $doc = new_Doc($dbaccess, $docid);
  $idocid=$doc->initid;

  $rdoc=new DocRel($dbaccess,$idocid);
  $rdoc->sinitid=$idocid;

  $action->lay->set("Title",$doc->title);
  $tlay=array();
  
  $trel=$rdoc->getIRelations();
  foreach ($trel as $k=>$v) {
    $tlay[$v["sinitid"].'_F']=array("iconsrc"=>$doc->getIcon($v["sicon"]),
			       "initid"=>$v["sinitid"],
			       "title"=>$v["stitle"],
			       "aid"=>$v["type"],
				    "alabel"=>_($v["type"]),
			       "type"=>_("Referenced from"));
  }
  $trel=$rdoc->getRelations();
  foreach ($trel as $k=>$v) {
    $tlay[$v["cinitid"].'_T']=array("iconsrc"=>$doc->getIcon($v["cicon"]),
			       "initid"=>$v["cinitid"],
			       "title"=>$v["ctitle"],
			       "aid"=>$v["type"],
				    "alabel"=>_($v["type"]),
			       "type"=>_("Reference"));
  }
  
  if (count($tlay)>0) {
    foreach ($tlay as $k=>$v) {
      $taid[$v["aid"]]=$v["aid"];
    }
    $q=new QueryDb($dbaccess,"DocAttr");
    $q->AddQuery(GetSqlCond($taid,"id"));
    $l=$q->Query(0,0,"TABLE");
    if ($l) {
      $la=array();
      foreach ($l as $k=>$v) {
	$la[$v["id"]]=$v["labeltext"];
      }
      foreach ($tlay as $k=>$v) {
	if ($la[$v["aid"]]) $tlay[$k]["alabel"]=$la[$v["aid"]];
	else if ($tlay[$k]["aid"]=='folder') $tlay[$k]["alabel"]=_("folder");
      }
    }
  }
  $action->lay->setBlockData("RELS",$tlay);
  $action->lay->set("docid",$docid);
}



function rnavigate2(&$action) {
  header('Content-type: text/xml; charset=iso8859-1'); 
  rnavigate($action);
}

?>