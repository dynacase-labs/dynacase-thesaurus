<?php
/**
 * Intranet User & Group  manipulation
 *
 * @author Anakeen 2004
 * @version $Id: Method.DocIntranet.php,v 1.2 2004/07/07 07:00:23 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
 /**
 */


/**
 * verify if the login syntax is correct and if the login not already exist
 * @param string $login login to test
 * @return array 2 items $err & $sug for view result of the constraint
 */
function ConstraintLogin($login) {
  $sug=array();
  $id=$this->GetValue("US_WHATID");
  $user=new User("",$id);
                                         
  if ($login == "") $err= _("the login must not be empty");
  else {
    if (!ereg("^[a-z][a-z0-9\.]+[a-z0-9]+$", $login)) {$err= _("the login syntax is like : john.doe");}

    if ($user->isAffected()) $iddomain=$user->iddomain;
    else $iddomain=1;
  

    $q=new QueryDb("","User");
    $q->AddQuery("login='$login'");
    $q->AddQuery("id != $id");
    $q->AddQuery("iddomain=$iddomain");
    $q->Query(0,0,"TABLE");
    if ($q->nb > 0) $err= _("login yet use");
  }
  return array("err"=>$err,"sug"=>$sug);
}

function preCreated() {
  if ($this->getValue("US_WHATID") != "") return _("what id already set in freedom\nThis kind of document can not be duplicated");
}

?>
