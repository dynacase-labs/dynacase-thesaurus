<?php
// ---------------------------------------------------------------
// $Id: popup_util.php,v 1.2 2001/11/22 17:49:13 eric Exp $
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
// Revision 1.2  2001/11/22 17:49:13  eric
// search doc
//
// Revision 1.1  2001/11/22 10:00:59  eric
// premier pas vers une API pour les popup
//

// layout javascript for popup


function popupInit($items) {
  global $lpopup;
  global $menuitems;
  global $action;
  global $tmenuaccess;

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
  $lpopup->Set("menuitems",$jsarray);

  $lpopup->Set("nbmitem", count($menuitems));
}

function popupInitItem($k) {
  global $tmenuaccess;
  global $menuitems;
  if (! isset($tmenuaccess[$k]["divid"])) {
    
    while (list($ki, $v) = each($menuitems)) {
      $tmenuaccess[$k]['v'.$ki] = 2; // invisible

    }
    $tmenuaccess[$k]["divid"] = $k;
  }
}

function popupActive($k, $nameid) {
  global $tmenuaccess;
  global $$nameid;
  popupInitItem($k);
  $tmenuaccess[$k][$$nameid]=1;
}


function popupInactive($k, $nameid) {
  global $tmenuaccess;
  global $$nameid;

  popupInitItem($k);
  $tmenuaccess[$k][$$nameid]=0;
}
function popupInvisible($k, $nameid) {
  global $tmenuaccess;
  global $$nameid;

  popupInitItem($k);
  $tmenuaccess[$k][$$nameid]=2;
}

function popupGen($kdiv) {  
  global $tmenuaccess;
  global $lpopup;
  global $action;

  function vcompare($a, $b) {
    $na = intval(substr($a,1));
    $nb = intval(substr($b,1));

    if ($na == $nb) return 0;
    return ($na < $nb) ? -1 : 1;
  }

  $lpopup->Set("nbdiv",$kdiv-1);
  reset($tmenuaccess);
  while (list($k, $v) = each($tmenuaccess)) {
    uksort($v, 'vcompare');

    $tmenuaccess[$k]["vmenuitems"]="[";
    while (list($ki, $vi) = each($v)) {
      if ($ki[0] == 'v') // its a value
	$tmenuaccess[$k]["vmenuitems"] .= "".$vi.",";
    }
  // replace last comma by ']'
    $tmenuaccess[$k]["vmenuitems"][strlen($tmenuaccess[$k]["vmenuitems"])-1]="]";
  }

  $lpopup->SetBlockData("MENUACCESS", $tmenuaccess);
  $action->parent->AddJsCode( $lpopup->gen());
}
?>