<?php
// ---------------------------------------------------------------
// $Id: freedom_ifld.php,v 1.1 2002/07/16 08:35:01 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_ifld.php,v $
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



// -----------------------------------
// search all folder where is docid
// -----------------------------------
function freedom_ifld(&$action) {
  // -----------------------------------

  $docid = GetHttpVars("id");
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new Doc($dbaccess, $docid);

  $lfather = array_reverse(fatherFld($dbaccess,$doc->initid));


  $lprev = 0;
  while (list($k,$v)= each ($lfather)) {
    // recompute level for indentation
    if ($lprev == 0) $lmax = $lfather[$k]["level"];
    $lfather[$k]["level"] = -($v["level"] - $lmax)*15; // by 15px

    $lprev = $v["level"];
  }
  

  $action->lay->Set("TITLE", $doc->title);
  $action->lay->SetBlockData("IFLD", $lfather);
}


function fatherFld($dbaccess,$docid,$level=0,$lfldid=array(),$lcdoc=array()) {
  // compute all path to accessing  document  


  $qfld = new QueryDb($dbaccess,"QueryDir");
  $qfld->AddQuery("qtype='S'");
  $qfld->AddQuery("childid=$docid");
  $tfld=$qfld->Query(0,0,"TABLE");



  $ldoc2 = array();
  if ($qfld->nb > 0) {
    
    
    while (list($k,$v)= each ($tfld)) {

      if (! in_array($v["dirid"], $lfldid)) { 
	// avoid infinite recursion

	$fld = new Dir($dbaccess, $v["dirid"]);
	if ($fld->Control("view") != "") return $lcdoc; // permission view folder

	$ldoc1 = array("level"=>$level,
			"ftitle"=>$fld->title,
			"fid"=>$fld->id);
	//	$ldoc = array_merge($ldoc, fatherFld($dbaccess,$v["dirid"],$level+1,$lfldid)) ;
	//	$ldoc = array_merge(fatherFld($dbaccess,$v["dirid"],$level+1,$lfldid),$ldoc) ;

	$lcdoc1=  $lcdoc;
	$lcdoc1[]=$ldoc1;

	$lfldid1=$lfldid;
	$lfldid1[] = $v["dirid"];
	
	$ldoc2 = array_merge(fatherFld($dbaccess,$v["dirid"],$level+1,
			     $lfldid1,
			     $lcdoc1),$ldoc2)  ;
      }
    }
    
  } else return $lcdoc;
  return $ldoc2;
}
?>
