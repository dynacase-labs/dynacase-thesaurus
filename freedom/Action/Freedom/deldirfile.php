<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: deldirfile.php,v 1.12 2005/04/05 17:29:38 eric Exp $
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
  $folio=GetHttpVars("folio","N")=="Y"; // return in folio

  //  print "deldirfile :: dirid:$dirid , docid:$docid";


  $dbaccess = $action->GetParam("FREEDOM_DB");


  $dir = new Doc($dbaccess,$dirid);// use initial id for directories
  $err = $dir->DelFile($docid);
  if ($err != "") $action->exitError($err);

  

  if ($folio) redirect($action,"FREEDOM","FOLIOLIST&dirid=".$dir->initid);
  else  redirect($action,"FREEDOM","FREEDOM_VIEW&dirid=".$dir->initid);
}




?>
