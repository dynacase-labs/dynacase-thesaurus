<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Method.DocSiteTech.php,v 1.7 2004/01/14 16:02:15 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage INCIDENT
 */
 /**
 */





function UpdateContracts() {
  global $action;

  include_once("FDL/Lib.Dir.php");



  // contracts():SI_IDCONTRATS,SI_CONTRATS
  if ($this->initid > 0) {
    $filter[]="in_textlist(co_idsites, $this->initid)";
    $contract = getChildDoc($this->dbaccess, 0,0,"ALL", $filter,1,"TABLE","CONTRACT");
    $idc=array();
    $tc=array();
    while(list($k,$v) = each($contract)) {

      $idc[] = $v["id"];
      $tc[] = $v["title"];
    }


    $this->setValue("SI_IDCONTRACTS",$idc);
    $this->setValue("SI_CONTRACTS",$tc);
  }
  
}
	
?>