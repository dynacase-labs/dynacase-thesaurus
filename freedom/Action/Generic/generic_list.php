<?php
// ---------------------------------------------------------------
// $Id: generic_list.php,v 1.1 2002/04/17 09:03:12 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_list.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2002
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


include_once("FDL/viewfolder.php");



// -----------------------------------
// -----------------------------------
function generic_list(&$action) {
// -----------------------------------
  // Set the globals elements

  // Get all the params      
  $dirid=GetHttpVars("dirid"); // directory to see
  $catgid=GetHttpVars("catg", $dirid); // category
  $startpage=GetHttpVars("page","0"); // page to see
  $tab=GetHttpVars("tab","0"); // tab to see 1 for ABC, 2 for DEF, ...

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $dir = new Dir($dbaccess,$dirid);
  $action->lay->Set("fldtitle",$dir->title);
  $action->lay->Set("dirid",$dirid);
  $action->lay->Set("tab",$tab);
  $action->lay->Set("catg",$catgid);

  $slice = $action->GetParam("CARD_SLICE_LIST",5);
  //  $action->lay->Set("next",$start+$slice);
  //$action->lay->Set("prev",$start-$slice);

  $action->lay->Set("nexticon",""); 
  $action->lay->Set("previcon",""); 

  // add filters like view control see DocUser::Control
    $sqlfilters=array("fromid = ".$action->GetParam("DEFAULT_FAMILY"));


  if (viewfolder($action, true, false,$slice, $sqlfilters) == $slice) {
    // can see next
    $action->lay->Set("nexticon",$action->GetIcon("next.png",N_("next"))); 
  }
  if ($startpage > 0) {
    // can see prev
    $action->lay->Set("previcon",$action->GetIcon("prev.png",N_("prev"))); 
  }
  

  // hightlight the selected part (ABC, DEF, ...)
  $onglet=array(array("onglabel"=> "A B C",
		      "ongdir" => "1"),
		array("onglabel"=> "D E F",
		      "ongdir" => "2"),
		array("onglabel"=> "G H I",
		      "ongdir" => "3"),
		array("onglabel"=> "J K L",
		      "ongdir" => "4"),
		array("onglabel"=> "M N O",
		      "ongdir" => "5"),
		array("onglabel"=> "P Q R S",
		      "ongdir" => "6"),
		array("onglabel"=> "T U V",
		      "ongdir" => "7"),
		array("onglabel"=> "W X Y Z",
		      "ongdir" => "8"),
		array("onglabel"=> "A - Z",
		      "ongdir" => "0"));

  
    while (list($k, $v) = each ($onglet)) {
      if ($v["ongdir"] == $tab) $onglet[$k]["ongclass"]="onglets";
      else $onglet[$k]["ongclass"]="onglet";
      $onglet[$k]["onglabel"] = str_replace(" ","<BR>",$v["onglabel"]);
    }

    $action->lay->SetBlockData("ONGLET", $onglet);
}
?>
