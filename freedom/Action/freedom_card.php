<?php
// ---------------------------------------------------------------
// $Id: freedom_card.php,v 1.15 2001/12/13 17:45:01 eric Exp $
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

include_once("FREEDOM/Class.Doc.php");
include_once("FREEDOM/Class.DocAttr.php");
include_once("FREEDOM/Class.DocValue.php");

include_once("Class.TableLayout.php");
include_once("Class.QueryDb.php");
include_once("Class.QueryGen.php");
include_once("FREEDOM/freedom_util.php");
include_once("VAULT/Class.VaultFile.php");

// -----------------------------------
// -----------------------------------
function freedom_card(&$action) {
  // -----------------------------------

  // GetAllParameters
  $docid = GetHttpVars("id");
  $abstract = (GetHttpVars("abstract",'N') == "Y");


  // Set the globals elements

  $baseurl=$action->GetParam("CORE_BASEURL");
  $standurl=$action->GetParam("CORE_STANDURL");
  $dbaccess = $action->GetParam("FREEDOM_DB");


  // Set Css
  $cssfile=$action->GetLayoutFile("freedom.css");
  $csslay = new Layout($cssfile,$action);
  $action->parent->AddCssCode($csslay->gen());

  include_once("FREEDOM/popup_util.php");

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  




  $tfile=array(); // array of file attributes 
  $kf=0; // number of files

  $doc = new Doc($dbaccess, $docid);
  $doc->refresh();
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


  $bdvalue = new DocValue($dbaccess);





  $frames= array();
  

  
  if ($abstract){
    // only 3 properties for abstract mode
    $listattr = $doc->GetAbstractAttributes();    
    $nprop=3;
  } else {
    $listattr = $doc->GetAttributes();
    $action->lay->SetBlockData("ALLPROP",array(array("boo"=>1)));
    $nprop=6;
    
  }
  // see locker for lockable document
  if ($doc->isRevisable())  {
    $action->lay->SetBlockData("LOCK",array(array("boo"=>1)));  
  } else  $nprop-=2; // revision & locker
  $action->lay->Set("nprop",$nprop);  




  $nattr = count($listattr); // attributes list count


  $k=0; // number of frametext
  $v=0;// number of value in one frametext
  $nbimg=0;// number of image in one frametext
  $currentFrameId="";

  $changeframe=false; // is true when need change frame
  $tableframe=array();
  $tableimage=array();
  $vf = new VaultFile($dbaccess, $action->parent->name);
  for ($i=0; $i < $nattr + 1; $i++)
    {

     

      //------------------------------
      // Compute value elements
      if ($i < $nattr)
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

      if (($i == $nattr) ||  // to generate final frametext
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
      if ($i < $nattr)
	{
      
	  if (($value != "") && ($listattr[$i]->visibility != "H"))
	    {
		
	      $currentFrameId = $listattr[$i]->frameid;

	      // print values
	      switch ($listattr[$i]->type)
		{
	      
		case "image": 
		  $tableimage[$nbimg]["imgsrc"]=$action->GetParam("CORE_BASEURL").
		     "app=".$action->parent->name."&action=EXPORTFILE&docid=".$docid."&attrid=".$listattr[$i]->id; // upload name

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
		  ereg ("(.*)\|(.*)", $value, $reg);		 
		  // reg[1] is mime type
		if ($vf -> Show ($reg[2], $info) == "") $fname = $info->name;
		else $fname=_("no filename");
		  $tableframe[$v]["value"]="<A target=\"_blank\" href=\"".
		     $action->GetParam("CORE_BASEURL").
		     "app=".$action->parent->name."&action=EXPORTFILE&docid=".$docid."&attrid=".$listattr[$i]->id
		     ."\">".$fname.
		     "</A>";
		$tfile[$kf]["file"]=$listattr[$i]->labeltext;
		$tfile[$kf]["attrid"]=$listattr[$i]->id;
		$kf++;
		break;
		case "textlist": 
		case "enumlist":
		case "longtext": 
		  $tableframe[$v]["value"]=nl2br(htmlentities($value));
		break;
		default : 
		  $tableframe[$v]["value"]=htmlentities($value);
		break;
		
		}

	      
	      // add link if needed
	      if ($listattr[$i]->link != "") {
		$tableframe[$v]["Abegin"]="<A href=\"";
		$tableframe[$v]["Abegin"].= urlWhatEncode(&$action, $listattr[$i]->link, $docid);
		$tableframe[$v]["Abegin"].="\">";
		$tableframe[$v]["Aend"]="</A>";
	      } else {
		$tableframe[$v]["Abegin"]="";
		$tableframe[$v]["Aend"]="";
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

  // ------------------------------------------------------
  // definition of popup menu
  popupInit('popupcard',  array('chicon','editdoc','lockdoc','revise','unlockdoc','editattr','histo','editprof','editcprof','properties','cancel'));


  $clf = ($doc->CanLockFile() == "");
  $cuf = ($doc->CanUnLockFile() == "");
  $cud = ($doc->CanUpdateDoc() == "");


  Popupactive('popupcard',$kdiv,'cancel');
  if (($doc->doctype=="C") && ($cud)) popupActive('popupcard',$kdiv,'chicon'); 
  else popupInvisible('popupcard',$kdiv,'chicon');

  if (! $doc->isRevisable() ) popupInvisible('popupcard',$kdiv,'lockdoc');
  else if (($doc->locked != $action->user->id) && 
      $clf) popupActive('popupcard',$kdiv,'lockdoc');
  else popupInactive('popupcard',$kdiv,'lockdoc');

  if (! $doc->isRevisable() ) popupInvisible('popupcard',$kdiv,'unlockdoc');
  elseif (($doc->locked != 0) && $cuf) popupActive('popupcard',$kdiv,'unlockdoc'); 
  else popupInactive('popupcard',$kdiv,'unlockdoc');

  if (! $doc->isRevisable()) popupInvisible('popupcard',$kdiv,'revise');
  else if (($doc->lmodify == 'Y') && 
	   ($cud)) popupActive('popupcard',$kdiv,'revise'); 
  else popupInactive('popupcard',$kdiv,'revise');



  if ($doc->Control("modifyacl") == "") {
    popupActive('popupcard',$kdiv,'editprof'); 
    popupActive('popupcard',$kdiv,'editcprof');
  } else {
    popupInactive('popupcard',$kdiv,'editprof');
    popupInactive('popupcard',$kdiv,'editcprof');
  }
  if ($cud) {
    popupActive('popupcard',$kdiv,'editattr'); 
    popupActive('popupcard',$kdiv,'editdoc');
  } else {
    if ($doc->locked < 0){ // fixed document
      popupInvisible('popupcard',$kdiv,'editdoc');
      popupInvisible('popupcard',$kdiv,'editattr'); 
      popupInvisible('popupcard',$kdiv,'editprof');
      popupInvisible('popupcard',$kdiv,'revise');
      popupInvisible('popupcard',$kdiv,'lockdoc');
      popupInvisible('popupcard',$kdiv,'unlockdoc');
      popupInvisible('popupcard',$kdiv,'chicon');
    } else {
      popupInactive('popupcard',$kdiv,'editattr'); 
      popupInactive('popupcard',$kdiv,'editprof');
      popupInactive('popupcard',$kdiv,'editdoc');
    }
  }
  if ($doc->doctype=="F") popupActive('popupcard',$kdiv,'histo'); 
  else popupInvisible('popupcard',$kdiv,'histo');

  if ($abstract) popupActive('popupcard',$kdiv,'properties'); 
  else popupInvisible('popupcard',$kdiv,'properties'); 


  if ($doc->doctype != "C") {
    popupInvisible('popupcard',$kdiv,'editcprof'); 
    popupInvisible('popupcard',$kdiv,'editattr'); 
  }

  if ($doc->doctype == "S") popupInvisible('popupcard',$kdiv,'editdoc'); 
  // unused menu items
  //$tmenuaccess[$kdiv]["vmenuitem9"]=0;

  $action->lay->SetBlockData("TABLEBODY",$frames);
  

  $owner = new User("", abs($doc->owner));
  $action->lay->Set("username", $owner->firstname." ".$owner->lastname);

  popupGen($kdiv);


}


// -----------------------------------
  function urlWhatEncode(&$action, $link, $docid) {
// -----------------------------------

    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $urllink="";
    for ($i=0; $i < strlen($link); $i++) {
      if ($link[$i] != "%") $urllink.=$link[$i];
      else {
	$i++;
	switch ($link[$i]) {
	case 1:
	case 2:
	case 3:
	case 4:
	case 5:
	case 6:
	case 7:
	case 8:
	case 9:

	  $sattrid="";
	  while (($link[$i] >= '0') && ($link[$i] <= '9')) {
	    $sattrid.=$link[$i];
	    $i++;
	  }
	  //	  print "attr=$sattrid";

	  $ovalue = new DocValue($dbaccess,array($docid,$sattrid));
	  $urllink.=$ovalue->value;
	  $i--;
	  break;
	case "B": // baseurl	  
	  $urllink.=$action->GetParam("CORE_BASEURL");

	  break;
	case "S": // standurl	  
	  $urllink.=$action->GetParam("CORE_STANDURL");

	  break;
	 default:
	  print "NOT $link[$i]<BR>";
	  break;
	}
      }
    }

    return ($urllink);

  }
?>
