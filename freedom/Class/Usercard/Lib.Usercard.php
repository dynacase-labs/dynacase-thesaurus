<?php
/**
 * Function utilities to manipulate users
 *
 * @author Anakeen 2004
 * @version $Id: Lib.Usercard.php,v 1.5 2007/02/16 07:35:54 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
 /**
 */
include_once("Class.Group.php");
include_once("FDL/Class.Dir.php");

/**
 * refresh a set of group
 * @param array $tg the groups which has been modify by insertion/deletion of user
 * @return 
 */


global $wg;
$wg=new group("",2); // working group







function refreshGroups($tg,$refresh=false) {
  global $wg;
  
  if (count($tg)==0) return array();
  reset($tg);
  $g=current($tg);
  $lc = $wg->getChildsGroupId($g);
  $it = array_intersect($lc, $tg);
  if (count($it) > 0) {
    array_unset($tg,current($it));
    array_unshift($tg,current($it));
    $tg=refreshGroups($tg,$refresh);
  } else {
    $lp=$wg->getParentsGroupId($g);
 
    //add not direct ascendant
    foreach ($lp as $gid) {
      $tg[$gid]=$gid;;
    }
    refresgOneGroup($g,$refresh);
    array_unset($tg,$g);
    array_unique($tg);
    $tg=refreshGroups($tg,$refresh);
  }
  

  return $tg;
}
function array_unset(&$t,$vp) {
  foreach ($t as $k=>$v) {
    if ($v == $vp) unset($t[$k]);
  }
}


function refresgOneGroup($gid,$refresh) {
  global $_SERVER;
  $g=new User("",$gid);
  if ($g->fid > 0) {
    $dbaccess=GetParam("FREEDOM_DB");
    $doc = new_Doc($dbaccess,$g->fid);
    if ($doc->isAlive()) {
      if ($_SERVER['HTTP_HOST'] == "") print sprintf("\trefreshing %s\n",$doc->title);
      wbartext(sprintf(_("refreshing %s"),$doc->title));
      if ($refresh) $doc->refreshMembers();
      $doc->SetGroupMail(($doc->GetValue("US_IDDOMAIN")>1));
      $doc->modify();
      $doc->specPostInsert();
      $doc->setValue("grp_isrefreshed","1");
      $doc->modify(true,array("grp_isrefreshed"),true);      
    }
  }
}

?>