<?php
// ---------------------------------------------------------------
// $Id: viewfolder.php,v 1.1 2001/12/18 09:18:10 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Attic/viewfolder.php,v $
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
include_once("FREEDOM/freedom_util.php");
include_once('FREEDOM/Class.QueryDirV.php');

// -----------------------------------
// -----------------------------------
function viewfolder(&$action, $with_abstract=false) {
// -----------------------------------


  // Get all the params      
  $dirid=GetHttpVars("dirid"); // directory to see


  $action->log->start("freedom_icons");
  // Set the globals elements


  $baseurl=$action->GetParam("CORE_BASEURL");
  $standurl=$action->GetParam("CORE_STANDURL");
  $dbaccess = $action->GetParam("FREEDOM_DB");

  // Get all the params      
  $dirid=GetHttpVars("dirid"); // directory to see
  
  $dir = new Doc($dbaccess,$dirid);
  $dirid=$dir->initid;  // use initial id for directories

  $action->lay->Set("dirid",$dirid);

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");


  // Set Popup
  include_once("FREEDOM/popup_util.php");

  
  // Admin need FREEDOM_MASTER privilege
  if ($action->HasPermission("FREEDOM_MASTER"))    
    $action->lay->Set("imgadmin",$action->GetIcon("admin.gif",N_("admin")));   
  else
    $action->lay->Set("imgadmin","");

  // Add need FREEDOM privilege
  if ($action->HasPermission("FREEDOM"))    
    {
      $action->lay->Set("imgimport",$action->GetIcon("import.gif",N_("import"))); 
      $action->lay->Set("imgadd",$action->GetIcon("edit.gif",N_("add")));   
    }
  else
    {
      $action->lay->Set("imgimport","");
      $action->lay->Set("imgadd","");
    }

  // export do not need any privilege
  $action->lay->Set("imgexport",$action->GetIcon("export.gif",N_("export")));





  $action->log->tic("before query gen");  
  $oqdv=new QueryDirV($dbaccess,$dirid );
  if ($dirid == "")   $ldoc = $oqdv->getAllDoc($dirid);
  else $ldoc = $oqdv->getChildDoc($dirid);

  
  //  $ldoc = $query->Query();
  $action->log->tic("after query gen");



  
  $bdattr = new DocAttr($dbaccess);

  


  $destdir="./".GetHttpVars("app")."/Download/"; // for downloading file

  
  if ($with_abstract) {
    // ------------------------------------------------------
    // construction of SQL condition to find abstract attributes
    $abstractTable = $bdattr->GetAbstractIds();
    $sql_cond_abs = sql_cond($abstractTable,"attrid");
    $query_val = new QueryDb($dbaccess,"DocValue");
  }




  // ------------------------------------------------------
  // definition of popup menu
  popupInit("popuplist",array('vprop','editdoc','cancel','copy','delete'));


  $kdiv=1;
  $tdoc=array();

  if (is_array($ldoc)) {
  $action->log->tic("begin loop");
  while(list($k,$doc) = each($ldoc)) 
    {
      // view control
      //      print "$doc->title :".$doc-> Control("view")."<BR>";
      if ($doc-> Control("view") != "") continue;


      $docid=$doc->id;

      $tdoc[$k]["id"] = $docid;
      if ($with_abstract)
	$tdoc[$k]["blockabstract"]="abstract_$k";
      // search title for freedom item


      $tdoc[$k]["title"] = $doc->title;
      
 

      $tdoc[$k]["iconsrc"]= $doc->geticon();
  
      $tdoc[$k]["divid"] = $kdiv;

      $tdoc[$k]["locked"] ="";
      if ($doc->isRevisable()) {
	if (($doc->locked > 0) && ($doc->locked == $action->parent->user->id)) $tdoc[$k]["locked"] = $action->GetIcon("clef1.gif",N_("locked"), 20,20);
	else if ($doc->locked > 0) $tdoc[$k]["locked"] = $action->GetIcon("clef2.gif",N_("locked"), 20,20);
	else if ($doc->locked < 0) $tdoc[$k]["locked"] = $action->GetIcon("nowrite.gif",N_("fixed"), 20,20);
	else if ($doc->lmodify == "Y") if ($doc->doctype == 'F') $tdoc[$k]["locked"] = $action->GetIcon("changed2.gif",N_("changed"), 20,20);
      }
      

      $tdoc[$k]["iconsrc"]= $doc->geticon();

      // ------------------------------
      // define accessibility

      popupActive("popuplist",$kdiv,'vprop');
      popupActive("popuplist",$kdiv,'cancel');
      popupActive("popuplist",$kdiv,'copy');

      if ($dirid > 0) popupActive("popuplist",$kdiv,'delete');
      else popupInactive("popuplist",$kdiv,'delete');

      $clf = ($doc->CanLockFile() == "");
      $cuf = ($doc->CanUnLockFile() == "");
      $cud = ($doc->CanUpdateDoc() == "");
      if ($cud) {
	popupActive("popuplist",$kdiv,'editdoc');
      } else {
	popupInactive("popuplist",$kdiv,'editdoc');
      }
      
      if ($doc->doctype == "S") popupInvisible('popuplist',$kdiv,'editdoc'); 
      
      $kdiv++;
      if ($doc->doctype == 'F') $tdoc[$k]["revision"]= $doc->revision;
      else $tdoc[$k]["revision"]="";

      

	      
	
      if ($with_abstract) {
	// search abstract for freedom item

	$query_val->basic_elem->sup_where=array ("(docid=$docid)",
						 $sql_cond_abs);


	$tablevalue = $query_val->Query();

 
      // Set the table elements
	$tableabstract= array();
	$nbabs=0; // nb abstract
	for ($i=0; $i < $query_val->nb; $i++)
	  {
	
	    $lvalue = chop($tablevalue[$i]->value);

	    if ($lvalue != "")
	      {
		$oattr=$doc->GetAttribute($tablevalue[$i]->attrid);
		$tableabstract[$nbabs]["name"]=$action->text($doc->GetLabel($oattr->frameid))."-".$action->text($oattr->labeltext);

		switch ($oattr->type)
		  {
	      
		  case "application": 
		  case "embed": 
		    ereg ("(.*)\|(.*)\|(.*)", $lvalue, $reg); 
		    $tableabstract[$nbabs]["value"]=$reg[3]; // export name
		    break;
		  case "image": 
		    
		 $efile=$action->GetParam("CORE_BASEURL").
		    "app=".$action->parent->name."&action=EXPORTFILE&docid=".$docid."&attrid=".$tablevalue[$i]->attrid; // upload name

		    $tableabstract[$nbabs]["value"]="<IMG align=\"absbottom\" width=\"30\" SRC=\"".$efile. "\">";
		    break;
		  case "url": 
		    $tableabstract[$nbabs]["value"]="<A target=\"_blank\" href=\"". 
		       htmlentities($lvalue)."\">".$lvalue.
		       "</A>";
		  break;
		  case "mail": 
		    $tableabstract[$nbabs]["value"]="<A href=\"mailto:". 
		       htmlentities($lvalue)."\">".$lvalue.
		       "</A>";
		  break;
		  case "longtext": 
		    $tableabstract[$nbabs]["value"]=nl2br(htmlentities(stripslashes($lvalue)));
		  break;
		  case "file": 
		    $tableabstract[$nbabs]["value"]="<A target=\"_blank\" href=\"".
		       $action->GetParam("CORE_BASEURL").
		       "app=".$action->parent->name."&action=EXPORTFILE&docid=".$docid."&attrid=".$tablevalue[$i]->attrid
		       ."\">".$lvalue.
		       "</A>";
	          break;
		  default : 
		    $tableabstract[$nbabs]["value"]=htmlentities(stripslashes($lvalue));
		  break;
		
		  }
		$nbabs++;
	      }
	  }
	$action->lay->SetBlockData("abstract_$k",$tableabstract);

	unset($tableabstract);
      }
    }
  }
  $action->log->tic("end loop");
  // Out
  //------------------------------
  // display popup action
  $tboo[0]["boo"]="";
  $action->lay->SetBlockData("VIEWPROP",$tboo);

  $action->lay->Set("nbdiv",$kdiv-1);
  $action->lay->SetBlockData("TABLEBODY", $tdoc);

  // display popup js
  popupGen($kdiv-1);
  
  // js : manage icons
  $licon = new Layout($action->Getparam("CORE_PUBDIR")."/FREEDOM/Layout/manageicon.js", $action);
  $licon->Set("nbdiv",$kdiv-1);
  $action->parent->AddJsCode($licon->gen());

  $action->log->end("freedom_icons");
}


?>
