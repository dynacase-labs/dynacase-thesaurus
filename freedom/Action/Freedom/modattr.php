<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: modattr.php,v 1.22 2004/09/22 16:16:39 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");
include_once("FDL/Lib.Attr.php");
include_once("FDL/Class.DocFam.php");
include_once("FDL/freedom_util.php");  



// -----------------------------------
function modattr(&$action) {


  // Get all the params      
  $docid=GetHttpVars("docid");
  $dirid = GetHttpVars("dirid",0); // directory to place doc if new doc

  $orders= GetHttpVars("order");
  $names= GetHttpVars("name");
  $types= GetHttpVars("type");
  $abstracts= GetHttpVars("abstractyn");
  $needed= GetHttpVars("neededyn");
  $titles= GetHttpVars("titleyn");
  $attrids= GetHttpVars("attrid");
  $frameids= GetHttpVars("frameid");
  $visibilities= GetHttpVars("visibility");
  $links= GetHttpVars("link");
  $phpfiles= GetHttpVars("phpfile");
  $phpfuncs= GetHttpVars("phpfunc");
  $elinks= GetHttpVars("elink");
  $phpconstraint= GetHttpVars("phpconstraint");
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
      $err=$doc-> Add();
      if ($err != "") $action->exitError($err);
      
      

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
      //  print $k.":".$v."<BR>";

	  
	  if ($names[$k] != "") {

	    $oattr->labeltext=stripslashes($names[$k]);
	    $oattr->title=isset($titles[$k])?$titles[$k]:"N";
	    $oattr->abstract=isset($abstracts[$k])?$abstracts[$k]:"N";
	    $oattr->needed=isset($needed[$k])?$needed[$k]:"N";
	    $oattr->type=stripslashes($types[$k]);
	    $oattr->id=strtolower($attrids[$k]);
	    $oattr->frameid=isset($frameids[$k])?$frameids[$k]:"0";
	    $oattr->ordered=isset($orders[$k])?$orders[$k]:"999";
	    $oattr->visibility=$visibilities[$k];
	    $oattr->link=stripslashes($links[$k]);
	    $oattr->phpfile=$phpfiles[$k];
	    $oattr->phpfunc=stripslashes($phpfuncs[$k]);
	    $oattr->elink=stripslashes($elinks[$k]);
	    $oattr->phpconstraint=$phpconstraint[$k];
	    $oattr->usefor='N';
	    if ($attrids[$k]=="") {
	      //print $oattr->id;
	      //     print "add $names[$k]<BR>";
	      if (isset($nattrids[$k]) && ($nattrids[$k] != ""))
		$oattr->id = $nattrids[$k];
	      $err = $oattr ->Add();
	      //	      print($err);
	    } else {
	      //print "mod $names[$k]<BR>";
	      $err=$oattr ->Modify();

	    }

	  }
	  

	
      
    }


  $wsh = getWshCmd();
  $cmd = $wsh . "--userid={$action->user->id} --api=fdl_adoc --docid=".$doc->initid;

  $err= exec($cmd, $out, $ret);

  $doc->unlock(true);
  if ($ret) $action->exitError($err);
       


  
  redirect($action,GetHttpVars("app"),"QUERYTITLE&id=".$doc->id,
	   $action->GetParam("CORE_STANDURL"));
}




?>
