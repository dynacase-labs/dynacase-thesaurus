<?php
// ---------------------------------------------------------------
// $Id: Lib.Attr.php,v 1.1 2002/11/04 09:13:17 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Lib.Attr.php,v $
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
include_once('FDL/Class.Doc.php');

function AttrToPhp($dbaccess, $tdoc) {
  global $action;
 
  $phpAdoc = new Layout("FDL/Layout/Class.Doc.xml",$action);


  


  $phpAdoc->Set("docid", $tdoc["id"]);
  $phpAdoc->Set("include","");
  if ($tdoc["fromid"] == 0) {
    $phpAdoc->Set("DocParent", $tdoc["classname"]);
    $phpAdoc->Set("AParent", "ADoc");
    $phpAdoc->Set("fromid", "");

  }  else  {
    $phpAdoc->Set("fromid", $tdoc["fromid"]);
    if ($tdoc["classname"] != "Doc".$tdoc["fromid"] ) {
      $phpAdoc->Set("DocParent", $tdoc["classname"]);
      $phpAdoc->Set("include","include_once(\"FDL/Class.Doc".$tdoc["fromid"].".php\");");
    } else $phpAdoc->Set("DocParent", "Doc".$tdoc["fromid"]);
    $phpAdoc->Set("AParent", "ADoc".$tdoc["fromid"]);
  }

   $phpAdoc->Set("title", $tdoc["title"]);

  $query = new QueryDb($dbaccess,"DocAttr");
  $query->AddQuery("docid=".$tdoc["id"]);

  $table1 = $query->Query();

  $phpAdoc->Set("sattr","");
    
  if ($query->nb > 0)	{

    $tmenu=array();
    $tfield=array();
    $tnormal=array();
    $tattr=array();
    $attrids=array();
    
    while(list($k,$v) = each($table1))   {
      switch ($v->visibility) {
      case "M": // menu
	$tmenu[] = array("attrid"=>strtolower($v->id),
			 "label"=>str_replace("\"","\\\"",$v->labeltext),
			 "order"=>intval($v->ordered),
			 "link"=>str_replace("\"","\\\"",$v->link),
			 "precond"=>$v->phpfunc);
	break;
      case "F": // frame
	$tfield[] = array("attrid"=>strtolower($v->id),
			  "label"=>str_replace("\"","\\\"",$v->labeltext));
	break;
	
      default: // normal
	$tnormal[] = array("attrid"=>strtolower($v->id),
			   "label"=>str_replace("\"","\\\"",$v->labeltext),
			   "type"=>str_replace("\"","\\\"",$v->type),
			   "order"=>intval($v->ordered),
			   "link"=>str_replace("\"","\\\"",$v->link),
			   "visibility"=>$v->visibility,
			   "needed"=>($v->needed=="Y")?"true":"false",
			   "title"=>($v->title=="Y")?"true":"false",
			   "abstract"=>($v->abstract=="Y")?"true":"false",
			   "frame"=>($v->frameid=="")?"FIELD_HIDDENS":strtolower($v->frameid),
			   "elink"=>$v->elink,
			   "phpfile"=>$v->phpfile,
			   "phpfunc"=>$v->phpfunc);
	$tattr[] = array("attrid"=>strtolower($v->id));	 
	$attrids[] = strtolower($v->id)." text";    
	break;
      }
    }	 


    $phpAdoc->Set("sattr", implode(",",$attrids));
    $phpAdoc->SetBlockData("MATTR", $tmenu);
    $phpAdoc->SetBlockData("FATTR", $tfield);
    $phpAdoc->SetBlockData("NATTR", $tnormal);
    $phpAdoc->SetBlockData("ATTRFIELD2", $tattr);
    reset( $tattr);
    $phpAdoc->SetBlockData("ATTRFIELD", $tattr);
      
  }      
    

  //----------------------------------
  // Add specials methods
  if ($tdoc["methods"] != "") {

    $filename=GetParam("CORE_PUBDIR")."/FDL/".$tdoc["methods"];
    $fd = fopen ($filename, "rb");
    $contents = fread ($fd, filesize ($filename));
    fclose ($fd);
    $phpAdoc->Set("METHODS",$contents  );
  } else $phpAdoc->Set("METHODS","");
  

  return $phpAdoc->gen();
    
}




?>
