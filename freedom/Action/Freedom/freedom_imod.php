<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_imod.php,v 1.3 2005/03/04 17:15:51 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: freedom_imod.php,v 1.3 2005/03/04 17:15:51 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_imod.php,v $
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

//include_once("FDL/freedom_util.php");
include_once("FDL/modcard.php");
include_once("FDL/Class.Dir.php");
include_once("FDL/Class.DocFam.php");
include_once("FDL/Class.Doc.php");



// -----------------------------------
function freedom_imod(&$action) {
  // -----------------------------------

  $famid=GetHttpVars("famid");
  $xml = GetHttpVars("xml_initial");
  $attrid = GetHttpVars("attrid");
  $attrid=substr($attrid,1,strlen($attrid)-2);//on enlève les ' pour la suite
  //printf($attrid);
  $action->lay->Set("attrid",$attrid);
  $action->lay->Set("famid",$famid);

  $type_attr=GetHttpVars("type_attr");
  $action->lay->Set("type_attr",$type_attr);

  $mod=GetHttpVars("mod");
  $action->lay->Set("mod",$mod);

  //$xml=stripslashes($xml);
  // $xml=ltrim($xml);
  $temp=base64_decode(trim($xml));
  $entete="<?xml version=\"1.0\" encoding=\"ISO-8859-1\" standalone=\"yes\" ?>";
  $xml=$entete;
  $xml.=$temp;
  //printf($xml);
$dbaccess = $action->GetParam("FREEDOM_DB");


$idoc= createDoc($dbaccess,$famid);
$idoc=fromxml($xml,$idoc);
$idoc->doctype='T';
$idoc->Add();


SetHttpVar("id",$idoc->id);

$err = modcard($action, $ndocid); // ndocid change if new doc
  if ($err != "")  $action-> ExitError($err);


   $idoc= new Doc($dbaccess,$idoc->id);

   
    $idoc->RefreshTitle();


    $numero=$attrid;
    // printf($numero);
    $taille=strlen($numero);
    $num="";
    $ok=true;
    while($ok) {
      $car=$numero[$taille-1];
      if ($car=="0" or $car=="1"or $car=="2"or $car=="3"or $car=="4"or $car=="5"or $car=="6" or $car=="7" or $car=="8" or $car=="9"){
	$num="$car$num";
	$taille--;
      }else{$ok=false;}
    }
    $att=substr($attrid,0,strlen($attrid)-strlen($num));
    //printf($att);
    $action->lay->Set("att",$att);

    //printf($num);



    $action->lay->Set("title",$num." : ".htmlentities(addslashes($idoc->title)));
    $action->lay->Set("title2",htmlentities(addslashes($idoc->title)));

    $xml2=$idoc->toxml(false,$attrid);
    //$title=recup_argument_from_xml($xml2,"title");//ds freedom_util
    //$action->lay->Set("title",$title);

    $xml_send=base64_encode($xml2);
    $action->lay->Set("xml2",$xml_send);
    //printf($mod);
    $action->lay->gen();
    /*
    if ($mod=="'special'"){
 redirect($action,"FREEDOM",
	       "FREEDOM_LOGO",
	       $action->GetParam("CORE_STANDURL"));
      
    }*/
   
  
}



?>
