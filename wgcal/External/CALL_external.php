<?php
/**
 * Functions used for edition help of USER, GROUP & SOCIETY Family
 *
 * @author Anakeen 2003
 * @version $Id: CALL_external.php,v 1.1 2005/03/30 10:04:40 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
/**
 */

include_once("FDL/Class.Dir.php");
include_once("FDL/Lib.Dir.php");
include_once("EXTERNALS/fdl.php");

// lcall(D,{USER},CALL_CONTACT):CALL_IDCONTACT,CALL_CONTACT,CALL_CONTACTMAIL,CALL_CONTACTPHONE,CALL_CONTACTMOBILE
function lcall($dbaccess, $famid, $strcontact) {
  global $action;
  $filter=array();
  if ($strcontact != "") $filter[]="title ~* '$strcontact'";
  $tinter = getChildDoc($dbaccess, 0 ,0,100, $filter,$action->user->id, "TABLE", $famid);
  $tr = array();
  while(list($k,$v) = each($tinter)) {
    $c_mail = getv($v,"us_mail");
    $c_phone = getv($v,"us_phone");
    $c_mobile = getv($v,"us_mobile");
    $tr[] = array($v["title"], $v["id"], $v["title"], $c_mail, $c_phone, $c_mobile );   
  }
  return $tr;
}

?>
  
  

}