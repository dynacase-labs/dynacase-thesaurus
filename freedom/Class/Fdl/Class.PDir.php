<?php
/**
 * Profile for folders
 *
 * @author Anakeen 2000 
 * @version $Id: Class.PDir.php,v 1.12 2007/10/11 12:35:10 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 */
 /**
 */



include_once("FDL/Class.Doc.php");


Class PDir extends Doc
{
  // --------------------------------------------------------------------
  //---------------------- OBJECT CONTROL PERMISSION --------------------
  var $acls = array("view","edit","delete","open","modify","send","unlock","confidential","forum");
  // --------------------------------------------------------------------
  
  
  var $defDoctype='P';
  var $defProfFamId=FAM_ACCESSDIR;

  function __construct($dbaccess='', $id='',$res='',$dbid=0) {
    // don't use Doc constructor because it could call this constructor => infinitive loop
     DocCtrl::__construct($dbaccess, $id, $res, $dbid);
  }
}

?>