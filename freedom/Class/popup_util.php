<?php
// ---------------------------------------------------------------
// $Id: popup_util.php,v 1.3 2001/11/26 18:01:02 eric Exp $
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
// $Log: popup_util.php,v $
// Revision 1.3  2001/11/26 18:01:02  eric
// new popup & no lock for no revisable document
//
// Revision 1.2  2001/11/22 17:49:13  eric
// search doc
//
// Revision 1.1  2001/11/22 10:00:59  eric
// premier pas vers une API pour les popup
//

// layout javascript for popup


function popupInit($name, $items) {
  global $lpopup;
  global $menuitems;
  global $action;
  global $tmenuaccess;
  global $tmenus;
  static  $km=0;

  $tmenuaccess=array();
  
  
  $lpopup = new Layout($action->GetLayoutFile("popup.js"));

  // css pour popup
  $cssfile=$action->GetLayoutFile("popup.css");
  $csslay = new Layout($cssfile,$action);
  $action->parent->AddCssCode($csslay->gen());

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

  $tmenus[$km]["menuitems"] = $jsarray;
  $tmenus[$km]["name"] = $name;
  $tmenus[$km]["nbmitem"] = count($menuitems);
  $km++;
}

function popupInitItem($name, $k) {
  global $tmenuaccess;
  global $menuitems;
  if (! isset($tmenuaccess[$name][$k]["divid"])) {
    
    while (list($ki, $v) = each($menuitems)) {
      $tmenuaccess[$name][$k]['v'.$ki] = 2; // invisible

    }
    $tmenuaccess[$name][$k]["divid"] = $k;

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
  global $tmenus;
  global $lpopup;
  global $action;


  $lpopup->Set("nbdiv",$kdiv-1);
  reset($tmenuaccess);
  $kv=0; // index for item

  while (list($name, $v2) = each($tmenuaccess)) {
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
    }
  }

  $lpopup->SetBlockData("MENUACCESS", $tma);
  $lpopup->SetBlockData("MENUS", $tmenus);
  $action->parent->AddJsCode( $lpopup->gen());
  $tmenus = array(); // re-init (for next time)
  $tmenusaccess = array(); 
}
?>