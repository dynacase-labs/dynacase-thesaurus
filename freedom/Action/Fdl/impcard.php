<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: impcard.php,v 1.3 2003/09/22 13:05:17 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: impcard.php,v 1.3 2003/09/22 13:05:17 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Fdl/impcard.php,v $
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


// -----------------------------------
// -----------------------------------
function impcard(&$action) {
  // -----------------------------------

  // GetAllParameters

  $mime = GetHttpVars("mime"); // send to be view by word editor
  $ext = GetHttpVars("ext","html"); // extension
  $docid = GetHttpVars("id");
  $zonebodycard = GetHttpVars("zone"); // define view action
  $szone=false;

  $dbaccess = $action->GetParam("FREEDOM_DB");


  $doc = new Doc($dbaccess, $docid);
  $action->lay->set("TITLE",$doc->title);  
  if ($zonebodycard == "") $zonebodycard=$doc->defaultview;
  if ($zonebodycard == "") $zonebodycard="FDL:VIEWCARD";

  if (ereg("[A-Z]+:[^:]+:S", $zonebodycard, $reg))  $szone=true;// the zonebodycard is a standalone zone ?

  if ($szone) {
    // change layout
    include_once("FDL/viewscard.php");
    $action->lay = new Layout(getLayoutFile("FDL","viewscard.xml"),$action);
    viewscard(&$action); 
    
  }

  if ($mime != "") {
    $export_file = uniqid("/tmp/export").".$ext";
  
    $of = fopen($export_file,"w+");
    fwrite($of, $action->lay->gen());
    fclose($of);
  
    http_DownloadFile($export_file, chop($doc->title).".$ext", "$mime");
  
    unlink($export_file);
    exit;
  }
}


?>
