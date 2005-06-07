<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Class.PDoc.php,v 1.9 2005/06/07 13:33:03 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
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

  function PDoc($dbaccess='', $id='',$res='',$dbid=0) {
    // don't use Doc constructor because it could call this constructor => infinitive loop
     DocCtrl::DocCtrl($dbaccess, $id, $res, $dbid);
  }
}

?>