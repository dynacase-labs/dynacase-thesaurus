<?php
// ---------------------------------------------------------------
// $Id: modcard.php,v 1.25 2003/02/20 11:34:04 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/modcard.php,v $
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
include_once("FDL/Class.DocValue.php");
include_once("FDL/freedom_util.php");  
include_once("VAULT/Class.VaultFile.php");





// -----------------------------------
function modcard(&$action, &$ndocid) {
  // modify a card values from editcard
  // -----------------------------------
  global $HTTP_POST_VARS;
  global $HTTP_POST_FILES;

  // Get all the params      
  $docid=GetHttpVars("id",0); 
  $dirid=GetHttpVars("dirid",10);
  $classid=GetHttpVars("classid",0);

  $dbaccess = $action->GetParam("FREEDOM_DB");



  if ( $docid == 0 )
    {
      // add new document
      // search the good class of document
      $doc = createDoc($dbaccess, $classid);
      if (! $doc) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document"),$classid));
      
      
      $doc->owner = $action->user->id;
      $doc->locked = 0;
      if ($doc->fromid <= 0) {
	$doc->profid = "0"; // NO PROFILE ACCESS
      }
      $err = $doc-> Add();
      if ($err != "")  $action->ExitError($err);
      
      $doc->initid = $doc->id;// it is initial doc
	    
	    
	    
    } 
  else 
    {
      
      // initialise object
      $doc = new Doc($dbaccess, $docid);
      
      $err = $doc->lock(true); // autolock
      if ($err != "")   $action->ExitError($err);

      // test object permission before modify values (no access control on values yet)
      $err=$doc-> CanUpdateDoc();
      if ($err != "")  $action-> ExitError($err);
      
    }
  

  
  // ------------------------------
  // update POSGRES text values

  while(list($k,$v) = each($HTTP_POST_VARS) )
    {
      //print $k.":".$v."<BR>";
      
      if ($k[0] == "_") // freedom attributes  begin with  _
	{

	  
	  $attrid = substr($k,1);
	  if (is_array($v)) {
	    $value = stripslashes(implode("\n",str_replace("\n","<BR>",$v)));	    
	  }
	  else $value = stripslashes($v);
	  $doc->SetValue($attrid, $value);	      
	      
	      
	}      
    }
  
  
  // ------------------------------
  // update POSGRES files values
  while(list($k,$v) = each($HTTP_POST_FILES) )    {
      if ($k[0] == "_") // freedom attributes  begin with  _
	{	  
	  $k=substr($k,1);

	      
	    $filename=insert_file($dbaccess,$doc->id,$k);
	
	      
	      
	  if ($filename != "")
	    {
	      $doc->SetValue($k, $filename);
		  
	      $action->register("reload$docid","Y"); // to reload cached client file
	    	  
	    }
	}
    }
  
  
  
  
  
  
  $doc->lmodify='Y'; // locally modified
  $doc->refresh();

  $err=$doc-> PostModify(); 
  $err.=$doc-> Modify(); 
  // if ( $docid == 0 ) $err=$doc-> PostCreated(); 
  $doc->unlock(true); // disabled autolock
  

  
  if ($err == "") {
    
    // change state if needed
      
    $newstate=GetHttpVars("newstate","");
    $comment=GetHttpVars("comment","");
    
    $err="";

    if (($newstate != "") && ($doc->state != $newstate)) {

      if ($doc->wid > 0) {
	$wdoc = new Doc($dbaccess,$doc->wid);
	
	$wdoc->Set($doc);
	$err=$wdoc->ChangeState($newstate,$comment);

      }

    } 
    $ndocid = $doc->id;
  }


  return $err;
}



//------------------------------------------------------------
function insert_file($dbaccess,$docid, $attrid)
     //------------------------------------------------------------
{
  
  global $action;
  global $HTTP_POST_FILES;
  
  global $upload_max_filesize;
  
  $userfile = $HTTP_POST_FILES["_".$attrid];
  

  if (($userfile['tmp_name'] == "none") || ($userfile['tmp_name'] == ""))
    {
      // if no file specified, keep current file
	
      if ($userfile['name'] != "") {
	$err = sprintf(_("Filename '%s' cannot be transmitted.\nThe Size Limit is %d bytes."), $userfile['name'],ini_get('upload_max_filesize'));
	$action->ExitError($err);
      }
      return "";
    }
  

  ereg ("(.*)\.(.*)$", $userfile['name'], $reg);
  
  // print_r($userfile);
  $ext=$reg[2];
  
  
  
  
  if (is_uploaded_file($userfile['tmp_name'])) {
    // move to add extension
      $doc= new Doc($dbaccess,$docid);
    $attr= $doc->GetAttribute( $attrid);
    //$destfile=str_replace(" ","_","/tmp/".chop($doc->title)."-".$attr->labeltext.".".$ext);
    
    $destfile=str_replace(" ","_","/tmp/".$userfile['name']);

    move_uploaded_file($userfile['tmp_name'], $destfile);
    if (isset($vf)) unset($vf);
    $vf = new VaultFile($dbaccess, "FREEDOM");
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
