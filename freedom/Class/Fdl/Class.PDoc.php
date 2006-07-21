<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Class.PDoc.php,v 1.12 2006/07/21 15:34:05 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 */
 /**
 */


include_once("FDL/Class.Doc.php");




Class PDoc extends Doc
{
    // --------------------------------------------------------------------
  //---------------------- OBJECT CONTROL PERMISSION --------------------
  var $acls = array("view","edit","delete","send","unlock","confidential");
  // --------------------------------------------------------------------
  
 
  // ------------
  var $defDoctype='P';
  var $defProfFamId=FAM_ACCESSDOC;

  function __construct($dbaccess='', $id='',$res='',$dbid=0) {
    // don't use Doc constructor because it could call this constructor => infinitive loop
     DocCtrl::__construct($dbaccess, $id, $res, $dbid);
  }


}

?>