<?php

// ---------------------------------------------------------------
// $Id: freedom_edit.php,v 1.6 2001/11/21 08:38:58 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Attic/freedom_edit.php,v $
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
// $Log: freedom_edit.php,v $
// Revision 1.6  2001/11/21 08:38:58  eric
// ajout historique + modif sur control object
//
// Revision 1.5  2001/11/16 18:04:39  eric
// modif de fin de semaine
//
// Revision 1.4  2001/11/15 17:51:50  eric
// structuration des profils
//
// Revision 1.3  2001/11/14 15:31:03  eric
// optimisation & divers...
//
// Revision 1.2  2001/11/09 18:54:21  eric
// et un de plus
//
// Revision 1.1  2001/11/09 09:41:14  eric
// gestion documentaire
//
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
function freedom_edit(&$action) {
  // -----------------------------------

  // Set the globals elements

  $baseurl=$action->GetParam("CORE_BASEURL");
  $standurl=$action->GetParam("CORE_STANDURL");
  $dbaccess = $action->GetParam("FREEDOM_DB");

   


  // Set Css
  $cssfile=$action->GetLayoutFile("freedom.css");
  $csslay = new Layout($cssfile,$action);
  $action->parent->AddCssCode($csslay->gen());



  $docid = GetHttpVars("id",0);        // document to edit
  $classid = GetHttpVars("classid",0); // use when new doc or change class
  $dirid = GetHttpVars("dirid",0); // directory to place doc if new doc

  $doc= newDoc($dbaccess,$docid);

  // when modification 
  if (($classid == 0) && ($docid != 0) ) $classid=$doc->fromid;

  
    

  // build list of class document
  $query = new QueryDb($dbaccess,"Doc");
  $query->AddQuery("doctype='C'");

  $selectclass=array();
  $tclassdoc = $doc->GetClassesDoc($classid);
  while (list($k,$cdoc)= each ($tclassdoc)) {
    $selectclass[$k]["idcdoc"]=$cdoc->initid;
    $selectclass[$k]["classname"]=$cdoc->title;
    $selectclass[$k]["selected"]="";
  }

  // add no inherit for class document
  if ($doc->doctype=="C") {
      $selectclass[$k+1]["idcdoc"]="0";
      $selectclass[$k+1]["classname"]=_("no document type");
  }

  if ($docid == 0)
    {
      switch ($classid) {
	case 2:
	  $action->lay->Set("TITLE", _("new directory"));
	break;
	case 3:	  
	case 4:	  
	  $action->lay->Set("TITLE", _("new profile"));
	break;
      default:
	$action->lay->Set("TITLE", _("new document"));
      }
      $action->lay->Set("editaction", $action->text("create"));
      if ($classid > 0) {
	$doc=newDoc($dbaccess,$classid); // the doc inherit from chosen class
      }
      // selected the current class document
      while (list($k,$cdoc)= each ($selectclass)) {	
	if ($classid == $selectclass[$k]["idcdoc"]) {	  
	  $selectclass[$k]["selected"]="selected";
	}
      }
    }
  else
    {      



      if ($doc->locked==0) { // lock if not yet
	$err = $doc->Lock();
	if ($err != "")   $action->ExitError($err);	
      }
      $err = $doc->CanUpdateDoc();
      if ($err != "")   $action->ExitError($err);
      if (! $doc->isAffected()) $action->ExitError(_("document not referenced"));
  
      $err = $doc->lock();
      if ($err != "")   $action->ExitError($err);
      $action->lay->Set("TITLE", $doc->title);
      $action->lay->Set("editaction", $action->text("modify"));
      
      // selected the current class document
      while (list($k,$cdoc)= each ($selectclass)) {	
	if ($doc->fromid == $selectclass[$k]["idcdoc"]) {	  
	  $selectclass[$k]["selected"]="selected";
	}
      }
    }
    

 

  

  $action->lay->Set("id", $docid);
  $action->lay->Set("dirid", $dirid);
  $action->lay->SetBlockData("SELECTCLASS", $selectclass);



  // ------------------------------------------------------
  // Perform SQL search for doc attributes
  // ------------------------------------------------------
  $query = new QueryDb($dbaccess,"DocAttr");
  

  $bdvalue = new DocValue($dbaccess);


  $bdattr = new DocAttr($dbaccess);


  // initialise query with all fathers doc
  if ($docid == "") {
    if ($classid > 0) {   
      // here $doc is the class document
      $sql_cond_doc = sql_cond(array_merge($doc->GetFathersDoc(),$doc->initid), "docid");
      $query->AddQuery($sql_cond_doc);
    }
  } else {
    $sql_cond_doc = sql_cond(array_merge($doc->GetFathersDoc(),$doc->initid), "docid");
    $query->AddQuery($sql_cond_doc);
  }
  $query->AddQuery("type != 'frame'");
  $query->order_by="ordered";


  //$frames= $query->Query(0,0,"TABLE","select distinct frametext from DocAttr" );
  $frames=array();
  $listattr = $query->Query();

  




  $k=0; // number of frametext
  $v=0;// number of value in one frametext
  $currentFrameId="";
  $changeframe=false;
  $tableframe=array();
  for ($i=0; $i < $query->nb + 1; $i++)
    {

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


      if (($i == $query->nb) ||  // to generate final frametext
	  $changeframe)
	{
	 $changeframe=false;
	  if ($v > 0 ) // one value detected
	    {
				      
	      $frames[$k]["frametext"]="[TEXT:".$doc->GetLabel($currentFrameId)."]";
	      $frames[$k]["TABLEVALUE"]="TABLEVALUE_$k";
	      $action->lay->SetBlockData($frames[$k]["TABLEVALUE"],
					 $tableframe);
	      unset($tableframe);
	      $tableframe=array();
	      $k++;
	    }
	  $v=1;
	}

     $destdir="./".GetHttpVars("app")."/Download/"; // for downloading file
      //------------------------------
      // Set the table value elements
      if ($i < $query->nb)
	{
      	  
	    $currentFrameId = $listattr[$i]->frameid;
	  $tableframe[$v]["value"]=chop(htmlentities($value));
	  $label = $doc->GetLabel($listattr[$i]->id);
	  $tableframe[$v]["attrid"]=$listattr[$i]->id;
	  $tableframe[$v]["name"]=chop("[TEXT:".$label."]");
	  //$tableframe[$v]["name"]=$action->text($label);

	  // output change with type
	  switch ($listattr[$i]->type)
	    {
	      case "application":
		// image
		$tableframe[$v]["inputtype"]="<IMG align=\"absbottom\" width=\"30\" SRC=\"";
		if ($value != "")		
		  {		  
		    ereg ("(.*)\|(.*)\|(.*)", $value, $reg); 
		    $tableframe[$v]["inputtype"] .= $action->GetImageUrl("appli.png");
		    $tableframe[$v]["inputtype"] .= "\" alt=\"".$reg[3]; // export name
		  }
		else	  
		  {
		    $tableframe[$v]["inputtype"] .= $action->GetImageUrl("noappli.png");
		    $tableframe[$v]["inputtype"] .= "\" alt=\"".$action->Text("noappli");
		  }
		$tableframe[$v]["inputtype"] .= "\">";

		//input 
		$tableframe[$v]["inputtype"] .="<input size=15 type=\"file\" name=\"".$listattr[$i]->id."\" value=\"".chop(htmlentities($value))."\">";
		break;

	      case "embed":
		// image
		$tableframe[$v]["inputtype"]="<IMG align=\"absbottom\" width=\"30\" SRC=\"";
		if ($value != "")		
		  {		  
		    ereg ("(.*)\|(.*)\|(.*)", $value, $reg); 
		    $tableframe[$v]["inputtype"] .= $action->GetImageUrl("embed.png");
		    $tableframe[$v]["inputtype"] .= "\" alt=\"".$reg[3]; // export name
		  }
		else	  
		  {
		    $tableframe[$v]["inputtype"] .= $action->GetImageUrl("noembed.png");
		    $tableframe[$v]["inputtype"] .= "\" alt=\"".$action->Text("noembed");
		  }
		$tableframe[$v]["inputtype"] .= "\">";

		//input 
		$tableframe[$v]["inputtype"] .="<input size=15 type=\"file\" name=\"".$listattr[$i]->id."\" value=\"".chop(htmlentities($value))."\">";
		break;

	      case "image": 
		$tableframe[$v]["inputtype"]="<IMG align=\"absbottom\" width=\"30\" SRC=\"";
		if ($value != "")				  {
		 
		    $efile = $action->GetParam("CORE_BASEURL").
		    "app=".$action->parent->name."&action=EXPORTFILE&docid=".$docid."&attrid=".$listattr[$i]->id;
		    $tableframe[$v]["inputtype"] .=$efile;
		  }
		else	  // if no image force default image
		  $tableframe[$v]["inputtype"] .= 
		    $action-> GetParam("FREEDOM_DEFAULT_IMAGE");		
		$tableframe[$v]["inputtype"] .= "\">";

		// input 
		$tableframe[$v]["inputtype"] .="<input size=15 type=\"file\" name=\"".$listattr[$i]->id."\" value=\"".chop(htmlentities($value))."\">";
		break;

	      case "file": 
		$tableframe[$v]["inputtype"]="<IMG align=\"absbottom\" width=\"30\" SRC=\"";
		
		  $tableframe[$v]["inputtype"] .= 
		    $action-> GetParam("FREEDOM_DEFAULT_IMAGE");		
		$tableframe[$v]["inputtype"] .= "\">";

		// input 
		$tableframe[$v]["inputtype"] .="<input size=15 type=\"file\" name=\"".$listattr[$i]->id."\" value=\"".chop(htmlentities($value))."\">";
		break;

	      case "longtext": 
		$tableframe[$v]["inputtype"]="<textarea rows=2 name=\"".
		  $listattr[$i]->id."\">".
		  chop(htmlentities(stripslashes($value))).
		  "</textarea>";
		break;

	      case "date": 
		$tableframe[$v]["inputtype"]="<input type=\"text\" maxlength=\"10\" name=\"".$listattr[$i]->id."\" value=\"".chop(htmlentities($value))."\">";
		break;
	      default : 
		$tableframe[$v]["inputtype"]="<input type=\"text\" name=\"".$listattr[$i]->id."\" value=\"".chop(htmlentities(stripslashes($value)))."\">";
		break;
		
	    }
		
	
	  $v++;

	}
  
    }

  // Out
  
  $action->lay->SetBlockData("TABLEBODY",$frames);
  




 
    

}
?>
