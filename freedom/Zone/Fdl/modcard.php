<?php
// ---------------------------------------------------------------
// $Id: modcard.php,v 1.9 2002/08/06 16:52:34 eric Exp $
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
	  $ofreedom = createDoc($dbaccess, $classid);
      
      
      $doc->owner = $action->user->id;
      $doc->locked = $action->user->id; // lock for next modification
	if ($ofreedom->fromid <= 0) {
	  $ofreedom->profid = "0"; // NO PROFILE ACCESS
	}
      $err = $ofreedom-> Add();
      if ($err != "")  $action->ExitError($err);
      
      $docid = $ofreedom-> id;
      $ofreedom->initid = $docid;// it is initial doc
	    
	    
	    
    } 
  else 
    {
      
      // initialise object
	$ofreedom = new Doc($dbaccess, $docid);
      
      // test object permission before modify values (no access control on values yet)
	$err=$ofreedom-> CanUpdateDoc();
      if ($err != "")  $action-> ExitError($err);
      
    }
  
  
  // ------------------------------
    // update POSGRES text values
      $bdvalue = new DocValue($dbaccess);
  $bdvalue->docid = $docid;
  while(list($k,$v) = each($HTTP_POST_VARS) )
    {
      //print $k.":".$v."<BR>";
      
      if ($k[0] == "_") // freedom attributes  begin with  _
	{
	  $oattr=new DocAttr($dbaccess, array($docid,$k));
	  
	  $bdvalue->attrid = substr($k,1);
	  if (is_array($v)) $bdvalue->value = stripslashes(implode("\n",str_replace("\n","<BR>",$v)));
	  else $bdvalue->value = stripslashes($v);
	  if ($v != "") $bdvalue ->Modify(); // only affected value
	    if ($v == " ") $bdvalue ->Delete();	//($bdvalue->value != "")) { // or reset value
	      
	      
	      
	}      
    }
  
  
  // ------------------------------
    // update POSGRES files values
      while(list($k,$v) = each($HTTP_POST_FILES) )
	{
	  if ($k[0] == "_") // freedom attributes  begin with  _
	    {	  
	      $k=substr($k,1);
	      $oattr=new DocAttr($dbaccess, array($docid,$k));
	      
	      $filename=insert_file($dbaccess,$docid,$k);
	      
	      
	      
	      if ($filename != "")
		{
		  $bdvalue->attrid = $k;
		  $bdvalue->value=$filename;
		  $bdvalue ->Modify();
		  
		  
	    	  
		}
	    }
	}
  
  
  
  
  
  if ($ofreedom->fromid == 2) {
    $ofreedom->doctype='D'; // directory
      
  }
  if (($ofreedom->fromid == 3) || 
      ($ofreedom->fromid == 4) || 
      ($ofreedom->fromid == 6)) { // profile doc
				    if ($ofreedom->fromid == 4) $ofreedom->doctype='D'; // directory profile
				      if ($ofreedom->fromid == 6) $ofreedom->doctype='S'; // search profile
					
					//$ofreedom->profid = -1;
				  $err=$ofreedom-> Modify();
				  if ($err != "") $action-> ExitError($err);
				  $ofreedom = new Doc($dbaccess, $docid); // change class object (perhaps)
				    //$ofreedom->SetControl();
				}
  $ofreedom->lmodify='Y'; // locally modified
    $err=$ofreedom-> Modify();
  $ofreedom->refresh();
  
  
  if ($err == "") {
    
    // change state if needed
      $newstate=GetHttpVars("newstate","");
    $comment=GetHttpVars("comment","");
    
    
    if (($newstate != "") && ($ofreedom->state != $newstate)) $err = $ofreedom->ChangeState($newstate,$comment );
    else   $err=$ofreedom-> Modify(); // new modify in case of the title reference a calculated value
      $ndocid = $ofreedom->id;
  }
  return $err;
}
//------------------------------------------------------------
function insert_file($dbaccess,$docid, $attrid)
     //------------------------------------------------------------
{
  
  global $HTTP_POST_FILES;
  
  $destimgdir="./".GetHttpVars("app")."/Upload/";
  
  $userfile = $HTTP_POST_FILES["_".$attrid];
  
  
  
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
