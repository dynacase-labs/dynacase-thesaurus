<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: viewxml.php,v 1.3 2003/08/18 15:47:03 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: viewxml.php,v 1.3 2003/08/18 15:47:03 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Fdl/viewxml.php,v $
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



include_once("FDL/Class.Doc.php");





// -----------------------------------
function viewxml(&$action) {
  // -----------------------------------

 

  // Get all the params      
  $docid=GetHttpVars("id"); // dccument to export

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc = new Doc($dbaccess, $docid);
  $xml=$doc->toxml(true,$docid);
  //$doc->fromxml($xml);
  //$xml=$doc->viewdtd();

  
  
  
 
  $export_file = uniqid("/tmp/xml");
  $export_file.=".xml";
 $fp= fopen($export_file,"w");

 fwrite($fp,$xml);
 fclose($fp);
 

  
 //http_DownloadFile($export_file,chop($doc->title).".xml","text/dtd");
 http_DownloadFile($export_file,str_replace(" ","_",chop($doc->title)).".xml","text/xml");
  
  unlink($export_file);
  exit;
}


?>