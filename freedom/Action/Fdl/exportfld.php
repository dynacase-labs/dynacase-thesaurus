<?php
// ---------------------------------------------------------------
// $Id: exportfld.php,v 1.6 2002/09/02 16:38:49 eric Exp $
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

include_once("FDL/Class.Dir.php");
include_once("FDL/Class.DocAttr.php");
include_once("VAULT/Class.VaultFile.php");

// --------------------------------------------------------------------
function exportfld(&$action, $aflid="0") 
// --------------------------------------------------------------------
{
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $fldid = GetHttpVars("id",$aflid);
  $fld = new Dir($dbaccess, $fldid);

  $ldoc = getChildDoc($dbaccess, $fldid,"0","ALL",array("doctype='F'"),$action->user->id);


  $foutname = uniqid("/tmp/exportfld").".csv";
  $fout = fopen($foutname,"w");



  while (list($k,$doc)= each ($ldoc)) {        
      $docids[]=$doc->id;
  }

  if (isset($docids)) {

  // get all values
  $query = new QueryDb($dbaccess,"DocValue");
  $query -> AddQuery(GetSqlCond($docids, "docid"));
  $qvalues = $query -> Query(0,0,"TABLE");

  // recompose the array to access by docid and attrid
  while (list($k,$v)= each ($qvalues)) {
      $value[$v["docid"]][$v["attrid"]]=$v["value"];
    }



  // compose the csv file
  $prevfromid = 0;
  reset($ldoc);
  while (list($k,$doc)= each ($ldoc)) {
    
    if ($prevfromid != $doc->fromid) {
      $lattr=$doc->GetNormalAttributes();
      fputs($fout,"//DOC;".$doc->fromid.";<specid>;<fldid>;");
      while (list($ka,$attr)= each ($lattr)) {
	fputs($fout,str_replace(";"," - ",$attr->labeltext).";");
      }
      fputs($fout,"\n");
      $prevfromid = $doc->fromid;
    }
    reset($lattr);

      fputs($fout,"DOC;".$doc->fromid.";".$doc->id.";".$fldid.";");
    // write values
    while (list($ka,$attr)= each ($lattr)) {
      
      if (isset($value[$doc->id][$attr->id])) fputs($fout,str_replace("\n","\\n",str_replace(";"," - ",$value[$doc->id][$attr->id])) .";");
      else fputs($fout,";");
    }
    fputs($fout,"\n");
  }
  }
  fclose($fout);
  Http_DownloadFile($foutnam, $feld->title.".csv", "text/csv");
  unlink($foutname);

  
}

?>
