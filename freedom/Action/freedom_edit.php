<?php

// ---------------------------------------------------------------
// $Id: freedom_edit.php,v 1.3 2001/11/14 15:31:03 eric Exp $
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
// Revision 1.3  2001/11/14 15:31:03  eric
// optimisation & divers...
//
// Revision 1.2  2001/11/09 18:54:21  eric
// et un de plus
//
// Revision 1.1  2001/11/09 09:41:14  eric
// gestion documentaire
//
// Revision 1.8  2001/10/17 14:35:55  eric
// mise en place de i18n via gettext
//
// Revision 1.7  2001/10/03 15:56:03  eric
// ajout type date pour roaming
//
// Revision 1.6  2001/09/10 16:51:45  eric
// ajout accessibilté objet
//
// Revision 1.5  2001/07/11 15:59:39  eric
// gestion erreur ldap
//
// Revision 1.4  2001/06/22 09:46:12  eric
// support attribut multimédia
//
// Revision 1.3  2001/06/19 16:08:17  eric
// correction pour type image
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
include_once("FREEDOM/Class.FileDisk.php");

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



  $docid = GetHttpVars("id");        // document to edit
  $classid = GetHttpVars("classid"); // use when new doc or change class
  $dirid = GetHttpVars("dirid"); // directory to place doc if new doc

  $doc= new Doc($dbaccess,$docid);

  

  
    

  // build list of class document
  $query = new QueryDb($dbaccess,"Doc");
  $query->AddQuery("doctype='C'");

  $selectclass=array();
  $tclassdoc = $query->Query();
  while (list($k,$cdoc)= each ($tclassdoc)) {
    $selectclass[$k]["idcdoc"]=$cdoc->initid;
    $selectclass[$k]["classname"]=$cdoc->title;
    $selectclass[$k]["selected"]="";
  }


  if ($docid == "")
    {
      $action->lay->Set("TITLE", _("new document"));
      $action->lay->Set("editaction", $action->text("create"));
      if ($classid > 0) {
	$doc=new Doc($dbaccess,$classid); // the doc inherit from chosen class
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
		if ($value != "")		
		  {
		 ereg ("(.*)\|(.*)", $value, $reg);
		 $efd = new FileDisk($dbaccess, $reg[2]);
		 $efile = $destdir.$efd->origname;  
		 $efd->Copyin($action->GetParam("CORE_PUBDIR")."/".$efile);
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
