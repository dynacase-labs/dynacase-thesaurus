<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: deldirfile.php,v 1.11 2004/03/25 11:10:09 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */



include_once("FDL/Class.Dir.php");
include_once("FDL/freedom_util.php");  



// -----------------------------------
function deldirfile(&$action) {
  // -----------------------------------



  // Get all the params      
  $dirid=GetHttpVars("dirid");
  $docid=GetHttpVars("docid");

  //  print "deldirfile :: dirid:$dirid , docid:$docid";


  $dbaccess = $action->GetParam("FREEDOM_DB");


  $dir = new Doc($dbaccess,$dirid);// use initial id for directories
  $err = $dir->DelFile($docid);
  if ($err != "") $action->exitError($err);

  
  redirect($action,GetHttpVars("app"),"FREEDOM_VIEW&dirid=$dirid");
  //RedirectSender($action);// return to sender
  

}




?>
