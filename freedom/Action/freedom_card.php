<?php
// ---------------------------------------------------------------
// $Id: freedom_card.php,v 1.8 2001/11/21 17:03:54 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Attic/freedom_card.php,v $
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
// $Log: freedom_card.php,v $
// Revision 1.8  2001/11/21 17:03:54  eric
// modif pour création nouvelle famille
//
// Revision 1.7  2001/11/21 13:12:55  eric
// ajout caractéristique creation profil
//
// Revision 1.6  2001/11/21 08:38:58  eric
// ajout historique + modif sur control object
//
// Revision 1.5  2001/11/19 18:04:22  eric
// aspect change
//
// Revision 1.4  2001/11/16 18:04:39  eric
// modif de fin de semaine
//
// Revision 1.3  2001/11/15 17:51:50  eric
// structuration des profils
//
// Revision 1.2  2001/11/14 15:31:03  eric
// optimisation & divers...
//
// Revision 1.1  2001/11/09 09:41:14  eric
// gestion documentaire
//
// Revision 1.7  2001/10/17 14:35:55  eric
// mise en place de i18n via gettext
//
// Revision 1.6  2001/10/12 10:24:35  eric
// pas d'affichage d'ACL pour objet non controllé
//
// Revision 1.5  2001/09/10 16:51:45  eric
// ajout accessibilté objet
//
// Revision 1.4  2001/07/05 11:41:31  eric
// ajout export format vcard
//
// Revision 1.3  2001/06/22 09:46:12  eric
// support attribut multimédia
//
// Revision 1.2  2001/06/15 10:32:48  eric
// typage des attributs avec ajout image
//
// Revision 1.1  2001/06/13 14:39:53  eric
// Freedom address book
//
// ---------------------------------------------------------------
include_once("FREEDOM/Class.Doc.php");
include_once("FREEDOM/Class.DocAttr.php");
include_once("FREEDOM/Class.DocValue.php");

include_once("Class.TableLayout.php");
include_once("Class.QueryDb.php");
include_once("Class.QueryGen.php");
include_once("FREEDOM/freedom_util.php");

// -----------------------------------
// -----------------------------------
function freedom_card(&$action) {
  // -----------------------------------

  // Set the globals elements

  $baseurl=$action->GetParam("CORE_BASEURL");
  $standurl=$action->GetParam("CORE_STANDURL");
  $dbaccess = $action->GetParam("FREEDOM_DB");

   
  // layout javascript for popup
  $lpopup = new Layout($action->GetLayoutFile("popup.js"));

  // Set Css
  $cssfile=$action->GetLayoutFile("freedom.css");
  $csslay = new Layout($cssfile,$action);
  $action->parent->AddCssCode($csslay->gen());
  // css pour poopup
  $cssfile=$action->GetLayoutFile("popup.css");
  $csslay = new Layout($cssfile,$action);
  $action->parent->AddCssCode($csslay->gen());

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  



  $docid = GetHttpVars("id");


  $tfile=array(); // array of file attributes 
  $kf=0; // number of files

  $doc = new Doc($dbaccess, $docid);
  //------------------------------
  // display document attributes
  $action->lay->Set("reference", $doc->initid);

  $action->lay->Set("revision", $doc->revision);
  
  if ($action->GetParam("CORE_LANG") == "fr") { // date format depend of locale
    setlocale (LC_TIME, "fr_FR");
    $action->lay->Set("revdate", strftime ("%a %d %b %H:%M",$doc->revdate));
  } else {
    $action->lay->Set("revdate", strftime ("%x %T",$doc->revdate));

  }

  $action->lay->Set("comment", $doc->comment);
  $destdir="./".GetHttpVars("app")."/Download/"; // for downloading file

  if ($doc->locked > 0) {
    $user = new User("", $doc->locked);
    $action->lay->Set("locked", $user->firstname." ".$user->lastname);
  } else {
    if ($doc->locked < 0) {
      $action->lay->Set("locked", _("fixed"));
    } else {
      $action->lay->Set("locked", _("nobody"));
    }
  }
  if ($doc->fromid > 0) {
    $cdoc = new Doc($dbaccess, $doc->fromid);
    $action->lay->Set("classtitle", $cdoc->title);
  } else {
    $action->lay->Set("classtitle", _("no family"));
  }
  if ($doc->profid > 0) {
    $pdoc = new Doc($dbaccess, $doc->profid);
    $action->lay->Set("profile", $pdoc->title);
  } else {
    if ($doc->profid == 0)
      $action->lay->Set("profile", _("no access control"));
    else
      $action->lay->Set("profile", _("specific control"));
      
  }
  $action->lay->Set("iconalt","icon");
  
  $action->lay->Set("iconsrc", $doc->geticon());

  if ($doc->fromid > 0)    $action->lay->Set("cid", $doc->fromid);
  else   $action->lay->Set("cid", $doc->id);
  



  //------------------------------
  // display icon action
  
  if ($action->HasPermission("FREEDOM"))
    {
      if ($doc->CanUpdateDoc() == "")	{
	$action->lay->Set("imgedit", $action->GetIcon("edit.gif",N_("edit")));
      } else {
	$action->lay->Set("imgedit", "");
      }

      //print ("errdel:".$doc->PreDelete());
      if ($doc->PreDelete() == "")	{
	  $action->lay->Set("imgdel", $action->GetIcon("delete.gif",N_("delete")));
	  $action->lay->Set("deltitle", AddSlashes($doc->title));
      } else {
	  $action->lay->Set("imgdel", "");

      }
      if (($doc->IsControlled() )
	  &&($doc->Control("viewacl") == ""))	{
	  $action->lay->Set("imgaccess", $action->GetIcon("access.gif",N_("goaccess"),20));
      } else {
	  $action->lay->Set("imgaccess", "");

      }

      
      
    }
 

 $action->lay->Set("TITLE", $doc->title);
 $action->lay->Set("id", $docid);

  // ------------------------------------------------------
  // Perform SQL search freedom
  $query = new QueryDb($dbaccess,"DocAttr");
  

  $sql_cond_doc = sql_cond(array_merge($doc->GetFathersDoc(),$doc->initid), "docid");
  $query->AddQuery($sql_cond_doc);
 $query->AddQuery("type != 'frame'");
 $query->order_by="ordered";

 $bdvalue = new DocValue($dbaccess);

 $bdattr = new DocAttr($dbaccess);


  // ------------------------------------------------------
  // definition of popup menu
  $menuitems= array('chicon','editdoc','lockdoc','revise','unlockdoc','editattr','histo','editprof','editcprof','cancel');
  while (list($ki, $imenu) = each($menuitems)) {
    $lpopup->Set("menuitem$ki",$imenu);
    ${$imenu} = "vmenuitem$ki";
  }
  $lpopup->Set("nbmitem", count($menuitems));

  $frames= array();
  
 $listattr = $query->Query();

  




 $k=0; // number of frametext
 $v=0;// number of value in one frametext
 $nbimg=0;// number of image in one frametext
 $currentFrameId="";

  $changeframe=false; // is true when need change frame
 $tableframe=array();
 $tableimage=array();
 for ($i=0; $i < $query->nb + 1; $i++)
   {

     

     //------------------------------
     // Compute value elements
     if ($i < $query->nb)
       {
	 $bdvalue->value=""; // to avoid remanence
	 $bdvalue->Select(array($docid,$listattr[$i]->id));
	 $value = $bdvalue->value;
	 

	 if ($value != "") // to define when change frame
	   {
	     if ( $currentFrameId != $listattr[$i]->frameid) {
	       if ($currentFrameId != "") $changeframe=true;
	     }
	   }
       }


     //------------------------------
     // change frame if needed

     if (($i == $query->nb) ||  // to generate final frametext
	 $changeframe)
       {
	 $changeframe=false;
	 if (($v+$nbimg) > 0) // one value detected
	   {
				      
	     $frames[$k]["frametext"]="[TEXT:".$doc->GetLabel($currentFrameId)."]";
	     $frames[$k]["rowspan"]=$v+1; // for images cell
	     $frames[$k]["TABLEVALUE"]="TABLEVALUE_$k";
	     $action->lay->SetBlockData($frames[$k]["TABLEVALUE"],
					$tableframe);
	     $frames[$k]["IMAGES"]="IMAGES_$k";
	     $action->lay->SetBlockData($frames[$k]["IMAGES"],
					$tableimage);
	     unset($tableframe);
	     unset($tableimage);
	     $tableframe=array();
	     $tableimage=array();
	     $k++;
	   }
	 $v=0;
	 $nbimg=0;
       }


     //------------------------------
     // Set the table value elements
     if ($i < $query->nb)
       {
      
	 if ($value != "")
	   {
		
	       $currentFrameId = $listattr[$i]->frameid;

	     // print values
	     switch ($listattr[$i]->type)
	       {
	      
	       case "image": 
		 $tableimage[$nbimg]["imgsrc"]=$action->GetParam("CORE_BASEURL").
		    "app=".$action->parent->name."&action=EXPORTFILE&docid=".$docid."&attrid=".$listattr[$i]->id; // upload name

		 break;
	       case "application": 
		 ereg ("(.*)\|(.*)\|(.*)", $value, $reg);
		 $tableframe[$v]["value"]="<A type=\"".$reg[1]."\" HREF=\"". 
		   $reg[2]."\">".$reg[3]."</A>" ;

		 
		 break;
	       case "embed": 
		 ereg ("(.*)\|(.*)", $value, $reg);		 
		 // reg[1] is mime type
		 $src = $action->GetParam("CORE_BASEURL").
		    "app=".$action->parent->name."&action=EXPORTFILE&docid=".$docid."&attrid=".$listattr[$i]->id;
		 $tableframe[$v]["value"]="<embed autostart=false  type=\"".$reg[1]."\" src=\"". 
		   $src."\">" ;
		 $tableframe[$v]["value"].="<noembed>
       Your browser doesn't support plug-ins! Please <a
       HREF=\"".$efile."\">use a helper application instead</a>
       </noembed>";
		 break;
	       case "url": 
		 $tableframe[$v]["value"]="<A target=\"_blank\" href=\"". 
		   htmlentities($value)."\">".$value.
		   "</A>";
		 break;
	       case "mail": 
		 $tableframe[$v]["value"]="<A href=\"mailto:". 
		   htmlentities($value)."\">".$value.
		   "</A>";
		 break;
	       case "file": 
		 $tableframe[$v]["value"]="<A target=\"_blank\" href=\"".
		    $action->GetParam("CORE_BASEURL").
		    "app=".$action->parent->name."&action=EXPORTFILE&docid=".$docid."&attrid=".$listattr[$i]->id
		    ."\">".$value.
		   "</A>";
		   $tfile[$kf]["file"]=$listattr[$i]->labeltext;
		   $tfile[$kf]["attrid"]=$listattr[$i]->id;
		   $kf++;
		 break;
	       case "longtext": 
		 $tableframe[$v]["value"]=nl2br(htmlentities($value));
		 break;
	       default : 
		 $tableframe[$v]["value"]=htmlentities($value);
		 break;
		
	       }
	
	     // print name except image (printed otherthere)
	     if ($listattr[$i]->type != "image")
	       {
		 $tableframe[$v]["name"]=$action->text($doc->GetLabel($listattr[$i]->id));
		 $v++;
	       }
	     else
	       {
		 $tableimage[$nbimg]["imgalt"]=$action->text($doc->GetLabel($listattr[$i]->id));
		 $nbimg++;
	       }
	   }
       }
  
   }

 // Out


  // ------------------------------
  // define accessibility
 $kdiv=1; // only one division
      $tmenuaccess[$kdiv]["divid"] = $kdiv;



      $clf = ($doc->CanLockFile() == "");
      $cuf = ($doc->CanUnLockFile() == "");
      $cud = ($doc->CanUpdateDoc() == "");

      $tmenuaccess[$kdiv][$cancel]=1;
      if (($doc->doctype=="C") && ($cud)) $tmenuaccess[$kdiv][$chicon]=1; 
      else $tmenuaccess[$kdiv][$chicon]=2;

      if (($doc->locked != $action->user->id) && 
	  $clf) $tmenuaccess[$kdiv][$lockdoc]=1;
      else $tmenuaccess[$kdiv][$lockdoc]=0;

      if (($doc->locked != 0) && $cuf) $tmenuaccess[$kdiv][$unlockdoc]=1; 
      else $tmenuaccess[$kdiv][$unlockdoc]=0;

      if ($doc->doctype != "F") $tmenuaccess[$kdiv][$revise]=2;
      else if (($doc->lmodify == 'Y') && 
	       ($cud)) $tmenuaccess[$kdiv][$revise]=1; 
      else $tmenuaccess[$kdiv][$revise]=0;

      if ($cud) {
	$tmenuaccess[$kdiv][$editdoc]=1; 
	$tmenuaccess[$kdiv][$editattr]=1; 
	$tmenuaccess[$kdiv][$editprof]=1;
      } else if ($doc->locked < 0){ // fixed document
	$tmenuaccess[$kdiv][$editdoc]= 2;
	$tmenuaccess[$kdiv][$editattr]=2; 
	$tmenuaccess[$kdiv][$editprof]=2;
	$tmenuaccess[$kdiv][$revise]=2;
	$tmenuaccess[$kdiv][$lockdoc]=2;
	$tmenuaccess[$kdiv][$unlockdoc]=2;
	$tmenuaccess[$kdiv][$chicon]=2;
      } else {
	$tmenuaccess[$kdiv][$editdoc]=0;
	$tmenuaccess[$kdiv][$editattr]=0; 
	$tmenuaccess[$kdiv][$editprof]=0;
      }
      if ($doc->doctype=="F") $tmenuaccess[$kdiv][$histo]=1; 
      else $tmenuaccess[$kdiv][$histo]=2; 



      if ($doc->doctype!="C") $tmenuaccess[$kdiv][$editcprof]=2; 
      else if ($cud) $tmenuaccess[$kdiv][$editcprof]=1;
      else $tmenuaccess[$kdiv][$editcprof]=0;
      // unused menu items
      //$tmenuaccess[$kdiv]["vmenuitem9"]=0;

  $action->lay->SetBlockData("TABLEBODY",$frames);
  

 $owner = new User("", $doc->owner);
 $action->lay->Set("username", $owner->firstname." ".$owner->lastname);

  // display popup js
  $lpopup->Set("nbdiv",$kdiv-1);
  $lpopup->SetBlockData("MENUACCESS", $tmenuaccess);
  $action->parent->AddJsCode($lpopup->gen());


}
?>
