<?php
// ---------------------------------------------------------------
// $Id: freedom_icons.php,v 1.4 2001/11/15 17:51:50 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Attic/freedom_icons.php,v $
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
// $Log: freedom_icons.php,v $
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
include_once("FREEDOM/Class.FileDisk.php");
include_once('FREEDOM/Class.QueryDirV.php');


// -----------------------------------
// -----------------------------------
function freedom_icons(&$action, $with_abstract=true) {
// -----------------------------------

  $action->log->start();
  // Set the globals elements


  $baseurl=$action->GetParam("CORE_BASEURL");
  $standurl=$action->GetParam("CORE_STANDURL");
  $dbaccess = $action->GetParam("FREEDOM_DB");

  // Get all the params      
  $dirid=GetHttpVars("dirid"); // directory to see
  
  $dir = newDoc($dbaccess,$dirid);
  $dirid=$dir->initid;  // use initial id for directories

  $action->lay->Set("dirid",$dirid);

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");



  $lpopup = new Layout($action->GetLayoutFile("popup.js"),$action);

  // Set Css
  $cssfile=$action->GetLayoutFile("freedom.css");
  $csslay = new Layout($cssfile,$action);
  $action->parent->AddCssCode($csslay->gen());
  // css pour popup
  $cssfile=$action->GetLayoutFile("popup.css");
  $csslay = new Layout($cssfile,$action);
  $action->parent->AddCssCode($csslay->gen());

  
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
  $tmenuaccess = array(); // to define action an each icon
  
  if ($with_abstract) {
    // ------------------------------------------------------
    // construction of SQL condition to find abstract attributes
    $abstractTable = $bdattr->GetAbstractIds();
    $sql_cond_abs = sql_cond($abstractTable,"attrid");
    $query_val = new QueryDb($dbaccess,"DocValue");
  }




  // ------------------------------------------------------
  // definition of popup menu
  $menuitems= array('vprop','chicon','chstate','editdoc','lockdoc','revise','unlockdoc','cancel','copy','delete');
  while (list($ki, $imenu) = each($menuitems)) {
    $lpopup->Set("menuitem$ki",$imenu);
    ${$imenu} = "vmenuitem$ki";
  }
  $lpopup->Set("nbmitem", 10);


  $kdiv=1;
  $tdoc=array();

  if (is_array($ldoc)) {
  $action->log->tic("begin loop");
  while(list($k,$doc) = each($ldoc)) 
    {
      // view control
      if ($doc-> Control("view") != "") continue;


      $docid=$doc->id;

      $tdoc[$k]["id"] = $docid;
      if ($with_abstract)
	$tdoc[$k]["blockabstract"]="abstract_$k";
      // search title for freedom item


      $tdoc[$k]["title"] = $doc->title;
      
 

      $tdoc[$k]["iconsrc"]= $doc->geticon();
  
      $tdoc[$k]["divid"] = $kdiv;

      // ------------------------------
      // define accessibility
      $tmenuaccess[$kdiv]["divid"] = $kdiv;
      $tmenuaccess[$kdiv][$vprop]=1;
      $tmenuaccess[$kdiv][$cancel]=1;
      $tmenuaccess[$kdiv][$copy]=1;
      $tmenuaccess[$kdiv][$chstate]=0;
      if ($dirid > 0) $tmenuaccess[$kdiv][$delete]=1;
      else $tmenuaccess[$kdiv][$delete]=0;

      $clf = ($doc->CanLockFile() == "");
      $cuf = ($doc->CanUnLockFile() == "");
      $cud = ($doc->CanUpdateDoc() == "");
      if ($clf || $cuf) {
	$tmenuaccess[$kdiv][$chicon]=1; 
	$tmenuaccess[$kdiv][$editdoc]=1;
      } else {
	$tmenuaccess[$kdiv][$chicon]=0; 
	$tmenuaccess[$kdiv][$editdoc]=0;
      }
      if (($doc->locked != $action->user->id) && 
	  $clf) $tmenuaccess[$kdiv][$lockdoc]=1;
      else $tmenuaccess[$kdiv][$lockdoc]=0;
      if (($doc->locked != 0) && $clf) $tmenuaccess[$kdiv][$unlockdoc]=1; 
      else $tmenuaccess[$kdiv][$unlockdoc]=0;

      if (($doc->lmodify == 'Y') && $cud) $tmenuaccess[$kdiv][$revise]=1; 
      else $tmenuaccess[$kdiv][$revise]=0;
      
      
      
      $kdiv++;
      $tdoc[$k]["revision"]= $doc->revision;

      

	      
	
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
  $lpopup->Set("nbdiv",$kdiv-1);
  $lpopup->SetBlockData("MENUACCESS", $tmenuaccess);


  $action->parent->AddJsCode($lpopup->gen());
  
  // js : manage icons
  $licon = new Layout($action->GetLayoutFile("manageicon.js"),$action);
  $licon->Set("nbdiv",$kdiv-1);
  $action->parent->AddJsCode($licon->gen());

  $action->log->end("freedom_icons");
}
?>
