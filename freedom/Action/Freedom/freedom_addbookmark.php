<?php
/**
 * Add folder in user bookmarks
 *
 * @author Anakeen 2005 
 * @version $Id: freedom_addbookmark.php,v 1.1 2005/03/29 20:34:07 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
include_once("FDL/Class.Doc.php");
/**
 * Add folder bookmark
 * @param Action &$action current action
 * @global dirid Http var : folder identificator to add
 */
function freedom_addbookmark(&$action) {
  $dirid = GetHttpVars("dirid"); 

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $attrid="FREEDOM_UBOOK";
  $ubook=$action->GetParam($attrid);
  if (strlen($ubook)>2)   $tubook = explode('][',substr($ubook,1,-1));
  else $tubook=array();
  $tid=array();
  foreach ($tubook as $k=>$v) {
    list($id,$label)=explode("|",$v);
    $tid[$id]=$label;
  }
  // add new folder
  $doc= new Doc($dbaccess,$dirid);
  if ($doc->isAlive()) {
    $tid[$doc->initid]=$doc->title;
  }

  // recompose the paramters
  $newbook="";
  foreach ($tid as $k=>$v) {
    $newbook.="[$k|$v]";
  }


  print "freedom_addbookmark $dirid $newbook";
  $action->parent->param->Set($attrid,$newbook,PARAM_USER.$action->user->id,$action->parent->id);

}





?>
