<?php
// ---------------------------------------------------------------
// $Id: freedom_mod.php,v 1.6 2001/11/21 13:12:55 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Attic/freedom_mod.php,v $
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
// $Log: freedom_mod.php,v $
// Revision 1.6  2001/11/21 13:12:55  eric
// ajout caractéristique creation profil
//
// Revision 1.5  2001/11/21 08:38:58  eric
// ajout historique + modif sur control object
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
//
// ---------------------------------------------------------------

include_once("FREEDOM/Class.Doc.php");
include_once("FREEDOM/Class.DocAttr.php");
include_once("FREEDOM/Class.DocValue.php");
include_once("FREEDOM/Class.FreedomLdap.php");
include_once("FREEDOM/freedom_util.php");  
include_once("VAULT/Class.VaultFile.php");



// -----------------------------------
function freedom_mod(&$action) {
  // -----------------------------------
  global $HTTP_POST_VARS;
  global $HTTP_POST_FILES;

  // Get all the params      
  $docid=GetHttpVars("id",0); 
  $dirid=GetHttpVars("dirid",0);
  $classid=GetHttpVars("classid",0);

  $dbaccess = $action->GetParam("FREEDOM_DB");


  // search the good class of document
  switch ($classid) {
  case 2: // directory
  case 4: // profile access directory
    include_once("FREEDOM/Class.Dir.php");
    $ofreedom = new Dir($dbaccess);
  break;
  default:
    include_once("FREEDOM/Class.DocFile.php");
    $ofreedom = new DocFile($dbaccess);
  }


  if ( $docid == "" )
    {
      // add new document
      $ofreedom->revision = "0";
      $ofreedom->owner = $action->user->id;
      $ofreedom->locked = $action->user->id; // lock for next modification
      $ofreedom->fileref = "0";
      $ofreedom->doctype = 'F';// it is a new  document (not a familly)
      $ofreedom->cprofid = "0"; // NO CREATION PROFILE ACCESS
      $ofreedom->useforprof = false;
      $ofreedom->fromid = $classid;

      if ($ofreedom->fromid > 0) {
	$cdoc = new Doc($dbaccess, $ofreedom->fromid);
	$ofreedom->profid = $cdoc->cprofid; // inherit from its familly	
      } else
	$ofreedom->profid = "0"; // NO PROFILE ACCESS

      $ofreedom-> Add();
      $docid = $ofreedom-> id;
      $ofreedom->initid = $docid;// it is initial doc



    } 
  else 
    {

      // initialise object
      $ofreedom -> Select($docid);
      
      // test object permission before modify values (no access control on values yet)
      $err=$ofreedom-> CanUpdateDoc();
      if ($err != "")
	  $action-> ExitError($err);
      
    }


  // ------------------------------
  // update POSGRES text values
  $bdvalue = new DocValue($dbaccess);
  $bdvalue->docid = $docid;
  while(list($k,$v) = each($HTTP_POST_VARS) )
    {
      //print $k.":".$v."<BR>";

      if (is_int($k)) // freedom attributes are identified by a number
	{
	  $oattr=new DocAttr($dbaccess, array($docid,$k));
	  

	  $bdvalue->attrid = $k;
	  $bdvalue->value = addslashes($v);
	  $bdvalue ->Modify();

	}      
    }


  // ------------------------------
  // update POSGRES files values
  while(list($k,$v) = each($HTTP_POST_FILES) )
    {
      if (is_int($k)) // freedom attributes are identified by a number
	{	  
	  $oattr=new DocAttr($dbaccess, array($docid,$k));
	  
	  $filename=insert_file($dbaccess,$docid,$k);


	  
	  if ($filename != "")
	    {
	      $bdvalue->value=$filename;
	      $bdvalue->attrid = $k;
	      $bdvalue ->Modify();
	    
	      
	    	  
	    }
	}
    }


  
  // update title     
  $ofreedom->title =  GetTitle($dbaccess,$docid);
  // change class document
  $ofreedom->fromid = $classid; // inherit from
  if ($ofreedom->fromid == 2) $ofreedom->doctype='D'; // directory
  if (($ofreedom->fromid == 3) || ($ofreedom->fromid == 4)) { // profile doc
    if ($ofreedom->fromid == 4) $ofreedom->doctype='D'; // directory profile
    $ofreedom->useforprof = true;
    $ofreedom->profid = -1;
    $ofreedom->SetControl();
  }
  $ofreedom->lmodify='Y'; // locally modified
  $err=$ofreedom-> Modify();
  if ($err != "")
	  $action-> ExitError($err);
      
  
  // ------------------------------
  // update LDAP values
  // conditions to be defined
  if (false)
    {
      $oldap=new FreedomLdap($action);
      if (  ($err=$oldap->Update($docid)) != "")
	{		  
	  $action-> ExitError($action->text($err));
	  
	    //  redirect($action,GetHttpVars("app"),"FREEDOM_EDIT&id=$docid&err=$err");
	  exit;
	}
    }

      

  if ($dirid > 0) {
    redirect($action,GetHttpVars("app"),"ADDDIRFILE&dirid=$dirid&mode=latest&docid=$docid");
    
  } else {    
    redirect($action,GetHttpVars("app"),"FREEDOM_CARD&id=$docid");
  }
}

//------------------------------------------------------------
/* Userland test for uploaded file. */ 


//------------------------------------------------------------
function insert_file($dbaccess,$docid, $attrid)
//------------------------------------------------------------
{

  global $HTTP_POST_FILES;

  $destimgdir="./".GetHttpVars("app")."/Upload/";

  $userfile = $HTTP_POST_FILES[$attrid];

      
  
  if ($userfile['tmp_name'] == "none")
    {
      // if no file specified, keep current file

      return "";
    }

  ereg ("(.*)\.(.*)$", $userfile['name'], $reg);

  //  print_r($userfile);

  $ext=$reg[2];
  



  if (is_uploaded_file($userfile['tmp_name'])) {
    // move to add extension
    $doc= new Doc($dbaccess,$docid);
    $attr= $doc->GetAttribute( $attrid);
    //$destfile=str_replace(" ","_","/tmp/".chop($doc->title)."-".$attr->labeltext.".".$ext);
    
    $destfile=str_replace(" ","_","/tmp/".$userfile['name']);
    move_uploaded_file($userfile['tmp_name'], $destfile);
    global $action;
    $vf = new VaultFile($dbaccess, $action->parent->name);
    $vf -> Store($destfile, false , $vid);

    unlink($destfile);
  } else {
    $err = sprintf(_("Possible file upload attack: filename '%s'."), $userfile['name']);
    $action->ExitError($err);
  }
  
      
  // return file type and upload file name
  return $userfile['type']."|".$vid;
    
}
?>
