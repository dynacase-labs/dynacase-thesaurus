<?php
// ---------------------------------------------------------------
// $Id: modattr.php,v 1.3 2002/03/26 16:14:00 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/modattr.php,v $
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


include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");
include_once("FDL/freedom_util.php");  



// -----------------------------------
function modattr(&$action) {
  // -----------------------------------
  global $HTTP_POST_VARS;
  global $HTTP_POST_FILES;


  //print_r($HTTP_POST_VARS);

  // Get all the params      
  $docid=GetHttpVars("docid");
  $dirid = GetHttpVars("dirid",0); // directory to place doc if new doc

  $orders= GetHttpVars("order");
  $names= GetHttpVars("name");
  $types= GetHttpVars("type");
  $abstracts= GetHttpVars("abstractyn");
  $titles= GetHttpVars("titleyn");
  $attrids= GetHttpVars("attrid");
  $frameids= GetHttpVars("frameid");
  $visibilities= GetHttpVars("visibility");
  $links= GetHttpVars("link");
  $phpfiles= GetHttpVars("phpfile");
  $phpfuncs= GetHttpVars("phpfunc");
  $nattrids= GetHttpVars("nattrid"); // for new attributes

  

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $bdfreedomattr = new DocAttr($dbaccess);
  if ( $docid == 0 )
    {
      $ofreedom = new Doc($dbaccess);
      //---------------------------
      // add new freedom familly
      //---------------------------
      $ofreedom->title = _("new familly document");
      $ofreedom->owner = $action->user->id;
      $ofreedom->locked = $action->user->id; // lock for next modification
      $ofreedom->doctype = 'C'; // it is a new class document
      $ofreedom->fromid = GetHttpVars("classid"); // inherit from
      $ofreedom->profid = "0"; // NO PROFILE ACCESS
      $ofreedom->useforprof = false;
      if (GetHttpVars("classid") >0) {
	$cdoc = new Doc($dbaccess,GetHttpVars("classid") );
	$ofreedom->classname = $cdoc->classname;
	$ofreedom->profid = $cdoc->cprofid; // inherit father profile
      }
      $ofreedom-> Add();
      $docid = $ofreedom-> id;
      
      

    } 
  else 
    {

      // initialise object
      $ofreedom = new Doc($dbaccess,$docid);
      
      // test object permission before modify values (no access control on values yet)
      $err=$ofreedom-> CanUpdateDoc();
      if ($err != "")
	  $action-> ExitError($err);

      // change class document
      $ofreedom->fromid = GetHttpVars("classid"); // inherit from
      $ofreedom-> Modify();
      
    }

  // ------------------------------
  // update POSGRES attributes
  $oattr=new DocAttr($dbaccess);
  $oattr->docid = $ofreedom->initid;
  while(list($k,$v) = each($orders) )
    {
      //print $k.":".$v."<BR>";

      if (is_int($k)) // doc attributes are identified by a number
	{
	  
	  if ($names[$k] != "") {

	    $oattr->labeltext=$names[$k];
	    $oattr->title=isset($titles[$k])?$titles[$k]:"N";
	    $oattr->abstract=isset($abstracts[$k])?$abstracts[$k]:"N";
	    $oattr->type=$types[$k];
	    $oattr->id=$attrids[$k];
	    $oattr->frameid=isset($frameids[$k])?$frameids[$k]:"0";
	    $oattr->ordered=isset($orders[$k])?$orders[$k]:"999";
	    $oattr->visibility=$visibilities[$k];
	    $oattr->link=$links[$k];
	    $oattr->phpfile=$phpfiles[$k];
	    $oattr->phpfunc=$phpfuncs[$k];

	    if ($attrids[$k]=="") {
	      //print $oattr->id;
	      //print "add $names[$k]<BR>";
	      if (isset($nattrids[$k]) && ($nattrids[$k] != ""))
		$oattr->id = $nattrids[$k];
	      $err = $oattr ->Add();
	      //	      print($err);
	    } else {
	      //print "mod $names[$k]<BR>";
	      $oattr ->Modify();
	    }
	  }
	  

	}
      
    }



  
      
  


      

  

  
  
  redirect($action,GetHttpVars("app"),"FREEDOM_EDIT&id=$docid&dirid=$dirid");
}




?>
