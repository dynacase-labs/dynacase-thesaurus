<?php
// ---------------------------------------------------------------
// $Id: mailcard.php,v 1.4 2002/07/31 10:01:53 eric Exp $
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
// -----------------------------------
function mailcard(&$action) {
  // -----------------------------------
  global $ifiles;
  $ifiles=array();
    // set title
  $docid = GetHttpVars("id");  
  $zonebodycard = GetHttpVars("zone"); // define view action

  $from = GetHttpVars("_MAIL_FROM","");
  $to = GetHttpVars("_MAIL_TO",'eric.brison@i-cesam.com');
  $cc = GetHttpVars("_MAIL_CC","");
  $comment = GetHttpVars("_MAIL_CM","");
  $bcc ="";
  $subject = GetHttpVars("_MAIL_SUBJECT");

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new Doc($dbaccess, $docid);

  if ($from == "") {
    $ma = new MailAccount("",$action->user->id);
    if ($ma->isAffected()) {
      $dom = new Domain("",$ma->iddomain);
      $from = $ma->login."@".$dom->name;
      if ($action->getParam("FDL_BCC") == "yes") $bcc="\\nbcc:$from";
    } else {
      $from = $action->user->login;
    }
  }

  

  $docmail = new Layout($action->GetLayoutFile("maildoc.xml"),$action);

  if ($comment != "") {
    $docmail->setBlockData("COMMENT", array(array("boo")));
    $docmail->set("comment", nl2br($comment));
  }

  $sgen = $docmail->gen();

   $sgen = preg_replace(array("/SRC=\"([^\"]+)\"/e","/src=\"([^\"]+)\"/e"),
 		      "srcfile('\\1')",
 		      $sgen);


  $pfout = "/tmp/".str_replace(array(" ","/"), "_",$doc->title);
  $fout = fopen($pfout,"w");
  fwrite($fout,$sgen);
  fclose($fout);

  if ($subject == "") $subject = $doc->title;
  $cmd = ("metasend  -b -S 4000000 -c '$cc' -F '$from' -t '$to$bcc'   -m 'text/html' ".
	 "-s '$subject' -e 'quoted-printable' -i mailcard -f '$pfout' ");
  $cmd .= " -/ related ";

  $afiles = $doc->GetSpecialAttributes("type='image' or type='file'");

  $vf = new VaultFile($dbaccess, "FREEDOM");
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
    
  

  $pubdir = $action->getParam("CORE_PUBDIR");
  while(list($k,$v) = each($ifiles)) {

	  if (file_exists($pubdir."/$v"))
	  $cmd .= " -n -e 'base64' -m 'image/".fileextension($v)."' ".
	    "-i '<".$v.">'  -f '".$pubdir."/$v"."'";
    
  }

  //print ($cmd);
  system ($cmd, $status);

  if ($status == 0)  $action->addlogmsg(sprintf(_("sending %s to %s"),$doc->title, $to));
  else $action->addlogmsg(sprintf(_("%s cannot be sent"),$doc->title));
  
  unlink($pfout);

  //

}
function fileextension($filename) {
	return substr(basename($filename), strrpos(basename($filename),
".") + 1);
}

function srcfile($src) {
  global $ifiles;
  $vext= array("gif","png","jpg","jpeg","bmp");


  if ((substr($src,0,3) == "cid") || 
      (substr($src,0,4) == "http") ) return "src=\"$src\"";

  if ( ! in_array(fileextension($src),$vext)) return "";

  $ifiles[] = $src;
  return "src=\"cid:$src\"";

}
?>
