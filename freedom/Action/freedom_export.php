<?php
// ---------------------------------------------------------------
// $Id: freedom_export.php,v 1.2 2001/12/19 17:57:32 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Attic/freedom_export.php,v $
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
// $Log: freedom_export.php,v $
// Revision 1.2  2001/12/19 17:57:32  eric
// on continue
//
// Revision 1.1  2001/11/09 09:41:14  eric
// gestion documentaire
//
// Revision 1.2  2001/10/03 15:56:03  eric
// ajout type date pour roaming
//
// Revision 1.1  2001/07/27 08:05:11  eric
// correction erreur de frappe format mime
//
// Revision 1.2  2001/06/22 09:46:12  eric
// support attribut multimédia
//
// Revision 1.1  2001/06/19 16:13:20  eric
// importation de fichier
//
//
// ---------------------------------------------------------------
include_once("FREEDOM/Class.Doc.php");
include_once("FREEDOM/Class.DocAttr.php");
include_once("FREEDOM/Class.DocValue.php");
include_once("FREEDOM/freedom_util.php");
include_once("Class.QueryDb.php");
include_once("Class.QueryGen.php");



  


  // -----------------------------------
function freedom_export(&$action) {
  // -----------------------------------
  // export all selected card in a tempory file
  // this file is sent by dowload  
  // -----------------------------------

  // Get all the params   
  $fulltext= GetHttpVars("fulltext");
  $selectedCategory = GetHttpVars("catg");
  $onlyowner = GetHttpVars("onlyowner");
  $conv_type= GetHttpVars("conv_type");


  $dbaccess = $action->GetParam("FREEDOM_DB");



  // ------------------------------------------------------
  // Perform SQL search freedom
  $query = new QueryDb($dbaccess,"Freedom");
  // ------------------------------------------------------
  // Add constraint for text search

  if ($fulltext != "")
    {
      //$action->ActUnregister("fulltext");
      $query->fulltext = "";
      $value = new DocValue($dbaccess);
      $textTable = $value->GetIdFreedom($fulltext);
      
      $sql_cond_text = GetSqlCond($textTable, "id");
      $query->AddQuery($sql_cond_text);
      
    }
  if (! ($action->HasPermission("ADMIN")) )
	  
    {
      $query->AddQuery("(visibility != 'N') OR (owner = ".$action->user->id.")");
    }
  

  if ($onlyowner == "true")
    {
      $query->AddQuery("owner = '".$action->user->id."'"); 
    }
  // ------------------------------------------------------
  // Add constraint for category
  if ($selectedCategory != "")
    {
      $query->AddQuery("category = '$selectedCategory'");
    }
  $query->order_by = "title";
  $tablecard=$query->Query();
  
  reset ($tablecard);

  

 
  // instanciate new import/export class
  include_once("FREEDOM/Class.FreedomImport".$conv_type.".php");


  $class = "FreedomImport".$conv_type;
  $vcard_export = new $class();
  $export_file = uniqid("/tmp/export");
  $export_file .= ".".$vcard_export->ext;

  $vcard_export-> Open($export_file,"w");


  while(list($k,$v) = each($tablecard)) 
    {
      $docid=$tablecard[$k]->id;


      // get attributes for a card
      freedom_get_attr_card($dbaccess, $docid,
			    $title, $tattr);

      
      $vcard_export-> write($title,$tattr);
	
    }
  $vcard_export-> close();

  $fcontents = join ('', file ($export_file));

  unlink($export_file);

  $nav=$action->Read("navigator",'EXPLORER');
  if ($nav=='NETSCAPE') {        
    header("CONTENT-TYPE: ".$vcard_export->mime_type.";filename=\"$title.$vcard_export->ext\"");
    echo $fcontents;
  } else {
    Http_Download($fcontents, $vcard_export->ext, "what");
  }
  exit;
}


  // -----------------------------------
function freedom_one_export(&$action) {
  // -----------------------------------
  // export one card in a tempory file
  // this file is sent by dowload  
  // -----------------------------------

  // Get all the params   
  $conv_type= GetHttpVars("conv_type");


  $docid=GetHttpVars("id");
  $dbaccess = $action->GetParam("FREEDOM_DB");



  

 
  // instanciate new import/export class
  include_once("FREEDOM/Class.FreedomImport".$conv_type.".php");


  $class = "FreedomImport".$conv_type;
  $vcard_export = new $class();
  $export_file = uniqid("/tmp/export");
  $export_file .= ".".$vcard_export->ext;

  $vcard_export-> Open($export_file,"w");

  


  // get attributes for a card
  freedom_get_attr_card($dbaccess, $docid,
			$title, $tattr);

      
  $vcard_export-> write($title,$tattr);
	
  $vcard_export-> close();

  $fcontents = join ('', file ($export_file));

  unlink($export_file);

  $nav=$action->Read("navigator",'EXPLORER');
  if ($nav=='NETSCAPE') {        
    header("CONTENT-TYPE: ".$vcard_export->mime_type.";filename=\"$title.$vcard_export->ext\"");
    echo $fcontents;
  } else {
    Http_Download($fcontents,$vcard_export->ext , $title);
  }
  exit;
}

?>
