<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_editimport.php,v 1.5 2005/02/08 11:34:37 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: freedom_editimport.php,v 1.5 2005/02/08 11:34:37 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_editimport.php,v $
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

// ---------------------------------------------------------------
include_once("FDL/import_file.php");
include_once("FDL/Lib.Dir.php");





// -----------------------------------
function freedom_editimport(&$action) {
  // -----------------------------------

  // Get all the params   
  $classid = GetHttpVars("classid",0); // doc familly
  $dirid = GetHttpVars("dirid",10); // directory to place imported doc (default unclassed folder)

  $dbaccess = $action->GetParam("FREEDOM_DB");

 

  // build list of class document
  $query = new QueryDb($dbaccess,"Doc");
  $query->AddQuery("doctype='C'");

  $selectclass=array();

  $doc = new Doc($dbaccess, $classid);
  $tclassdoc = GetClassesDoc($dbaccess, $action->user->id,$classid,"TABLE");

  while (list($k,$cdoc)= each ($tclassdoc)) {
    $selectclass[$k]["idcdoc"]=$cdoc["initid"];
    $selectclass[$k]["classname"]=$cdoc["title"];
    if ($cdoc["initid"] == $classid) $selectclass[$k]["selected"]="selected";
    else $selectclass[$k]["selected"]="";
  }


  $action->lay->SetBlockData("SELECTCLASS", $selectclass);


  $lattr = $doc->GetImportAttributes();
  $format = "DOC;".(($doc->name!="")?$doc->name:$doc->id).";<special id>;<special dirid> ";

  $ttemp=explode(";",$format);
  while (list($k, $v) = each ($ttemp)) {
    $tformat[$k]["labeltext"]=htmlentities($v);    
  }

  while (list($k, $attr) = each ($lattr)) {
    $format .= "; ".$attr->labelText;
    $tformat[$k]["labeltext"]=$attr->labelText;
  }
  
  $action->lay->set("mailaddr",getMailAddr($action->user->id));

  $action->lay->SetBlockData("TFORMAT", $tformat);
  
  $action->lay->Set("cols",count($tformat));

  $action->lay->Set("dirid",$dirid);

  $action->lay->Set("format",$format);
}



?>
