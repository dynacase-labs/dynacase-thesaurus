<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_gaccess.php,v 1.6 2004/03/01 08:51:20 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: freedom_gaccess.php,v 1.6 2004/03/01 08:51:20 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_gaccess.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2001
// O*O  Anakeen development team
//  O   dev@anakeen.com
// ---------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------

include_once("FDL/Class.Doc.php");
include_once("FDL/Class.VGroup.php");




  // -----------------------------------
function freedom_gaccess(&$action) {
  // -----------------------------------
  // 
  // edition of group accessibilities
  // ---------------------

  // Get all the params   
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid= GetHttpVars("id");
  $gid= GetHttpVars("gid"); // view user access for the gid group (view all groups if null)

  // 
  // edition of group accessibilities
  // ---------------------
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");



  $doc = new Doc($dbaccess, $docid);
  $err= $doc->control("viewacl");
  if ($err != "") $action->exitError($err);


  $acls = $doc->acls;
  $acls[]="viewacl";
  $acls[]="modifyacl"; //add this acl global for every document

  // contruct headline
  reset($acls);
  $hacl=array();
  while(list($k,$v) = each($acls)) {
    $hacl[$k]["aclname"]=_($v);
    $hacl[$k]["acldesc"]=_($doc->dacls[$v]["description"]);
  }
  
  $action->lay->SetBlockData("DACLS", $hacl);
  $action->lay->Set("title", $doc->title);
  $tg=array(); // users or group list

  if ($gid == 0) {
    //-----------------------
    // contruct grouplist
    $ouser = new User();
    $tiduser = $ouser->GetGroupList("TABLE");
    $hg= array();
    $userids= array();
    $sgroup=array(); // all group which are in a group i.e. not the root group

    foreach($tiduser as $k=>$v) {
      $g = new Group("",$v["id"]);

      $title[$v["id"]]=$v["firstname"]." ".$v["lastname"];

      foreach($g->groups as $kg=>$gid) {

	$hg[$gid][$v["id"]]=$v["id"];
	$sgroup[$v["id"]]=$v["id"];// to define root group
      }

    }


    foreach($hg as $k=>$v) {
      if (! in_array( $k, $sgroup)) {
	// it's a root group
	$tg = array_merge($tg,getTableG($hg, $k));
      }
    }
    if ($action->user->id > 1) {
      $tg[]=array("level"=>0,
		  "gid"=>$action->user->id,
		  "displayuser"=>"inline",
		  "displaydyn"=>"none",
		  "displaygroup"=>"none");
      $title[$action->user->id]=$action->user->firstname." ".$action->user->lastname;
    }
  
  } else {
    //-----------------------
    // contruct user list
    $ouser = new User("", $gid);
    $tusers = $ouser->getGroupUserList("TABLE");

    $tg[]=array("level"=>0,
		"gid"=>$gid,
		"displayuser"=>"none",
		"displaydyn"=>"none",
		"displaygroup"=>"inline");
    $title[$gid]=$ouser->firstname." ".$ouser->lastname;
    if ($tusers) {
    
    
      foreach($tusers as $k=>$v) {

	if ($k > 100) {
	  $action->AddWarningMsg(sprintf(_("Not all users can be vieved.\nlimit %d has been reached"),$k));
	  break;
	}
	$title[$v["id"]]=$v["firstname"]." ".$v["lastname"];
	$tg[]=array("level"=>10,
		    "gid"=>$v["id"],
		    "displaydyn"=>"none",
		    "displayuser"=>"inline",
		    "displaygroup"=>"none");
      }
    }
  }

  // add dynamic group for dynamic profile
  if ($doc->getValue("DPDOC_FAMID") > 0) {
    
   
    $pdoc = new Doc($dbaccess , $doc->getValue("DPDOC_FAMID"));
    $pattr = $pdoc->GetProfilAttributes();
    foreach($pattr as $k=>$v) {

      $vg=new Vgroup($dbaccess,$v->id);
      if (! $vg->isAffected()) {
	$vg->id=$v->id;
	$vg->Add();
      }
      $tg[]=array("level"=>0,
		  "gid"=>$vg->num,
		  "displaydyn"=>"inline",
		  "displayuser"=>"none",
		  "displaygroup"=>"none");
      $title[$vg->num]=$v->labelText;
    }
    
  }


  // add  group title
  foreach($tg as $k=>$v) {
    $tacl[$v["gid"]]=getTacl($dbaccess,$doc->dacls, $acls, $docid, $v["gid"]);
    $tg[$k]["gname"]=$title[$v["gid"]];
    $tg[$k]["ACLS"]="ACL$k";
    $action->lay->setBlockData("ACL$k",$tacl[$v["gid"]]);
  }
  


  $action->lay->setBlockData("GROUPS",$tg);
  $action->lay->set("docid",$docid);


  $err= $doc->control("modifyacl");
  if ($err == "") {
    $action->lay->setBlockData("MODIFY",array(array("zou")));
    $action->lay->set("dmodify","");
  } else $action->lay->set("dmodify","none");
}

//--------------------------------------------
function getTableG($hg,$id, $level=0) {
//--------------------------------------------

  $r[]=array("gid"=>$id,
	     "level"=>$level*10,
	     "displayuser"=>"none",
	     "displaydyn"=>"none",
	     "displaygroup"=>"inline");
  if (isset($hg[$id])) {
    reset($hg[$id]);
    while(list($kg,$gid) = each($hg[$id])) {
      $r=array_merge($r, getTableG($hg,$gid, $level+1));
    }
  } 

  return $r;
  
}

//--------------------------------------------
function getTacl($dbaccess,$dacls, $acls, $docid,$gid) {
//--------------------------------------------
  
  $perm = new DocPerm($dbaccess, array($docid,$gid));
  

  foreach($acls as $k=>$v ) {


    $tableacl[$k]["aclname"]=$v;
      $pos=$dacls[$v]["pos"];

      $tableacl[$k]["aclid"]=$pos;
      $tableacl[$k]["iacl"]=$k; // index for table in xml
      if ($perm->ControlUp($pos)) {
	    $tableacl[$k]["selected"]="checked";
	    $tableacl[$k]["bimg"]="bgreen.gif";
      } else {
	    $tableacl[$k]["selected"]="";
	    if ($perm->ControlU($pos)) {
	      $tableacl[$k]["bimg"]="bgrey.gif";
	    } else {
	      if ($perm->ControlUn($pos)) $tableacl[$k]["bimg"]="bred.gif";
	      else $tableacl[$k]["bimg"]="1x1.gif";
	    }
      }
  }

  return $tableacl;
}

?>
