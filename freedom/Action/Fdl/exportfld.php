<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: exportfld.php,v 1.14 2004/04/23 15:24:38 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: exportfld.php,v 1.14 2004/04/23 15:24:38 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Fdl/exportfld.php,v $
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

include_once("FDL/Lib.Dir.php");
include_once("FDL/Class.DocAttr.php");
include_once("VAULT/Class.VaultFile.php");

// --------------------------------------------------------------------
function exportfld(&$action, $aflid="0", $famid="") 
// --------------------------------------------------------------------
{
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $fldid = GetHttpVars("id",$aflid);
  $fld = new Doc($dbaccess, $fldid);
  if ($famid=="") $famid=GetHttpVars("famid");
  $tdoc = getChildDoc($dbaccess, $fldid,"0","ALL",array(),$action->user->id,"TABLE",$famid);

    usort($tdoc,"orderbyfromid");

  $foutname = uniqid("/tmp/exportfld").".csv";
  $fout = fopen($foutname,"w");



  while (list($k,$doc)= each ($tdoc)) {        
    $docids[]=$doc->id;
  }

  if (isset($docids)) {

    // to invert HTML entities
    $trans = get_html_translation_table (HTML_ENTITIES);
    $trans = array_flip ($trans);
    

    
    $doc = createDoc($dbaccess,0);

    // compose the csv file
    $prevfromid = -1;
    reset($tdoc);
    while (list($k,$zdoc)= each ($tdoc)) {
      $doc->ResetMoreValues();
      $doc->Affect($zdoc);
      $doc->GetMoreValues();

      if ($prevfromid != $doc->fromid) {
	$adoc = $doc->getFamDoc();
	$lattr=$adoc->GetExportAttributes();
	fputs($fout,"//FAM;".$adoc->title."(".$doc->fromid.");<specid>;<fldid>;");
	while (list($ka,$attr)= each ($lattr)) {
	  fputs($fout,str_replace(";"," - ",$attr->labelText).";");
	}
	fputs($fout,"\n");
	$prevfromid = $doc->fromid;
      }
      reset($lattr);

      fputs($fout,"DOC;".$doc->fromid.";".$doc->id.";".$fldid.";");
      // write values
      while (list($ka,$attr)= each ($lattr)) {
      
	$value= $doc->getValue($attr->id);
	// invert HTML entities
	$value = preg_replace("/(\&[a-zA-Z0-9\#]+;)/es", "strtr('\\1',\$trans)", $value);
 
	// invert HTML entities which ascii code like &#232;

	$value = preg_replace("/\&#([0-9]+);/es", "chr('\\1')", $value);

	
	fputs($fout,str_replace(array("\n",";","\r"),
				array("\\n"," - ",""),
				$value) .";");
     
      }
      fputs($fout,"\n");
    }
  }
  fclose($fout);
  Http_DownloadFile($foutname, $fld->title.".csv", "text/csv");
  unlink($foutname);

  exit;
}

function orderbyfromid($a, $b) {
  
    if ($a["fromid"] == $b["fromid"]) return 0;
    if ($a["fromid"] > $b["fromid"]) return 1;
  
  return -1;
}


?>
