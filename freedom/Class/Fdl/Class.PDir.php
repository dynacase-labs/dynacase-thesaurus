<?php
/**
 * Profile for folders
 *
 * @author Anakeen 2000 
 * @version $Id: Class.PDir.php,v 1.10 2005/06/28 08:37:46 eric Exp $
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
  var $acls = array("view","edit","delete","open","modify","send","unlock","confidential");
  // --------------------------------------------------------------------
  
  
  var $defDoctype='P';
  var $defProfFamId=FAM_ACCESSDIR;

  function __construct($dbaccess='', $id='',$res='',$dbid=0) {
    // don't use Doc constructor because it could call this constructor => infinitive loop
     DocCtrl::__construct($dbaccess, $id, $res, $dbid);
  }



}

?>