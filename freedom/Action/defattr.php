<?php
// ---------------------------------------------------------------
// $Id: defattr.php,v 1.5 2001/11/21 13:12:55 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Attic/defattr.php,v $
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
// $Log: defattr.php,v $
// Revision 1.5  2001/11/21 13:12:55  eric
// ajout caractéristique creation profil
//
// Revision 1.4  2001/11/21 08:38:58  eric
// ajout historique + modif sur control object
//
// Revision 1.3  2001/11/15 17:51:50  eric
// structuration des profils
//
// Revision 1.2  2001/11/14 15:31:03  eric
// optimisation & divers...
//
// Revision 1.1  2001/11/09 09:41:13  eric
// gestion documentaire
//

// ---------------------------------------------------------------

include_once("FREEDOM/Class.Doc.php");
include_once("FREEDOM/Class.DocAttr.php");

function defattr(&$action) 
{
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);

  // Set Css
  $cssfile=$action->GetLayoutFile("freedom.css");
  $csslay = new Layout($cssfile,$action);
  $action->parent->AddCssCode($csslay->gen());

  $action->lay->Set("docid",$docid);


  $doc= new Doc($dbaccess,$docid);
  // build values type array
  $odocattr= new DocAttr($dbaccess);

  $action->lay->Set("TITLE",_("new document family"));



  $selectclass=array();
  $tclassdoc = $doc->GetClassesDoc($doc->fromid);
  while (list($k,$cdoc)= each ($tclassdoc)) {
    $selectclass[$k]["idcdoc"]=$cdoc->id;
    $selectclass[$k]["classname"]=$cdoc->title;
    $selectclass[$k]["selected"]="";
  }


  $selectframe= array();
  $selectoption= array();
  while (list($k,$type)= each ($odocattr->deftype)) {
    $selectoption[$k]["typevalue"]=$type;
    $selectoption[$k]["selected"]="";
  }

  $nbattr=0; // if new document 

  // display current values
  $newelem=array();
  if ($docid > 0) {

    // control if user can update 
      $err = $doc->CanUpdateDoc();
      if ($err != "")   $action->ExitError($err);

    $doc->GetFathersDoc();
    $action->lay->Set("TITLE",$doc->title);

    // selected the current class document
    while (list($k,$cdoc)= each ($selectclass)) {
      //      print $doc->doctype." == ".$selectclass[$k]["idcdoc"]."<BR>";
      if ($doc->fromid == $selectclass[$k]["idcdoc"]) {

	$selectclass[$k]["selected"]="selected";
      }
    }
    $query = new QueryDb($dbaccess,"Docattr");
    $sql_cond_doc = sql_cond(array_merge($doc->fathers,$docid), "docid");
    $query->AddQuery($sql_cond_doc);
    $query->order_by="ordered";
    $tattr = $query->Query();


    if ($query->nb > 0)
      {
	$selectframe= array();
	reset($tattr);
	while (list($k,$attr)= each ($tattr)) {
	  if ($attr->type == "frame") {
	    $selectframe[$k]["framevalue"]=$attr->labeltext;
	    $selectframe[$k]["frameid"]=$attr->id;
	    $selectframe[$k]["selected"]="";
	  }
	}

	$nbattr=$query->nb;
	reset($tattr);
	while(list($k,$attr) = each($tattr)) 
	  {

	    $newelem[$k]["attrid"]=$attr->id;
	    $newelem[$k]["attrname"]=$attr->labeltext;
	    $newelem[$k]["order"]=$attr->ordered;
	    $newelem[$k]["neweltid"]=$k;
	    if ($attr->abstract == "Y") {
	      $newelem[$k]["abscheck"]="checked";
	    } else {
	      $newelem[$k]["abscheck"]="";
	    }
	    if ($attr->title == "Y") {
	      $newelem[$k]["titcheck"]="checked";
	    } else {
	      $newelem[$k]["titcheck"]="";
	    }

	    if ($attr->docid == $docid) {
	      $newelem[$k]["disabled"]="";
	    } else {
	      $newelem[$k]["disabled"]="disabled";
	    }


	    while(list($kopt,$opt) = each($selectoption))  {
	      if ($opt["typevalue"] == $attr->type){
		$selectoption[$kopt]["selected"]="selected"; 
	      }else{
		$selectoption[$kopt]["selected"]=""; 
	      }
		  
	    }

	    while(list($kopt,$opt) = each($selectframe))  {
	      if ($opt["frameid"] == $attr->frameid){
		$selectframe[$kopt]["selected"]="selected"; 
	      }else{
		$selectframe[$kopt]["selected"]=""; 
	      }
		  
	    }

	    $newelem[$k]["SELECTOPTION"]="SELECTOPTION_$k";
	    $action->lay->SetBlockData($newelem[$k]["SELECTOPTION"],
	    $selectoption);

	    $newelem[$k]["SELECTFRAME"]="SELECTFRAME_$k";
	    $action->lay->SetBlockData($newelem[$k]["SELECTFRAME"],
	    $selectframe);
	      
  
	  }
      }
    
  }


  // reset default values
  while(list($kopt,$opt) = each($selectframe))  $selectframe[$kopt]["selected"]=""; 
  while(list($kopt,$opt) = each($selectoption))  $selectoption[$kopt]["selected"]=""; 
    

  $action->lay->SetBlockData("SELECTCLASS", $selectclass);

  // add 3 new attributes to be defined
  for ($k=$nbattr;$k<3+$nbattr;$k++) {
    $newelem[$k]["neweltid"]=$k;
    $newelem[$k]["attrname"]="";
    $newelem[$k]["order"]="";
    $newelem[$k]["attrid"]="";
    $newelem[$k]["SELECTOPTION"]="SELECTOPTION_$k";
    $action->lay->SetBlockData($newelem[$k]["SELECTOPTION"],
					 $selectoption);

    $newelem[$k]["SELECTFRAME"]="SELECTFRAME_$k";
    $action->lay->SetBlockData($newelem[$k]["SELECTFRAME"],
                              $selectframe);
    $newelem[$k]["disabled"]="";
  }

  $action->lay->SetBlockData("NEWELEM",$newelem);

}

?>
