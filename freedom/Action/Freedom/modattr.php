<?php
// ---------------------------------------------------------------
// $Id: modattr.php,v 1.11 2003/01/27 13:26:31 eric Exp $
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
include_once("FDL/Class.DocFam.php");
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
      $doc = new DocFam($dbaccess);
      //---------------------------
      // add new freedom familly
      //---------------------------
      $doc->title = _("new familly document");
      $doc->owner = $action->user->id;
      $doc->locked = $action->user->id; // lock for next modification
      $doc->doctype = 'C'; // it is a new class document
      $doc->fromid = GetHttpVars("classid"); // inherit from
      $doc->profid = "0"; // NO PROFILE ACCESS

      if (GetHttpVars("classid") >0) {
	$cdoc = new Doc($dbaccess,GetHttpVars("classid") );
	$doc->classname = "";
	$doc->profid = $cdoc->cprofid; // inherit father profile
      }
      $doc-> Add();
      
      

    } 
  else 
    {

      // initialise object
      $doc = new Doc($dbaccess,$docid);
      
      $doc->lock(true);
      // test object permission before modify values (no access control on values yet)
      $err=$doc-> CanUpdateDoc();
      if ($err != "")
	$action-> ExitError($err);

      // change class document
      $doc->fromid = GetHttpVars("classid"); // inherit from
      $doc-> Modify();
      
    }

  // ------------------------------
  // update POSGRES attributes
  $oattr=new DocAttr($dbaccess);
  $oattr->docid = $doc->initid;
  while(list($k,$v) = each($orders) )
    {
      //      print $k.":".$v."<BR>";

	  
	  if ($names[$k] != "") {

	    $oattr->labeltext=stripslashes($names[$k]);
	    $oattr->title=isset($titles[$k])?$titles[$k]:"N";
	    $oattr->abstract=isset($abstracts[$k])?$abstracts[$k]:"N";
	    $oattr->type=stripslashes($types[$k]);
	    $oattr->id=$attrids[$k];
	    $oattr->frameid=isset($frameids[$k])?$frameids[$k]:"0";
	    $oattr->ordered=isset($orders[$k])?$orders[$k]:"999";
	    $oattr->visibility=$visibilities[$k];
	    $oattr->link=$links[$k];
	    $oattr->phpfile=$phpfiles[$k];
	    $oattr->phpfunc=$phpfuncs[$k];

	    if ($attrids[$k]=="") {
	      //print $oattr->id;
	      //     print "add $names[$k]<BR>";
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


  $wsh = GetParam("CORE_PUBDIR")."/wsh.php";
  $cmd = $wsh . " --userid={$action->user->id} --api=fdl_adoc --docid=".$doc->initid;

  $err= exec($cmd, $out, $ret);

  $doc->unlock(true);
  if ($ret) $action->exitError($err);
       


  
  redirect($action,GetHttpVars("app"),"QUERYTITLE&id=".$doc->id);
}




?>
