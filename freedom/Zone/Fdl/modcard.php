<?php
/**
 * Modification of document
 *
 * @author Anakeen 2000 
 * @version $Id: modcard.php,v 1.66 2004/09/22 16:16:39 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");
include_once("FDL/freedom_util.php");  
include_once("VAULT/Class.VaultFile.php");





// -----------------------------------
function modcard(&$action, &$ndocid) {
  // modify a card values from editcard
  // -----------------------------------

  // Get all the params      
  $docid=GetHttpVars("id",0); 
  $dirid=GetHttpVars("dirid",10);
  $classid=GetHttpVars("classid",0);
  $usefor = GetHttpVars("usefor"); // use for default values for a document
  $vid = GetHttpVars("vid"); // special controlled view

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $ndocid=$docid;

  if (($usefor=="D")||($usefor=="Q")) {
    //  set values to family document
    specialmodcard($action,$usefor);
    return "";
  }
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
  

  
  // apply specified mask
  if (($vid != "") && ($doc->cvid > 0)) {
    // special controlled view
    $cvdoc= new Doc($dbaccess, $doc->cvid);
    $cvdoc->Set($doc);
    $err = $cvdoc->control($vid); // control special view
    if ($err != "") $action->exitError($err);
    $tview = $cvdoc->getView($vid);
    $doc->setMask($tview["CV_MSKID"]); // apply mask to avoid modification of invisible attribute
  }

  // ------------------------------

  $err=setPostVars($doc);

  if ($err != "") $action->Addwarningmsg($err);
  
  
  
  


  // verify attribute constraint

  
  if (((GetHttpVars("noconstraint")!="Y") || ($action->user->id!=1)) &&
      (! $doc->verifyAllConstraints())) {
    // redirect to edit action
    global $appl;


    if ($appl->name != "GENERIC") {
      global $core;
      $appl->Set("GENERIC",$core);
    }
    $action->Set("GENERIC_EDIT",
		 $appl);
    setHttpVar("viewconstraint","Y");
    $action->addWarningMsg(_("Some constraint attribute are not respected.\nYou must correct these values before save document."));
    echo ( $action->execute());
    exit;
    
    
  } else {
    if ($docid==0) {
      // now can create new doc
      $err = $doc-> Add();
      if ($err != "")  $action->ExitError($err);
      
      $doc->initid = $doc->id;// it is initial doc	    
      $ndocid = $doc->id;
    }
    $doc->lmodify='Y'; // locally modified
    $doc->refresh();
    $err=$doc-> PostModify(); 
    // add trace to know when and who modify the document
    if ( $docid == 0 ) {
      //$doc->Addcomment(_("creation"));
    } else {
      $doc->Addcomment(_("change"));
    }
    if ($err=="") {$err.=$doc-> Modify();  }

    // if ( $docid == 0 ) $err=$doc-> PostCreated(); 
     $doc->unlock(true); // disabled autolock
  
    if ($err == "") {
    
      // change state if needed
      
      $newstate=GetHttpVars("newstate","");
      $comment=GetHttpVars("comment","");
    
      $err="";


      if (($newstate != "") && ($newstate != "-")) {

	if ($doc->wid > 0) {
	  if ($newstate != "-") {
	    $wdoc = new Doc($dbaccess,$doc->wid);
	
	    $wdoc->Set($doc);
	    setPostVars($wdoc);
	    $err=$wdoc->ChangeState($newstate,$comment);
	  }
	}

      } else {
	// test if auto revision
	$fdoc = $doc->getFamDoc();

	if ($fdoc->schar == "R") {
	  $doc->AddRevision(sprintf("%s : %s",_("auto revision"),$comment));
	} else {
	  if ($comment != "") $doc->AddComment($comment);
	}
      }
      $ndocid = $doc->id;
    }
  }


  return $err;
}

function setPostVars(&$doc) {
    // update POSGRES text values
  global $_POST;
  global $_FILES;
  $err="";
  foreach ($_POST as $k=>$v)    {
      
      if ($k[0] == "_") // freedom attributes  begin with  _
	{	  
	  $attrid = substr($k,1);
	  if (is_array($v)) {
	    if (isset($v["-1"])) {
	      unset($v["-1"]);	     
	    }
	    $value = stripslashes(implode("\n",str_replace("\n","<BR>",$v)));	    
	  }
	  else $value = stripslashes($v);

	  $err=$doc->SetValue($attrid, $value);	      
	      
	      
	}      
    }
    // ------------------------------
  // update POSGRES files values
  foreach ($_FILES as $k=>$v)    {
      if ($k[0] == "_") // freedom attributes  begin with  _
	{	  
	  $k=substr($k,1);

	      
	  $filename=insert_file($doc->dbaccess,$doc->id,$k);
	
	  if ($filename != "")
	    {
	      if (substr($k,0,4) == "UPL_") $k=substr($k,4);

	      $doc->SetValue($k, $filename);
	    	  
	    }
	}
    }
  
  return $err;
}


//------------------------------------------------------------
function insert_file($dbaccess,$docid, $attrid)
     //------------------------------------------------------------
{
  
  global $action;
  global $_FILES;
  
  global $upload_max_filesize;
  

  $postfiles = $_FILES["_".$attrid];


  $toldfile=array();

  if (is_array($postfiles['tmp_name'])) {// array of file
    $tuserfiles=array();
    while(list($kp,$v) = each($postfiles) )  {
      while(list($k,$ufv) = each($v) )  {
	if ($k >= 0)	$tuserfiles[$k][$kp]=$ufv;
      }      
    }
    

  

  } else { // only one file
    $tuserfiles[]=$postfiles;
  }

  $rt=array(); // array of file to be returned
  while(list($k,$userfile) = each($tuserfiles) )    {

    $rt[$k]="";
    if ($userfile['name'] == " ")  {
      $rt[$k]=" "; // delete reference file
      continue;
    }
    if (($userfile['tmp_name'] == "none") || ($userfile['tmp_name'] == "") || ($userfile['size'] == 0))
      {
	// if no file specified, keep current file
	
	if ($userfile['name'] != "") {
	  switch ($userfile['error']) {
	  case UPLOAD_ERR_INI_SIZE:
	    $err = sprintf(_("Filename '%s' cannot be transmitted.\nThe Size Limit is %s bytes."), $userfile['name'],ini_get('upload_max_filesize'));
	    break;	    
	  case UPLOAD_ERR_FORM_SIZE:
	    $err = sprintf(_("Filename '%s' cannot be transmitted.\nThe Size Limit was specified in the HTML form."), $userfile['name']);
	    break;
	  case UPLOAD_ERR_PARTIAL:
	    $err = sprintf(_("Filename '%s' cannot be transmitted completly.\nMay be saturation of server disk."), $userfile['name']);
	    break;
	  default:
	    $err = sprintf(_("Filename '%s' cannot be transmitted."), $userfile['name']);
	  }
	  $action->ExitError($err);
	}
	// reuse old value
	
	if (substr($attrid,0,3) == "UPL") {
	  $oldfile = getHttpVars(substr($attrid,3));
	 
	  if (! is_array($oldfile))  $rt[$k]=$oldfile;
	  else if (isset($oldfile[$k])) $rt[$k]=$oldfile[$k];

	}
	
	continue;
      }
  

    ereg ("(.*)\.(.*)$", $userfile['name'], $reg);
  
    // print_r($userfile);
    $ext=$reg[2];
  
  
  
  
    if (is_uploaded_file($userfile['tmp_name'])) {
      // move to add extension
      //$destfile=str_replace(" ","_","/tmp/".chop($doc->title)."-".$attr->labeltext.".".$ext);
    
      $destfile=str_replace(" ","_","/tmp/".$userfile['name']);
      $destfile=str_replace("'","",$destfile);
      $destfile=str_replace("\"","",$destfile);

      move_uploaded_file($userfile['tmp_name'], $destfile);
      if (isset($vf)) unset($vf);
      $vf = newFreeVaultFile($dbaccess);
      $err=$vf -> Store($destfile, false , $vid);
      
      if ($err != "") {
	AddWarningMsg($err);
      }
      unlink($destfile);
    } else {
      $err = sprintf(_("Possible file upload attack: filename '%s'."), $userfile['name']);
      $action->ExitError($err);
    }


    $rt[$k]=$userfile['type']."|".$vid; // return file type and upload file name
    
  
  }

  if ((count($rt) == 0) || ((count($rt) == 1) && ($rt[0]==""))) return "";
  // return file type and upload file name
  return implode("\n",$rt);
  
}
// -----------------------------------
function specialmodcard(&$action,$usefor) {
  
  global $_POST;
  global $_FILES;

  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $classid=GetHttpVars("classid",0);
  
  $cdoc = new Doc($dbaccess, $classid); // family doc

 

  foreach ($_POST as $k=>$v)    {
      //print $k.":".$v."<BR>";
      
      if ($k[0] == "_") // freedom attributes  begin with  _
	{	  
	  $attrid = substr($k,1);
	  if (is_array($v)) {
	    if (isset($v["-1"])) {
	      unset($v["-1"]);	     
	    }
	    $value = stripslashes(implode("\n",str_replace("\n","<BR>",$v)));	    
	  }
	  else $value = stripslashes($v);
	  if ($value != "") {
	   if ($usefor=="D") $cdoc->setDefValue($attrid,$value);
	   else if ($usefor=="Q") $cdoc->setParam($attrid,$value);
	  }
	      
	      
	}      
    }

  
  // ------------------------------
  // update POSGRES files values
  foreach ($_FILES as $k=>$v)    {
      if ($k[0] == "_") // freedom attributes  begin with  _
	{	  
	  $k=substr($k,1);

	      
	  $filename=insert_file($dbaccess,$doc->id,$k);
	
	      
	  if ($filename != "")
	    {
	      if (substr($k,0,4) == "UPL_") $k=substr($k,4);
	      $cdoc->setDefValue($k,$filename);
	    }
	}
    }
  

  $cdoc->modify();

  
  redirect($action,GetHttpVars("redirect_app","FDL"),
	   GetHttpVars("redirect_act","FDL_CARD&refreshfld=N&id=$classid"),
	   $action->GetParam("CORE_STANDURL"));
}
?>
