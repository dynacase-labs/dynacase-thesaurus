<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: viewfolder.php,v 1.48 2003/11/03 09:01:41 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: viewfolder.php,v 1.48 2003/11/03 09:01:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/viewfolder.php,v $
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



include_once("FDL/Lib.Dir.php");
include_once("FDL/Class.DocAttr.php");
include_once("FDL/freedom_util.php");
include_once("FDL/Class.QueryDir.php");

// -----------------------------------
// -----------------------------------
function viewfolder(&$action, $with_abstract=false, $with_popup=true,
		    $column=false,
		    $slice="1000",  // view all document (not slice by slice)
		    $sqlfilters=array(),// more filters to see specials doc
		    $famid="")       // folder containt special fam id 
{
// -----------------------------------


  // Get all the params      
  $dirid=GetHttpVars("dirid"); // directory to see
  $refresh=GetHttpVars("refresh","no"); // force folder refresh
  $startpage=GetHttpVars("page","0"); // page number
  $target=GetHttpVars("target","fdoc"); // target for hyperlinks
  $sqlorder=GetHttpVars("sqlorder","title"); // order sort attribute


  // $column = ($with_popup && ($action->getParam("FREEDOM_VIEW")=="column"));
  
  // Set the globals elements


  $baseurl=$action->GetParam("CORE_BASEURL");
  $standurl=$action->GetParam("CORE_STANDURL");
  $dbaccess = $action->GetParam("FREEDOM_DB");

  


  $dir = new Doc($dbaccess,$dirid);

  $dirid=$dir->id;  // use initial id for directories
  $distinct=false;

  // control open
  if ($dir->defDoctype=='S') {    
    $aclctrl="execute";
  } else $aclctrl="open";
  if (($err=$dir->Control($aclctrl)) != "") $action->exitError($err);


  $action->lay->Set("dirtitle",stripslashes($dir->title));
  $action->lay->Set("dirid",$dirid);

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");











  if ($dirid == "")  {
    $action->exitError(_("cannot see unknow folder"));

  }
  
    if ($startpage>0) {
      $pagefolder = $action->Read("pagefolder");
      $start = $pagefolder[$startpage];
    } else $start=0;


  $ldoc = getChildDoc($dbaccess, $dirid,$start,$slice,$sqlfilters,$action->user->id,"TABLE",$famid, 
		      $distinct, $sqlorder);

  
  




  if ($with_popup) {
    // Set Popup
    include_once("FDL/popup_util.php");
    // ------------------------------------------------------
    // definition of popup menu
    popupInit("popuplist",array('vprop','editdoc','cancel','copy','addbasket','duplicate','ifld','delete'));

  }


  $kdiv=1;
  $tdoc=array();

    $nbseedoc=0;
  if (is_array($ldoc)) {
    

    // get date format 
  if ($action->GetParam("CORE_LANG") == "fr_FR") { // date format depend of locale
    setlocale (LC_TIME, "fr_FR");
    $fdate= "%d/%m/%y";
  } else {
    $fdate="%x";
  }
      
    $nbdoc=0;
  $prevFromId = -2;

  if ($column) {
    usort($ldoc,"orderbyfromid");
    $tfamdoc=array();
  }

  $doc = createDoc($dbaccess,$famid,false);
  while((list($k,$zdoc) = each($ldoc)) )
      {
	if ($column) $doc->ResetMoreValues();
	$doc->Affect($zdoc);
	if ($column) $doc->GetMoreValues();
	$nbseedoc++;

	// view control
	  //unnecessary now// if ($doc-> Control("view") != "") continue;


	$nbdoc++; // one more visible doc

	$docid=$doc->id;

	$tdoc[$k]["id"] = $docid;
	// search title for freedom item


	$tdoc[$k]["title"] = $doc->title;
	if ($doc->doctype =="C") 	$tdoc[$k]["title"] = "<B>". $doc->title ."</B>";
	if (strlen($doc->title) > 20)	$tdoc[$k]["abrvtitle"] = substr($doc->title,0,12)." ... ".substr($doc->title,-5);
	else $tdoc[$k]["abrvtitle"] =  $doc->title;

	$tdoc[$k]["profid"] = $doc->profid;
	$tdoc[$k]["revdate"] = strftime ($fdate, $doc->revdate);

      
 

	$tdoc[$k]["iconsrc"]= $doc->geticon();
  
	$tdoc[$k]["divid"] = $kdiv;

	$tdoc[$k]["locked"] ="";
	if ($doc->isRevisable()) {
	  if ($doc->locked == -1) $tdoc[$k]["locked"] = $action->GetIcon("revised.gif",N_("fixed"), 20,20);
	  else if ((abs($doc->locked) == $action->parent->user->id)) $tdoc[$k]["locked"] = $action->GetIcon("clef1.gif",N_("locked"), 20,20);
	  else if ($doc->locked != 0) $tdoc[$k]["locked"] = $action->GetIcon("clef2.gif",N_("locked"), 20,20);
	  else if ($doc->control("edit") != "")  $tdoc[$k]["locked"] = $action->GetIcon("nowrite.gif",N_("read-only"), 20,20);
	  else if ($doc->lmodify == "Y") if ($doc->doctype == 'F') $tdoc[$k]["locked"] = $action->GetIcon("changed2.gif",N_("changed"), 20,20);
	}
      

	$tdoc[$k]["iconsrc"]= $doc->geticon();

	if ($with_popup) {
	  // ------------------------------
	  // define popup accessibility

	  popupInvisible("popuplist",$kdiv,'vprop'); // don't use : idem like simple clic
	  popupActive("popuplist",$kdiv,'cancel');
	  popupActive("popuplist",$kdiv,'copy');
	  popupActive("popuplist",$kdiv,'addbasket');
	  popupActive("popuplist",$kdiv,'ifld');
	  popupActive("popuplist",$kdiv,'duplicate');

	  if ($dirid > 0) popupActive("popuplist",$kdiv,'delete');
	  else popupInactive("popuplist",$kdiv,'delete');

	  if ($doc->doctype=='C') {
	    popupInvisible("popuplist",$kdiv,'editdoc');
	  } else {
	    $cud = ($doc->CanLockFile() == "");
	    if ($cud) {
	      popupActive("popuplist",$kdiv,'editdoc');
	    } else {
	      popupInactive("popuplist",$kdiv,'editdoc');
	    }
	  }
	  
	  if ($dir->defDoctype != 'D') {
	    // it's a search :: inhibit duplicate and suppress reference
	    popupInvisible("popuplist",$kdiv,'duplicate');
	    popupInvisible("popuplist",$kdiv,'delete');	  
	  }
	}


	$kdiv++;
	if ($doc->isRevisable()) $tdoc[$k]["revision"]= $doc->revision;
	else $tdoc[$k]["revision"]="";
	if ($doc->wid > 0) $tdoc[$k]["state"]= $action->Text($doc->state);
	else $tdoc[$k]["state"]="";
      
	
	      
	if ($doc->doctype == 'D') $tdoc[$k]["isfld"]= "true";
	else $tdoc[$k]["isfld"]= "false";
	
	
	  // ----------------------------------------------------------
	  //                 ABSTRACT MODE
	  // ----------------------------------------------------------
	if ($with_abstract ) {
	  // search abstract attribute for freedom item
	  $doc->ApplyMask(); // apply mask attribute
	  $tdoc[$k]["ABSTRACTVALUES"]=$doc->viewDoc($doc->defaultabstract,"finfo");
	  $tdoc[$k]["LOrR"]=($k%2==0)?"left":"right";
	}
	
	  // ----------------------------------------------------------
	  //                 COLUMN MODE
	  // ----------------------------------------------------------
	if ($column) {
	  if ($doc->fromid != $prevFromId) {
	    $adoc = $doc->getFamDoc();
	    if (count($tdoc) > 1) {
	      $doct = $tdoc[$k];
	      array_pop($tdoc);
	      $action->lay->SetBlockData("BVAL".$prevFromId, $tdoc);
	      $tdoc=array();

	      $tdoc[$k]=$doct;
	    }

	    $tfamdoc[] = array("iconsrc"=>$tdoc[$k]["iconsrc"],
			       "ftitle"=>$adoc->title,
			       "blockattr" => "BATT".$doc->fromid,
			       "blockvalue" => "BVAL".$doc->fromid);
	      
	    // create the TR head 
	    $lattr=$adoc->GetAbstractAttributes();
	    $taname=array();
	    $emptytableabstract=array();
	    while (list($ka,$attr) = each($lattr))  {	
	      $emptytableabstract[$attr->id]["value"]="-";
	      $taname[$attr->id]["aname"]=_($attr->labelText);
	    }
	    $action->lay->SetBlockData("BATT".$doc->fromid,$taname);
	      
	  }
	  
	  reset($lattr);
	  $tvalues=array();
	  while (list($ka,$attr) = each($lattr))  {	
	    //$tvalues[]=$doc->getValue($attr->id,"-");
	      $tvalues[]=$doc->getHtmlValue($attr,$doc->getValue($attr->id,"-"),$target);
	  }
	  $tdoc[$k]["values"]=implode('</td><td class="tlist">',$tvalues);
	}
	$prevFromId=$doc->fromid;
      }
  }

  // Out
  //------------------------------
  // display popup action
  $tboo[0]["boo"]="";
  $action->lay->SetBlockData("VIEWPROP",$tboo);

  $action->lay->Set("nbdiv",$kdiv-1);
  if ($column){
    $action->lay->SetBlockData("BVAL".$prevFromId, $tdoc);
    $action->lay->SetBlockData("TABLEBODY", $tfamdoc);
  } else  $action->lay->SetBlockData("TABLEBODY", $tdoc);

  if ($with_popup) {
    // display popup js
    popupGen($kdiv-1);
  
  }

  if ($with_popup || $column) {
    // js : manage icons
    $licon = new Layout($action->Getparam("CORE_PUBDIR")."/FDL/Layout/manageicon.js", $action);
    $licon->Set("nbdiv",$kdiv-1);
    $action->parent->AddJsCode($licon->gen());
  }

  // when slicing
  $pagefolder[$startpage+1] = $nbseedoc+$start;
  $action->Register("pagefolder",$pagefolder);
  $action->lay->Set("next",$startpage+1);
  $action->lay->Set("prev",$startpage-1);

  $action->lay->Set("nbdoc",$nbdoc);



  
  return $nbdoc;
}

function orderbyfromid($a, $b) {
  
    if ($a["fromid"] == $b["fromid"]) return 0;
    if ($a["fromid"] > $b["fromid"]) return 1;
  
  return -1;
}


?>
