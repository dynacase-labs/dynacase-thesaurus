<?php
/**
 * Functions to send document by email
 *
 * @author Anakeen 2000 
 * @version $Id: mailcard.php,v 1.44 2005/04/15 16:21:17 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: mailcard.php,v 1.44 2005/04/15 16:21:17 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Fdl/mailcard.php,v $
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
include_once("Class.MailAccount.php");


// -----------------------------------
function mailcard(&$action) {
  // -----------------------------------

  $docid = GetHttpVars("id"); 
  $cr = GetHttpVars("cr"); // want a status
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new Doc($dbaccess, $docid);

  // control sending
  $err=$doc->control('send');
  if ($err != "") $action->exitError($err);

  $err=sendmailcard($action);  

  if ($cr == "Y") {
    if ($err != "") $action->exitError($err);
    else $action->addWarningMsg(sprintf(_("the document %s has been sended"),$doc->title));
  }
  redirect($action,GetHttpVars("redirect_app","FDL"),
	   GetHttpVars("redirect_act","FDL_CARD&latest=Y&refreshfld=Y&id=".$doc->id),
	   $action->GetParam("CORE_STANDURL"));

}
// -----------------------------------
function sendmailcard(&$action) {
  $err = sendCard($action,
		  GetHttpVars("id"),
		  GetHttpVars("_mail_to",''),
		  GetHttpVars("_mail_cc",""),
		  GetHttpVars("_mail_subject"),
		  GetHttpVars("zone"),
		  GetHttpVars("ulink","N")=="Y",
		  GetHttpVars("_mail_cm",""),
		  GetHttpVars("_mail_from",""), 
		  GetHttpVars("_mail_bcc",""), 
		  GetHttpVars("_mail_format","html")
		  );

  if ($err != "") return $err;

  // also change state sometime with confirmmail action
  
  $state = GetHttpVars("state"); 
 
  if ($state != "") {
    
    $docid = GetHttpVars("id"); 
  
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $doc = new Doc($dbaccess, $docid);
    if ($doc->wid > 0) {
      if ($state != "-") {
	$wdoc = new Doc($dbaccess,$doc->wid);
	$wdoc->Set($doc);
	$err=$wdoc->ChangeState($state,_("email sended"),true);
	if ($err != "")  $action-> ExitError($err);
      }
    } else {
      $action->AddLogMsg(sprintf(_("the document %s is not related to a workflow"),$doc->title));
    }
  }
}
// -----------------------------------
function sendCard(&$action,
		  $docid,
		  $to,$cc,$subject,
		  $zonebodycard, // define mail layout
		  $ulink=false,// don't see hyperlink
		  $comment="",
		  $from="",
		  $bcc="",
		  $format="html"// define view action
		  ) {

  if (($to == "")&&($bcc=="")) return _("mail dest is empty");
  // -----------------------------------
  $viewonly=  (GetHttpVars("viewonly","N")=="Y");
  // -----------------------------------
  global $ifiles;
  global $tfiles;
  global $tmpfile;
  global $vf; 
  global $doc;
  global $pubdir;

  $ifiles=array();
   $tfiles=array();
   $tmpfile=array();
  $mixed=true; // to see file as attachement
  // set title
  
  
  setHttpVar("target","mail");
  setHttpVar("id",$docid); // for view zone
  if (GetHttpVars("_mail_format") == "") setHttpVar("_mail_format",$format);

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new Doc($dbaccess, $docid);

  $ftitle = str_replace(array(" ","/"), "_",$doc->title);
  $ftitle = str_replace("'", "",$ftitle);
  $ftitle = str_replace("&", "",$ftitle);

  $vf = newFreeVaultFile($dbaccess);
  $pubdir = $action->getParam("CORE_PUBDIR");
  $szone=false;
  

  if ($bcc != "") $bcc = "\\nBcc:$bcc";
  
  if ($action->getParam("FDL_BCC") == "yes") {    
    $umail=getMailAddr($action->user->id);
    if ($umail != "") {      
      if ($bcc != "") $bcc = "$bcc,$umail";
      else  $bcc = "\\nBcc:$umail";
    }
  }
  if ($from == "") {
    $from=getMailAddr($action->user->id);
    if ($from == "")  $from = $action->user->login;
    
    $bcc .="\\nReturn-Path:$from";
  }

  if ($from != "") {    
    $bcc .="\\nReturn-Path:$from";
  }
  $layout="maildoc.xml"; // the default
 
  if ($zonebodycard == "") $zonebodycard=$doc->defaultmview;
  if ($zonebodycard == "") $zonebodycard=$doc->defaultview;



  if (ereg("[A-Z]+:[^:]+:S", $zonebodycard, $reg))  $szone=true;// the zonebodycard is a standalone zone ?
  if (ereg("[A-Z]+:[^:]+:T", $zonebodycard, $reg))  setHttpVar("dochead","N");// the zonebodycard without head ?


  if (ereg("html",$format, $reg)) {
    // ---------------------------
    if ($szone) {

     
      $sgen = $doc->viewDoc($zonebodycard,"mail",$ulink);

      if ($comment != "") {
	$comment= nl2br($comment);
	$sgen = preg_replace("'<body([^>]*)>'i",
			     "<body \\1><P>$comment<P><HR>",
			     $sgen);
      }
       
    } else {
      // contruct HTML mail
      
      $docmail = new Layout(getLayoutFile("FDL",$layout),$action);

      $docmail->Set("TITLE", $doc->title);
      $docmail->Set("zone", $zonebodycard);
      if ($comment != "") {
	$docmail->setBlockData("COMMENT", array(array("boo")));
	$docmail->set("comment", nl2br($comment));
      }

      $sgen = $docmail->gen();
    }
    if ($viewonly) {echo $sgen;exit;}


    $sgen1 = preg_replace("/src=\"(index[^\"]+)\"/ei",
			 "imgvaultfile('\\1')",
			 $sgen);
    $sgen1 = preg_replace(array("/SRC=\"([^\"]+)\"/e","/src=\"([^\"]+)\"/e"),
			 "srcfile('\\1')",
			 $sgen1);

    $pfout = uniqid("/tmp/".$doc->id);
    $fout = fopen($pfout,"w");
    if ($mixed) {
      fwrite($fout, preg_replace("/href=\"[^\"]*\"/i", "title=\""._("see attachement files")."\"", $sgen1));
    } else {
      fwrite($fout,$sgen1);
    }
    fclose($fout);
  }

  if (ereg("pdf",$format, $reg)) {
      // ---------------------------
      // contruct PDF mail
      if ($szone) {
	$sgen = $doc->viewDoc($zonebodycard,"mail",false);
      } else {
    
    
	$docmail2 = new Layout(getLayoutFile("FDL",$layout),$action);


	$docmail2->Set("zone", $zonebodycard);
	$docmail2->Set("TITLE", $doc->title);
  
	$sgen = $docmail2->gen();
      }
      $sgen2 = preg_replace("/src=\"([^\"]+)\"/ei",
			   "realfile('\\1')",
			   $sgen);

      $ppdf = uniqid("/tmp/".$doc->id).".pdf.html";
      $fout = fopen($ppdf,"w");
      fwrite($fout,$sgen2);
      fclose($fout);
  }


  // ---------------------------
  // contruct metasend command
  if ($subject == "") $subject = $ftitle;
  $subject = str_replace("\"","'",$subject);
  $cmd = "metasend  -b -S 4000000 -c '$cc' -F '$from' -t '$to$bcc' -s \"$subject\"  ";


  if (ereg("html",$format, $reg)) {
    $cmd .= " -/ related ";
    $cmd .= " -m 'text/html' -e 'quoted-printable' -i mailcard -f '$pfout' ";
  } else if ($format == "pdf") {
    $cmd .= " -/ mixed ";
    $ftxt = "/tmp/".str_replace(array(" ","/","(",")"), "_",uniqid($doc->id).".txt");
    $comment = str_replace("'","'\"'\"'",$comment);
    
    system("echo '$comment' > $ftxt");
    $cmd .= " -m 'text/plain' -e 'quoted-printable' -i comment -f '$ftxt' ";
  }



    // ---------------------------
    // insert attached files
  if (preg_match_all("/href=\"cid:([^\"+]*)[+|\"]/i",$sgen,$match)) {
    $tcids = $match[1]; // list of file references inserted in mail

    $afiles = $doc->GetFileAttributes();

    if (count($afiles) > 0) {
      while(list($k,$v) = each($afiles)) {
	if (in_array($v->id, $tcids)) {
	  $tva=array();
	  $cidindex="";
	  if ($v->repeat) $tva=$doc->getTValue($v->id);
	  else $tva[]=$doc->getValue($v->id);

	  while(list($ka,$va) = each($tva)) {
	    if ($va != "") {

	      list($mime,$vid)=explode("|",$va);
	      //      ereg ("(.*)\|(.*)", $va, list($mime,$vid)$reg);

	      if ($vid != "") {
		if ($vf -> Retrieve ($vid, $info) == "") {  
		
		  $cidindex= ($v->repeat)?"+$ka":"";
		  if ($mixed)    $cidindex.="zou";
		  $cmd .= " -n -e 'base64' -m '$mime;\\n\\tname=\"".$info->name."\"' ".
		    "-i '<".$v->id.$cidindex.">'  -f '".$info->path."'";
	  
		}
	      }
	    }
	  }
	}
      }
    }
  }
    // ---------------------------
    // add icon image
    if (ereg("html",$format, $reg)) {
      if (! $szone) {
	$va=$doc->icon;
	if ($va != "") {
	  list($mime,$vid)=explode("|",$va);

	  if ($vid != "") {
	    if ($vf -> Retrieve ($vid, $info) == "") {  
	      $cmd .= " -n -e 'base64' -m '$mime;\\n\\tname=\"".$info->name."\"' ".
		 "-i '<icon>'  -f '".$info->path."'";
	   
	    }
	  } else {
	    $icon=$doc->getIcon();
	    if (file_exists($pubdir."/$icon"))
	      $cmd .= " -n -e 'base64' -m 'image/".fileextension($icon)."' ".
		"-i '<icon>'  -f '".$pubdir."/$icon"."'";
	  }
	}
      }
    }
  
    
    // ---------------------------
    // add inserted image

    foreach($ifiles as $v) {

      if (file_exists($pubdir."/$v"))
	$cmd .= " -n -e 'base64' -m 'image/".fileextension($v)."' ".
	  "-i '<".$v.">'  -f '".$pubdir."/$v"."'";
    
    }

    foreach($tfiles as $k=>$v) {

      if (file_exists($v))
	$cmd .= " -n -e 'base64' -m '".trim(`file -ib "$v"`)."' ".
	  "-i '<".$k.">'  -f '".$v."'";
    
    }
  

  if (ereg("pdf",$format, $reg)) {
    // try PDF 
    $fps= uniqid("/tmp/".$doc->id)."ps";
    $fpdf= uniqid("/tmp/".$doc->id)."pdf";
    $cmdpdf = "/usr/bin/html2ps -U -i 0.5 -b $pubdir/ $ppdf > $fps && ps2pdf $fps $fpdf";

    system ($cmdpdf, $status);
    if ($status == 0)  {
      $cmd .= " -n -e 'base64' -m 'application/pdf;\\n\\tname=\"".$ftitle.".pdf\"' ".
	 "-i '<pdf>'  -f '$fpdf'";
    
    } else {
      $action->addlogmsg(sprintf(_("PDF conversion failed for %s"),$doc->title));
    }
  }  
  $cmd = "export LANG=C;".$cmd;

  system ($cmd, $status);

  $err="";
  if ($status == 0)  {
    $doc->addcomment(sprintf(_("sended to %s"), $to));
    $action->addlogmsg(sprintf(_("sending %s to %s"),$doc->title, $to)); 
    $action->addwarningmsg(sprintf(_("sending %s to %s"),$doc->title, $to));   
  } else {
    print ($cmd);
    $err=sprintf(_("%s cannot be sent"),$doc->title);
    $action->addlogmsg(sprintf(_("%s cannot be sent"),$doc->title));
    $action->addwarningmsg(sprintf(_("%s cannot be sent"),$doc->title));
   
  }

  
  // suppress temporaries files
  if (isset($ftxt))  unlink($ftxt);
  if (isset($fpdf))  unlink($fpdf);
  if (isset($fps))   unlink($fps);
  if (isset($pfout)) unlink($pfout);
  if (isset($ppdf)) unlink($ppdf);

  
  $tmpfile=array_merge($tmpfile,$tfiles);
  foreach($tmpfile as $k=>$v) {
    if (file_exists($v) && (substr($v,0,5)=="/tmp/"))
      unlink($v);    
  }
 

  return $err;

}


function srcfile($src) {
  global $ifiles;
  $vext= array("gif","png","jpg","jpeg","bmp");


  if (substr($src,0,3) == "cid")   return "src=\"$src\"";
  if (substr($src,0,4) == "http")  return "src=\"$src\"";

  if ( ! in_array(fileextension($src),$vext)) return "";

  $ifiles[$src] = $src;
  return "src=\"cid:$src\"";
}
function imgvaultfile($src) {
  global $tfiles;
  $newfile=copyvault($src);
  if ($newfile) {
    $src="img".count($tfiles);
    $tfiles[$src] = $newfile;
    return "src=\"cid:$src\"";
  }
  return "";
}
function copyvault($src) {
  global $_SERVER;

  $url="http://".$_SERVER['PHP_AUTH_USER'].":".$_SERVER['PHP_AUTH_PW'].'@'.$_SERVER['SERVER_NAME']."/what/".$src;
  $newfile=uniqid("/tmp/img");
 

  if (!copy($url, $newfile)) {
    return "";
  }
  return $newfile;
}


function realfile($src) {
  global $vf; 
  global $doc; 
  global $pubdir;
  global $tmpfile;

  $f=false;
  if ($src == "cid:icon") {
    $va=$doc->icon;
  } else { 
    if (substr($src,0,4) == "cid:") $va=$doc->getValue(substr($src,4));
    elseif (substr($src,0,5) == "index") {
      $va= copyvault($src);
      $tmpfile[]=$va;
    } else $va=$src;
  }

  if ($va != "") {
    list($mime,$vid)=explode("|",$va);

    if ($vid != "") {
      if ($vf -> Retrieve ($vid, $info) == "") {  
	$f= $info->path;
      }

    } else {
      if (file_exists($pubdir."/$va")) $f=$pubdir."/$va";
      elseif (file_exists($pubdir."/Images/$va")) $f=$pubdir."/Images/$va";
      elseif ((substr($va,0,8)=='/tmp/img') && file_exists($va)) $f=$va;

    }
  }
  
    
//   $mime=trim(`file -ib "$f"`);
//   print "<br>[$mime][$f][$va]";
//   if (substr($mime,0,5) != "image") $f="";

  if ($f) return "src=\"$f\"";
  return "";

}

?>
