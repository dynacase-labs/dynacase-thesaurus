<?php
// ---------------------------------------------------------------
// $Id: popup_util.php,v 1.8 2001/12/18 09:18:10 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Attic/popup_util.php,v $
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





function popupInit($name, $items) {
  global $menuitems;
  global $tmenus;

  
  // ------------------------------------------------------
  // definition of popup menu
  $menuitems= $items;
  $jsarray = "[";
  while (list($ki, $imenu) = each($menuitems)) {

    $jsarray .= "'".$imenu."',";
    global ${$imenu} ;
    ${$imenu} = 'v'.$ki;
  }
  // replace last comma by ']'
  $jsarray[strlen($jsarray)-1]="]";

  $tmenus[$name]["menuitems"] = $jsarray;
  $tmenus[$name]["name"] = $name;
  $tmenus[$name]["nbmitem"] = count($menuitems);

}

function popupInitItem($name, $k) {
  global $tmenuaccess;
  global $menuitems;

  if (! isset($tmenuaccess[$name][$k]["divid"])) {
    $tmenuaccess[$name][$k]["divid"] = $k;
    reset($menuitems);
    while (list($ki, $v) = each($menuitems)) {
      $tmenuaccess[$name][$k]['v'.$ki] = 2; // invisible

    }

  }
}

function popupActive($name,$k, $nameid) {
  global $tmenuaccess;
  global $$nameid;
  popupInitItem($name,$k);
  $tmenuaccess[$name][$k][$$nameid]=1;
}


function popupInactive($name,$k, $nameid) {
  global $tmenuaccess;
  global $$nameid;

  popupInitItem($name,$k);
  $tmenuaccess[$name][$k][$$nameid]=0;
}
function popupInvisible($name,$k, $nameid) {
  global $tmenuaccess;
  global $$nameid;

  popupInitItem($name,$k);
  $tmenuaccess[$name][$k][$$nameid]=2;
}

  function vcompare($a, $b) {
    $na = intval(substr($a,1));
    $nb = intval(substr($b,1));

    if ($na == $nb) return 0;
    return ($na < $nb) ? -1 : 1;
  }
function popupGen($kdiv) {  
  global $tmenuaccess;
  global $menuitems;
  global $tmenus;
  global $action;
  static $first=1;
  global $tcmenus; // closeAll menu


  if ($first) {
    // static part
    $action->parent->AddJsRef($action->Getparam("CORE_PUBURL")."/FREEDOM/Layout/popupfunc.js");

    // css pour popup
    $cssfile=$action->Getparam("CORE_PUBDIR")."/FREEDOM/Layout/popup.css";
    $csslay = new Layout($cssfile,$action);
    $action->parent->AddCssCode($csslay->gen());
        $first=0;
  }
  $lpopup = new Layout($action->Getparam("CORE_PUBDIR")."/FREEDOM/Layout/popup.js");
  if (isset($tmenuaccess)) {
    reset($tmenuaccess);
    $kv=0; // index for item

    while (list($name, $v2) = each($tmenuaccess)) {
      $nbdiv=0;
      while (list($k, $v) = each($v2)) {
      
	uksort($v, 'vcompare');
      
	$tma[$kv]["vmenuitems"]="[";
	while (list($ki, $vi) = each($v)) {
	  if ($ki[0] == 'v') // its a value
	    $tma[$kv]["vmenuitems"] .= "".$vi.",";
	}
	// replace last comma by ']'
	$tma[$kv]["vmenuitems"][strlen($tma[$kv]["vmenuitems"])-1]="]";
      
	$tma[$kv]["name"]=$name;
	$tma[$kv]["divid"]=$v["divid"];
	$kv++;
	$nbdiv++;
      }
      $tmenus[$name]["nbdiv"]=$nbdiv;
    }

    $lpopup->SetBlockData("MENUACCESS", $tma);
    $lpopup->SetBlockData("MENUS", $tmenus);
    if (isset($tcmenus)) $tcmenus=array_merge($tcmenus, $tmenus);
    else $tcmenus= $tmenus;
    $lpopup->SetBlockData("CMENUS", $tcmenus);
  }
  $action->parent->AddJsCode( $lpopup->gen());

  $tmenus = array(); // re-init (for next time)
  $tmenuaccess = array(); 
  $menuitems = array(); 
  unset($tmenus);
  unset($tmenusaccess);
  unset($tmenuitems);
}
?>