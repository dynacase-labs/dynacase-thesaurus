<?php
/**
 * Use to help to the construction of batch document
 *
 * @author Anakeen 2005
 * @version $Id: freedom_applybatch.php,v 1.4 2005/10/05 14:37:58 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */



include_once("FDL/Class.Doc.php");  
include_once("FDL/Lib.Dir.php");  




/**
 * Choose a batch document
 * @param Action &$action current action
 * @global id Http var : folder identificator to use to construct batch
 */
function freedom_applybatch(&$action) {

  $dirid = GetHttpVars("id"); 
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $bdoc=new_Doc($dbaccess,"BATCH");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
 
  $tb=$bdoc->getChildFam();
  $q=new QueryDb($dbaccess, "docAttr");
  foreach ($tb as $k=>$v) {
    $tb[$k]["iconsrc"]=$bdoc->getIcon($v["icon"]);

    $adoc="Adoc".$v["id"];
    $fa=new ADoc$adoc;
    print_r2($fa);

    $q->resetQuery();
    $q->AddQuery("docid=".$v["id"]);
    $q->AddQuery("type='action'");
    $q->AddQuery("docid=".$v["id"]);
    $la=$q->Query(0,0,"TABLE");
    $ta=array();
    if ($la) {
      foreach ($la as $ka=>$va) {
	$ta[]=$va["labeltext"];
      }
    }
    $tb[$k]["actions"]=implode(",<br>",$ta);

  }

  $action->lay->setBlockData("BATCHFAMS",$tb);
  $action->lay->set("dirid",$dirid);

}

?>