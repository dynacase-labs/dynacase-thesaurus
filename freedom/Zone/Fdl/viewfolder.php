<?php
// ---------------------------------------------------------------
// $Id: viewfolder.php,v 1.4 2002/02/22 15:34:54 eric Exp $
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


include_once("FDL/Class.Dir.php");
include_once("FDL/Class.DocAttr.php");
include_once("FDL/Class.DocValue.php");
include_once("FDL/freedom_util.php");
include_once("FDL/Class.QueryDir.php");

// -----------------------------------
// -----------------------------------
function viewfolder(&$action, $with_abstract=false, $with_popup=true,
		    $slice="1000",  // view all document (not slice by slice)
		    $sqlfilters=array())    // more filters to see specials doc
{
// -----------------------------------


  // Get all the params      
  $dirid=GetHttpVars("dirid"); // directory to see
  $refresh=GetHttpVars("refresh","no"); // force folder refresh
  $startpage=GetHttpVars("page","0"); // page number



  // Set the globals elements


  $baseurl=$action->GetParam("CORE_BASEURL");
  $standurl=$action->GetParam("CORE_STANDURL");
  $dbaccess = $action->GetParam("FREEDOM_DB");

  // Get all the params      
  $dirid=GetHttpVars("dirid"); // directory to see
  
  $dir = new Dir($dbaccess,$dirid);
  $dirid=$dir->initid;  // use initial id for directories

  $action->lay->Set("dirid",$dirid);

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");


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







  if ($dirid == "")  {
    $action->exitError(_("cannot see unknow folder"));

  }
  
    if ($startpage>0) {
      $pagefolder = $action->Read("pagefolder");
      $start = $pagefolder[$startpage];
    } else $start=0;


  $ldoc = getChildDoc($dbaccess, $dirid,$start,$slice,$sqlfilters);


  
  




  
  $bdattr = new DocAttr($dbaccess);

  



  
  if ($with_abstract) {
    // ------------------------------------------------------
    // construction of SQL condition to find abstract attributes
    $abstractTable = $bdattr->GetAbstractIds();
    $sql_cond_abs = GetSqlCond($abstractTable,"attrid");
    $query_val = new QueryDb($dbaccess,"DocValue");
  }




  if ($with_popup) {
    // Set Popup
    include_once("FDL/popup_util.php");
    // ------------------------------------------------------
    // definition of popup menu
    popupInit("popuplist",array('vprop','editdoc','cancel','copy','delete'));

  }


  $kdiv=1;
  $tdoc=array();

    $nbseedoc=0;
  if (is_array($ldoc)) {
    
    // skip first start doc
    // no use psql slice and start due to reuse same query with cache

    

    // get begin page 
    
//     if ($startpage>0) {
//       $pagefolder = $action->Read("pagefolder");
//       $start = $pagefolder[$startpage];
//     } else $start=0;

//     while (($nbseedoc < $start) && (list($k,$doc) = each($ldoc))  ) $nbseedoc++;
      
    $nbdoc=0;
    while((list($k,$doc) = each($ldoc)) )
      {
	$nbseedoc++;

	// view control
	if ($doc-> Control("view") != "") continue;


	$nbdoc++; // one more visible doc

	$docid=$doc->id;

	$tdoc[$k]["id"] = $docid;
	if ($with_abstract)
	  $tdoc[$k]["blockabstract"]="abstract_$k";
	// search title for freedom item


	$tdoc[$k]["title"] = $doc->title;
	$tdoc[$k]["profid"] = $doc->profid;
      
 

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

	if ($with_popup) {
	  // ------------------------------
	  // define popup accessibility

	  popupActive("popuplist",$kdiv,'vprop');
	  popupActive("popuplist",$kdiv,'cancel');
	  popupActive("popuplist",$kdiv,'copy');

	  if ($dirid > 0) popupActive("popuplist",$kdiv,'delete');
	  else popupInactive("popuplist",$kdiv,'delete');

	
	  $cud = ($doc->CanUpdateDoc() == "");
	  if ($cud) {
	    popupActive("popuplist",$kdiv,'editdoc');
	  } else {
	    popupInactive("popuplist",$kdiv,'editdoc');
	  }
	
	  if ($doc->doctype == "S") popupInvisible('popuplist',$kdiv,'editdoc'); 
	}


	$kdiv++;
	if ($doc->doctype == 'F') $tdoc[$k]["revision"]= $doc->revision;
	else $tdoc[$k]["revision"]="";
	$tdoc[$k]["state"]= $doc->state;
      

	      
	
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

		  $tableabstract[$nbabs]["name"]=$action->text($oattr->labeltext);

		  switch ($oattr->type)
		    {
	      	    case "image": 
		    
		      $efile=$doc->GetHtmlValue($oattr,$lvalue);

		    $tableabstract[$nbabs]["value"]="<IMG align=\"absbottom\" width=\"30\" SRC=\"".$efile. "\">";
		    break;
		    
		    default : 
		      $tableabstract[$nbabs]["value"]=$doc->GetHtmlValue($oattr,$lvalue);
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

  // Out
  //------------------------------
  // display popup action
  $tboo[0]["boo"]="";
  $action->lay->SetBlockData("VIEWPROP",$tboo);

  $action->lay->Set("nbdiv",$kdiv-1);
  $action->lay->SetBlockData("TABLEBODY", $tdoc);

  if ($with_popup) {
    // display popup js
    popupGen($kdiv-1);
  
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


?>
