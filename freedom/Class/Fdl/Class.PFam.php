<?php
/**
 * Profil for family document
 *
 * @author Anakeen 2000 
 * @version $Id: Class.PFam.php,v 1.4 2005/06/28 08:37:46 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */




include_once("FDL/Class.Doc.php");


Class PFam extends Doc
{
  // --------------------------------------------------------------------
  //---------------------- OBJECT CONTROL PERMISSION --------------------
  var $acls = array("view","edit","delete","create","icreate");
  // --------------------------------------------------------------------
  
  
  var $defDoctype='P';
  var $defProfFamId=FAM_ACCESSFAM;

  function __construct($dbaccess='', $id='',$res='',$dbid=0) {
    // don't use Doc constructor because it could call this constructor => infinitive loop
     DocCtrl::__construct($dbaccess, $id, $res, $dbid);
  }



}

?>