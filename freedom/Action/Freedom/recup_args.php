<?php
// ---------------------------------------------------------------
// $Id: recup_args.php,v 1.1 2003/06/27 07:40:45 mathieu Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/recup_args.php,v $
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
include_once("FDL/Class.DocFam.php");
include_once("FDL/modcard.php");
function recup_args(&$action){
  

 $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
 

  $famid= GetHttpVars("famid");
  // printf($famid);
  $xml = GetHttpVars("xml");
  $temp=base64_decode($xml);
  // printf("hey");
  //printf($temp);
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $attrid=GetHttpVars("attrid");




  $action->lay->Set("attrid",$attrid);
 



  $docid=GetHttpVars("docid");
  //printf($docid);
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  $doc = new Doc($dbaccess, $docid);
  //print_r($doc);

  if ($doc->GetValue("act_type")==1){//action de type action
    $idoc= createDoc($dbaccess,601);
    $action->lay->Set("famid",601);// familly action_implement
  }
  if ($doc->GetValue("act_type")==2){//action de type condition
    $idoc= createDoc($dbaccess,602);
    $action->lay->Set("famid",602);// familly condition_implement
  }

  $args_nom=$doc->GetValue("act_liste_noms");
  //printf($args_nom);
  $args_descrps=$doc->GetValue("act_liste_descrps");

  $idoc->SetValue("ai_args_nom", $args_nom);
  $idoc->SetValue("ai_args_descrip",  $args_descrps);

  $nom=GetHttpVars("nom_act");
  $titre=GetHttpVars("titre");
  //printf("titre : ");
  //printf($titre);

 $idoc->SetValue("ai_action", $nom);
 $idoc->SetValue("ba_title",  $titre);
 $idoc->SetValue("ai_idaction", $docid);

 //printf("ai_args_nom : ");
 //printf($idoc->GetValue("ai_args_nom"));

 // print_r($idoc);


  $xml2=$idoc->toxml(false,"");
  //printf($xml2);
  $xml_send=base64_encode($xml2);
  $action->lay->Set("xml2",$xml_send);

}
?>