<?php
// ---------------------------------------------------------------
// $Id: mailcard.php,v 1.19 2003/01/13 18:59:26 eric Exp $
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

include_once("FDL/viewbodycard.php");
include_once("Class.MailAccount.php");


// -----------------------------------
function mailcard(&$action) {
  // -----------------------------------

  $docid = GetHttpVars("id"); 
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new Doc($dbaccess, $docid);

  // control sending
  $err=$doc->control('send');
  if ($err != "") $action->exitError($err);

  sendmailcard($action);  
}
// -----------------------------------
function sendmailcard(&$action) {
  // -----------------------------------
  global $ifiles;
  global $vf; 
  global $doc;

  $ifiles=array();
  // set title
  $docid = GetHttpVars("id");  
  $zonebodycard = GetHttpVars("zone"); // define view action
  $format = GetHttpVars("_mail_format","html"); // define view action
  $szone = (GetHttpVars("szone","N")=="Y"); // the zonebodycard is a standalone zone ?
 
  $from = GetHttpVars("_mail_from","");
  $to = GetHttpVars("_mail_to",'eric.brison@i-cesam.com');
  $cc = GetHttpVars("_mail_cc","");
  $comment = GetHttpVars("_mail_cm","");
  $bcc ="";
  $subject = GetHttpVars("_mail_subject");

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new Doc($dbaccess, $docid);


  $title = str_replace(array(" ","/"), "_",$doc->title);
  $vf = new VaultFile($dbaccess, "FREEDOM");
  $pubdir = $action->getParam("CORE_PUBDIR");

  if ($action->getParam("FDL_BCC") == "yes") {
    
    $umail=getMailAddr($action->user->id);
    if ($umail != "") {
       $bcc .= "\\nBcc:$umail";
    }
  }
  if ($from == "") {
    $from=getMailAddr($action->user->id);
    if ($from == "")  $from = $action->user->login;
    
    $bcc .="\\nReturn-Path:$from";
  }

  $layout="maildoc.xml"; // the default
 
  if ($zonebodycard == "") $zonebodycard="FDL:VIEWCARD";
  if (ereg("html",$format, $reg)) {
    // ---------------------------
    if ($szone) {

     
      $sgen = $doc->viewDoc($zonebodycard,"mail",false);

      if ($comment != "") {
	$comment= nl2br($comment);
	$sgen = preg_replace("'<body([^>]+)>'",
			     "<body \\1><P>$comment<P><HR>",
			     $sgen);
      }
       
    } else {
      // contruct HTML mail
      if ($action->parent->name == "FDL")
	$docmail = new Layout($action->GetLayoutFile($layout),$action);
      else {
	$appl = new Application();
	$appl->Set("FDL",	     $action->parent);
	$docmail = new Layout($appl->GetLayoutFile($layout),$action);
      }

      $docmail->Set("TITLE", $title);
      $docmail->Set("zone", $zonebodycard);
      if ($comment != "") {
	$docmail->setBlockData("COMMENT", array(array("boo")));
	$docmail->set("comment", nl2br($comment));
      }

      $sgen = $docmail->gen();
    }
    $sgen = preg_replace(array("/SRC=\"([^\"]+)\"/e","/src=\"([^\"]+)\"/e"),
			 "srcfile('\\1')",
			 $sgen);

    $pfout = uniqid("/tmp/$title");
    $fout = fopen($pfout,"w");
    fwrite($fout,$sgen);
    fclose($fout);
  }

  if (ereg("pdf",$format, $reg)) {
    // ---------------------------
    // contruct PDF mail
    if ($szone) {
      $sgen = $doc->viewDoc($zonebodycard,"_self",false);
    } else {
    if ($action->parent->name == "FDL")
      $docmail2 = new Layout($action->GetLayoutFile($layout),$action);
    else {
      $appl = new Application();
      $appl->Set("FDL",	     $action->parent);
      $docmail2 = new Layout($appl->GetLayoutFile($layout),$action);
    }


    $docmail2->Set("zone", $zonebodycard);
    $docmail2->Set("TITLE", $title);
  
    $sgen = $docmail2->gen();
    }
    $sgen = preg_replace("/cid:([^\"]+)\"/e",
			 "realfile('\\1')",
			 $sgen);

    $phtml = uniqid("/tmp/$title").".html";
    $fout = fopen($phtml,"w");
    fwrite($fout,$sgen);
    fclose($fout);
  }

  // ---------------------------
  // contruct metasend command
  if ($subject == "") $subject = $title;
  $cmd = "metasend  -b -S 4000000 -c '$cc' -F '$from' -t '$to$bcc' -s '$subject'  ";


  if (ereg("html",$format, $reg)) {
    $cmd .= " -/ related ";
    $cmd .= " -m 'text/html' -e 'quoted-printable' -i mailcard -f '$pfout' ";
  } else if ($format == "pdf") {
    $cmd .= " -/ mixed ";
    $ftxt = "/tmp/".str_replace(array(" ","/","(",")"), "_",uniqid($title).".txt");
    system("echo '$comment' > $ftxt");
    $cmd .= " -m 'text/plain' -e 'quoted-printable' -i comment -f '$ftxt' ";
  }



    // ---------------------------
    // insert attached files

    $afiles = $doc->GetFileAttributes();

    if (count($afiles) > 0) {
    
      while(list($k,$v) = each($afiles)) {
	$va=$doc->getValue($v->id);
	if ($va != "") {
	  list($mime,$vid)=explode("|",$va);
	  //      ereg ("(.*)\|(.*)", $va, list($mime,$vid)$reg);

	  if ($vid != "") {
	    if ($vf -> Retrieve ($vid, $info) == "") {  
	      $cmd .= " -n -e 'base64' -m '$mime;\\n\\tname=\"".$info->name."\"' ".
		 "-i '<".$v->id.">'  -f '".$info->path."'";
	  
	    }
	  }
	}
      }
    }
    if (ereg("html",$format, $reg)) {
      if (! $szone) {
	// add icon image
	$va=$doc->icon;
	if ($va != "") {
	  list($mime,$vid)=explode("|",$va);

	  if ($vid != "") {
	    if ($vf -> Retrieve ($vid, $info) == "") {  
	      $cmd .= " -n -e 'base64' -m '$mime;\\n\\tname=\"".$info->name."\"' ".
		 "-i '<icon>'  -f '".$info->path."'";
	   
	    }
	  }
	}
      }
    }
  

    while(list($k,$v) = each($ifiles)) {

      if (file_exists($pubdir."/$v"))
	$cmd .= " -n -e 'base64' -m 'image/".fileextension($v)."' ".
	  "-i '<".$v.">'  -f '".$pubdir."/$v"."'";
    
    }
  

  if (ereg("pdf",$format, $reg)) {
    // try PDF 
    $fps= uniqid("/tmp/$title")."ps";
    $fpdf= uniqid("/tmp/$title")."pdf";
    $cmdpdf = "/usr/bin/html2ps -U -i 0.5 -b $pubdir/ $phtml > $fps && ps2pdf $fps $fpdf";

    system ($cmdpdf, $status);

    if ($status == 0)  {
      $cmd .= " -n -e 'base64' -m 'application/pdf;\\n\\tname=\"".$title.".pdf\"' ".
	 "-i '<pdf>'  -f '$fpdf'";
    
    } else {
      $action->addlogmsg(sprintf(_("PDF conversion failed for %s"),$doc->title));
    }
  }  
  $cmd = "export LANG=C;".$cmd;
  
  system ($cmd, $status);

  if ($status == 0)  {
    $doc->addcomment(sprintf(_("sended to %s"), $to));
    $action->addlogmsg(sprintf(_("sending %s to %s"),$title, $to));   
  } else {
    print ($cmd);
    $action->addlogmsg(sprintf(_("%s cannot be sent"),$title));
  }

  
  // suppress temporaries files
  if (isset($ftxt))  unlink($ftxt);
  if (isset($fpdf))  unlink($fpdf);
  if (isset($fps))   unlink($fps);
  if (isset($pfout)) unlink($pfout);
 

  

}


function srcfile($src) {
  global $ifiles;
  $vext= array("gif","png","jpg","jpeg","bmp");


  if (substr($src,0,3) == "cid")   return "src=\"$src\"";
  if   (substr($src,0,4) == "http")  return "src=\"$src\"";

  if ( ! in_array(fileextension($src),$vext)) return "";

  $ifiles[] = $src;
  return "src=\"cid:$src\"";
}

function realfile($src) {
  global $vf; 
  global $doc; 

  if ($src == "icon") {
    $va=$doc->icon;
  } else {
    $va=$doc->getValue($src);
  }
    if ($va != "") {
      list($mime,$vid)=explode("|",$va);


      if ($vid != "") {
	if ($vf -> Retrieve ($vid, $info) == "") {  
	  return $info->path."\"";
	}

    }
  }
  
  return "\"";

}


?>
