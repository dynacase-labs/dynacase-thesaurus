<?php
// ---------------------------------------------------------------
// $Id: editransition.php,v 1.1 2003/06/27 07:40:45 mathieu Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/editransition.php,v $
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
function editransition(&$action){ 
  $etats=GetHttpVars("state");
  $tab=explode(",",$etats);
  $tt=GetHttpVars("tt");
  $tabtt=explode(",",$tt);
  while (list($k, $v) = each($tabtt)){
    $tab_tt[$k]=explode("*",$v);
  }

  // print_r($tab_tt);
  while (list($i,$tt)=each($tab_tt)){
    $tab_relation_tt[$tt[1]]=$tt[0];
  }


  $docid=GetHttpVars("docid");
  $action->lay->set("id",$docid);
 
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc= new Doc($dbaccess,$docid);
 




  $idetats_ini=explode("\n",$doc->GetValue("wor_trans_idetat_ini"));
  $idetats_fin=explode("\n",$doc->GetValue("wor_trans_idetat_fin"));
  $descriptions=explode("\n",$doc->GetValue("wor_trans_descrip"));
  $tts=explode("\n",$doc->GetValue("wor_trans_tt"));


  while (list($k, $v) = each($idetats_ini)) {
    $descrip[$v][$idetats_fin[$k]]=$descriptions[$k];
    $type_trans[$v][$idetats_fin[$k]]=$tts[$k];
  }

  $ligne1=array();
  reset($tab);
  $tab2=$tab;
  $tab[-1]="transition_initiale:-1";

  //to be in  first
  $lignes[-1]["etat"]="transition_initiale";
  $lignes[-1]["LIGNEEE"]="LIGNE_transition_initiale:-1";

  while (list($i,$etat)=each($tab)){
    $nom=explode(":",$etat);
    if($i!=-1){
      $ligne1[$i]["nom_etat"]=$nom[0];
      // $ligne2[$i]["new_trans"]="";
     
     
      $lignes[$i]["etat"]=$nom[0];
      $lignes[$i]["LIGNEEE"]="LIGNE_$etat";
    }
  }


  $action->lay->setBlockData("LIGNE1",$ligne1);
  $action->lay->setBlockData("LIGNE2",$ligne2);
  $action->lay->setBlockData("LIGNES",$lignes);

  reset($tab);

 while(list($i,$etat)=each($tab)){
   reset($tab2);
  while(list($x,$etat2)=each($tab2)){

   
   
      $inputlay=new Layout("FREEDOM/Layout/input_transition.xml",$action);

      $etat_ini=explode(":",$etat);
      $etat_fin=explode(":",$etat2);
      $result=100/sizeof($tab);
      // printf($result);
      $inputlay->set("width","$result");
      $inputlay->set("value_etat_ini",$etat_ini[0]);
      $inputlay->set("value_idetat_ini",$etat_ini[1]);
      $inputlay->set("value_etat_fin",$etat_fin[0]);
      $inputlay->set("value_idetat_fin",$etat_fin[1]);
      $inputlay->set("value_descrip",$descrip[$etat_ini[1]][$etat_fin[1]]);
      $value_tt=$type_trans[$etat_ini[1]][$etat_fin[1]];
      $inputlay->set("value_tt",$value_tt);
      $inputlay->set("text_tt",$tab_relation_tt[$value_tt]);
      $temp="trans";
      $temp.=$i;
      $temp.="_$x";
      $inputlay->set("id_tt",$temp);
      // $inte[$i][$x]["idtt"]= $temp;
      $inte[$i][$x]["input"]= $inputlay->gen();
     
      

   
  }

 }
 reset($tab);
 //$inte[0]["idtt"]="dd";
 while (list($i,$etat)=each($tab)){

   //  print_r($inte[$i]);printf("<BR>");
   $action->lay->setBlockData($lignes[$i]["LIGNEEE"],$inte[$i]);
 }

 reset($tab_tt);
 while (list($i,$tt)=each($tab_tt)){
   $option[$i]["titre"]=$tt[0];//nom $tt[1] correspond à l'id de l'attribut
   $option[$i]["id_tt"]=$tt[1];
 }
 $action->lay->setBlockData("OPTIONS",$option);
 $action->lay->gen();
}


?>