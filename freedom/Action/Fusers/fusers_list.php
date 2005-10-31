<?php
/**
 * display users and groups list
 *
 * @author Anakeen 2000 
 * @version $Id: fusers_list.php,v 1.4 2005/10/31 11:48:54 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

include_once("FDL/Lib.Dir.php");
function fusers_list(&$action) {

  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/common.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");

  $dbaccess = $action->GetParam("FREEDOM_DB");
 

 


  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/mktree.js");

  $action->lay->set("isMaster", $action->parent->Haspermission("FUSERS_MASTER"));



  $user = new User();
  $ugroup=$user->GetGroupsId();
    
  $q2= new queryDb("","User");
  $groups=$q2->Query(0,0,"TABLE","select users.*, groups.idgroup, domain.name as domain from users, groups, domain where users.id = groups.iduser and users.iddomain=domain.iddomain and users.isgroup='Y'");

  $q2= new queryDb("","User");
  $mgroups=$q2->Query(0,0,"TABLE","select users.*, domain.name as domain from users,domain where users.iddomain=domain.iddomain and isgroup='Y' and id not in (select iduser from groups)");
  
  if ($groups) {
    foreach ($groups as $k=>$v) {
      $groupuniq[$v["id"]]=$v;
      $groupuniq[$v["id"]]["checkbox"]="";
      if (in_array($v["id"],$ugroup)) 	 $groupuniq[$v["id"]]["checkbox"]="checked";
    }
  }
  if (!$groups) $groups=array();
  if ($mgroups) {
    foreach ($mgroups as $k=>$v) {
	$cgroup=fusers_getChildsGroup($v["id"],$groups);
	$tgroup[$k]=$v;
	$tgroup[$k]["SUBUL"]=$cgroup;	
      $groupuniq[$v["id"]]=$v;
      $groupuniq[$v["id"]]["checkbox"]="";
      if (in_array($v["id"],$ugroup)) $groupuniq[$v["id"]]["checkbox"]="checked";
    }
  }
  $action->lay->setBlockData("LI",$tgroup);
  $action->lay->setBlockData("SELECTGROUP",$groupuniq);

  $action->lay->set("expand", (count($groups) < 15));
}

/**
 * internal function use for choosegroup
 * use to compute displayed group tree
 */
function fusers_getChildsGroup($id,$groups) {
  $tlay=array();
  foreach ($groups as $k=>$v) {
    if ($v["idgroup"]==$id) {
      $tlay[$k]=$v;
       $tlay[$k]["SUBUL"]=fusers_getChildsGroup($v["id"],$groups);

    }
  }
  
  if (count($tlay)==0) return "";
  global $action;
  $lay = new Layout("FUSERS/Layout/fusers_ligroup.xml",$action);
  $lay->setBlockData("LI",$tlay);
  return $lay->gen();
}
?>