<?php
/**
 * Profile for folders
 *
 * @author Anakeen 2000 
 * @version $Id: Class.PDir.php,v 1.8 2004/04/27 09:21:16 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Doc.php");


Class PDir extends Doc
{
  // --------------------------------------------------------------------
  //---------------------- OBJECT CONTROL PERMISSION --------------------
  var $acls = array("view","edit","delete","open","modify","send","unlock");
  // --------------------------------------------------------------------
  
  
  var $defDoctype='P';
  var $defProfFamId=FAM_ACCESSDIR;

  function PDir($dbaccess='', $id='',$res='',$dbid=0) {
    // don't use Doc constructor because it could call this constructor => infinitive loop
     DocCtrl::DocCtrl($dbaccess, $id, $res, $dbid);
  }



}

?>