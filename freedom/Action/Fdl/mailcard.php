<?php
/**
 * Functions to send document by email
 *
 * @author Anakeen 2000 
 * @version $Id: mailcard.php,v 1.66 2007/01/18 17:12:10 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("Class.MailAccount.php");
include('Mail/mime.php');
include('Net/SMTP.php');


// -----------------------------------
function mailcard(&$action) {
  // -----------------------------------

  $docid = GetHttpVars("id"); 
  $cr = GetHttpVars("cr"); // want a status
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new_Doc($dbaccess, $docid);

  // control sending
  $err=$doc->control('send');
  if ($err != "") $action->exitError($err);

  $mailto = "";
  $mailcc = "";
  $mailbcc = "";
  $mailfrom = GetHttpVars("_mail_from");

  foreach (array("plain","link") as $format) {
    $tmailto[$format]=array();
    $tmailcc[$format]=array();
    $tmailbcc[$format]=array();
  }

  $tuid=array(); // list of user id to notify

  $mt = GetHttpVars("_mail_to","");
  if ($mt == "") {
    $rtype = GetHttpVars("_mail_copymode", "");
    $raddr = GetHttpVars("_mail_recip", "");
    $idraddr = GetHttpVars("_mail_recipid", "");
    $tformat = GetHttpVars("_mail_sendformat", "");
    if (count($raddr)>0) {
      foreach ($raddr as $k => $v) {
	$v=trim($v);
        if ($v!="") { 
	  if ($tformat[$k]=="") $tformat[$k]="plain";
          switch ($rtype[$k]) {
          case "cc": $tmailcc[$tformat[$k]][$v]=$v; break;
          case "bcc": $tmailbcc[$tformat[$k]][$v]=$v; break;
          default : 
	    $tmailto[$tformat[$k]][$v]=$v;
	    if ($idraddr[$k] > 0) $tuid[]=$idraddr[$k];
	    break;
          }
        }
      }
    }
  }

  $sendedmail=false;
  foreach (array("plain","link") as $format) {
    
    $mailto=implode(",",$tmailto[$format]);
    $mailcc=implode(",",$tmailcc[$format]);
    $mailbcc=implode(",",$tmailbcc[$format]);

    // correct trim --->
    setHttpVar("_mail_to", $mailto);
    setHttpVar("_mail_cc", $mailcc);
    setHttpVar("_mail_bcc", $mailbcc);
    setHttpVar("_mail_from", $mailfrom);
    if ($format=="link") setHttpVar("_mail_format", "htmlnotif");     
    if (($mailto!="") || ($mailcc!="") || ($mailbcc!=""))  {
      $err=sendmailcard($action);  
      $sendedmail=true;
    }
  }

  if ($cr == "Y") {
    if ($err != "") $action->exitError($err);
    elseif ($sendedmail) $action->addWarningMsg(sprintf(_("the document %s has been sended"),$doc->title));
    else $action->addWarningMsg(sprintf(_("the document %s has not been sended : no recipient"),$doc->title));
  }
  //  print_r2($tuid);
  foreach ($tuid as $uid) {
    if ($uid > 0) {
      $tu=getTDoc($dbaccess,$uid);
      $wuid=getv($tu,"us_whatid");
      //      $err=$doc->addComment(_("document received for"),HISTO_NOTICE,"RCPTDOC",$wuid);
      $err=$doc->addUTag($wuid,"TOVIEW");
    }
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
    $doc = new_Doc($dbaccess, $docid);
    if ($doc->wid > 0) {
      if ($state != "-") {
	$wdoc = new_Doc($dbaccess,$doc->wid);
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
		  $format="html", // define view action
		  $sendercopy=true, // true : a copy is send to the sender according to the Freedom user parameter 
		  $addfiles = array()
		  ) {

  // -----------------------------------
  $viewonly=  (GetHttpVars("viewonly","N")=="Y");
  if ((!$viewonly) &&($to == "")&&($cc=="")&&($bcc=="")) return _("mail dest is empty");

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
  $doc = new_Doc($dbaccess, $docid);

  $ftitle = str_replace(array(" ","/",")","("), "_",$doc->title);
  $ftitle = str_replace("'", "",$ftitle);
  $ftitle = str_replace("\"", "",$ftitle);
  $ftitle = str_replace("&", "",$ftitle);

  $to=   str_replace("\"","'",$to);
  $from= str_replace("\"","'",$from);
  $cc=   str_replace("\"","'",$cc);
  $bcc=  str_replace("\"","'",$bcc);

  $vf = newFreeVaultFile($dbaccess);
  $pubdir = $action->getParam("CORE_PUBDIR");
  $szone=false;

  $themail = new My_Mail_mime();
  
  if ($sendercopy && $action->getParam("FDL_BCC") == "yes") {    
    $umail=getMailAddr($action->user->id);
    if ($umail != "") {      
      if ($bcc != "") $bcc = "$bcc,$umail";
      else  $bcc = "$umail";
    }
  }
  if ($from == "") {
    $from=getMailAddr($action->user->id);
    if ($from == "")  $from = $action->user->login.'@'.$_SERVER["HTTP_HOST"];    
  }

  
  $layout="maildoc.xml"; // the default
  if ($format=="htmlnotif") {
    $layout="mailnotification.xml";
    $zonebodycard="FDL:MAILNOTIFICATION";
  }
 
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
      $docmail->Set("ID", $doc->id);
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

    $pfout = uniqid("/var/tmp/".$doc->id);
    $fout = fopen($pfout,"w");
   
    fwrite($fout,$sgen1);
    
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

      $ppdf = uniqid("/var/tmp/".$doc->id).".pdf.html";
      $fout = fopen($ppdf,"w");
      fwrite($fout,$sgen2);
      fclose($fout);
  }


  // ---------------------------
  // contruct metasend command
  if ($subject == "") $subject = $ftitle;
  $subject = str_replace("\"","'",$subject);

  list($login,$domain)=explode('@',$from); // add for qmail anti-virus
  $qmailopt=sprintf("QMAILUSER=\"%s\" QMAILHOST=\"%s\"",$login,$domain);

  $maxsplit=$action->getParam("FDL_SPLITSIZE",4000000);
  $cmd = "$qmailopt metasend  -b -S $maxsplit -c \"$cc\" -F \"$from\" -t \"$to$bcc\" -s \"$subject\"  ";
 

  if (ereg("html",$format, $reg)) {
    $cmd .= " -/ related ";
    $cmd .= " -m 'text/html' -e 'quoted-printable' -i mailcard -f '$pfout' ";
    //$themail->addAttachment($pfout,'text/html',$doc->title,true,'quoted-printable');
    $themail->setHTMLBody($pfout,true);
  } else if ($format == "pdf") {
    $cmd .= " -/ mixed ";
    $ftxt = "/var/tmp/".str_replace(array(" ","/","(",")"), "_",uniqid($doc->id).".txt");
    $comment = str_replace("'","'\"'\"'",$comment);
    
    system("echo '$comment' > $ftxt");
    $cmd .= " -m 'text/plain' -e 'quoted-printable' -i comment -f '$ftxt' ";
    $themail->setTxtBody($ftxt,true);
  }


  if ($format != "pdf") {

    // ---------------------------
    // insert attached files
  if (preg_match_all("/(href|src)=\"cid:([^\"]*)\"/i",$sgen,$match)) {
    $tcids = $match[2]; // list of file references inserted in mail

    $afiles = $doc->GetFileAttributes();
    $taids = array_keys($afiles);
    if (count($afiles) > 0) {
      foreach($tcids as $kf=>$vaf) {
	$tf=explode("+",$vaf);
	if (count($tf)==1) {
	  $aid=$tf[0];
	  $index=-1;
	} else {
	  $aid=$tf[0];
	  $index=$tf[1];	  
	}
	if (in_array($aid, $taids)) {	
	  $tva=array();
	  $cidindex="";
	  if ($afiles[$aid]->repeat) $va=$doc->getTValue($aid,"",$index);
	  else $va=$doc->getValue($aid);


	  if ($va != "") {

	      list($mime,$vid)=explode("|",$va);
	      //      ereg ("(.*)\|(.*)", $va, list($mime,$vid)$reg);

	      if ($vid != "") {
		if ($vf->Retrieve ($vid, $info) == "") {  
		
		  $cidindex= $vaf;
		  if (($mixed) && ($afiles[$aid]->type != "image"))  $cidindex=$info->name;
		  $cmd .= " -n -e 'base64' -m '$mime;\\n\\tname=\"".$info->name."\"\\n\\tfilename=\"".$info->name."\"' ".
		    "-i '<".$cidindex.">'  -f '".$info->path."'";
		  $themail->addAttachment($info->path,$mime,$info->name,true,'base64',$cidindex);
	  
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
	      $themail->addAttachment($info->path,$mime,$info->name,true,'base64','icon');
	      
	    }
	  } else {
	    $icon=$doc->getIcon();
	    if (file_exists($pubdir."/$icon"))
	      $cmd .= " -n -e 'base64' -m 'image/".fileextension($icon)."' ".
		"-i '<icon>'  -f '".$pubdir."/$icon"."'";
	    $themail->addAttachment($pubdir."/$icon","image/".fileextension($icon),"icon",true,'base64','icon');
	  }
	}
      }
    }
  
    
    // ---------------------------
    // add inserted image


    foreach($ifiles as $v) {

      if (file_exists($pubdir."/$v")) {
	$cmd .= " -n -e 'base64' -m 'image/".fileextension($v)."' ".
	  "-i '<".$v.">'  -f '".$pubdir."/$v"."'";
	$themail->addAttachment($pubdir."/$v","image/".fileextension($v),$v,true,'base64',$v);
      }
    }


    foreach($tfiles as $k=>$v) {
      if (file_exists($v)) {
	$cmd .= " -n -e 'base64' -m '".trim(`file -ib "$v"`)."' ".
	  "-i '<".$k.">'  -f '".$v."'";
	$themail->addAttachment($v,trim(`file -ib "$v"`),"$k",true,'base64',$k);
      }
      
    }
  
    // Other files, 
    if (count($addfiles)>0) 
      {
	foreach ($addfiles as $kf => $vf) 
	  {
	    if (count($vf)==3) 
	      {
		$fview = $vf[0];
		$fname = $vf[1];
		$fmime = $vf[2];
		
		$fgen = $doc->viewDoc($fview, "mail");
		$fpname = "/var/tmp/".str_replace(array(" ","/","(",")"), "_", uniqid($doc->id).$fname);
		if ($fp = fopen($fpname, 'w')) {
		  fwrite($fp, $fgen);
		  fclose($fp);
		}
		$fpst = stat($fpname);
		if (is_array($fpst) && $fpst["size"]>0) {
		  $cmd .= " -n -e 'base64' -m '".$fmime.";\\n\\tname=\"".$fname."\"' ".
		    "-i '<".$fname.">'  -f '".$fpname."'";
		  $themail->addAttachment($fpname,$fmime,$fname,true,'base64',$fname);
		}
	      }
	  }
      }

  }
  if (ereg("pdf",$format, $reg)) {
    // try PDF 
    $fps= uniqid("/var/tmp/".$doc->id)."ps";
    $fpdf= uniqid("/var/tmp/".$doc->id)."pdf";
    $cmdpdf = "/usr/bin/html2ps -U -i 0.5 -b $pubdir/ $ppdf > $fps && ps2pdf $fps $fpdf";

    system ($cmdpdf, $status);
    if ($status == 0)  {
      $cmd .= " -n -e 'base64' -m 'application/pdf;\\n\\tname=\"".$ftitle.".pdf\"' ".
	 "-i '<pdf>'  -f '$fpdf'";
      $themail->addAttachment($fpdf,'application/pdf',$doc->title.".pdf");
      
    } else {
      $action->addlogmsg(sprintf(_("PDF conversion failed for %s"),$doc->title));
    }
  }  
  $cmd = "export LANG=C;".$cmd;
  //  system ($cmd, $status);


  $err=mysendmail($to,$from,$cc,$bcc,$subject,$themail);
  //  mail($to, $subject, $message, $headers);
  


  if ($err=="")  {
    if ($cc != "") $lsend=sprintf("%s and %s",$to,$cc);
    else $lsend=$to;
    $doc->addcomment(sprintf(_("sended to %s"), $lsend));
    $action->addlogmsg(sprintf(_("sending %s to %s"),$doc->title, $lsend)); 
    $action->addwarningmsg(sprintf(_("sending %s to %s"),$doc->title, $lsend));   
  } else {
    $action->log->warning($err);
    $action->addlogmsg(sprintf(_("%s cannot be sent"),$doc->title));
    $action->addwarningmsg(sprintf(_("%s cannot be sent"),$doc->title));
    $action->addwarningmsg($err);
   
  }

  
  // suppress temporaries files
  if (isset($ftxt))  unlink($ftxt);
  if (isset($fpdf))  unlink($fpdf);
  if (isset($fps))   unlink($fps);
  if (isset($pfout)) unlink($pfout);
  if (isset($ppdf)) unlink($ppdf);

  
  $tmpfile=array_merge($tmpfile,$tfiles);
  foreach($tmpfile as $k=>$v) {
    if (file_exists($v) && (substr($v,0,5)=="/var/tmp/"))
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
    return "src=\"cid:$src\" ";
  }
  return "";
}
function copyvault($src) {
  global $_SERVER;

  $url="http://".$_SERVER['PHP_AUTH_USER'].":".$_SERVER['PHP_AUTH_PW'].'@'.$_SERVER['SERVER_NAME']."/what/".$src;
  $newfile=uniqid("/var/tmp/img");
 

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
      elseif ((substr($va,0,8)=='/var/tmp/img') && file_exists($va)) $f=$va;

    }
  }
  
    
//   $mime=trim(`file -ib "$f"`);
//   print "<br>[$mime][$f][$va]";
//   if (substr($mime,0,5) != "image") $f="";

  if ($f) return "src=\"$f\"";
  return "";

}


class My_Mail_mime extends Mail_mime {
  // USE TO ADD CID in attachment
  /**
     * Adds a file to the list of attachments.
     *
     * @param  string  $file       The file name of the file to attach
     *                             OR the file data itself
     * @param  string  $c_type     The content type
     * @param  string  $name       The filename of the attachment
     *                             Only use if $file is the file data
     * @param  bool    $isFilename Whether $file is a filename or not
     *                             Defaults to true
     * @return mixed true on success or PEAR_Error object
     * @access public
     */
    function addAttachment($file, $c_type = 'application/octet-stream',
                           $name = '', $isfilename = true,
                           $encoding = 'base64',$cid='')
    {
        $filedata = ($isfilename === true) ? $this->_file2str($file)
                                           : $file;
        if ($isfilename === true) {
            // Force the name the user supplied, otherwise use $file
            $filename = (!empty($name)) ? $name : basename($file);
        } else {
            $filename = $name;
        }
        if (empty($filename)) {
            return PEAR::raiseError(
              'The supplied filename for the attachment can\'t be empty'
            );
        }
        if (PEAR::isError($filedata)) {
            return $filedata;
        }

        $this->_parts[] = array(
                                'body'     => $filedata,
                                'name'     => $filename,
                                'c_type'   => $c_type,
                                'encoding' => $encoding,
                                'cid' => $cid
                               );
        return true;
    }
  
   /**
     * Adds an attachment subpart to a mimePart object
     * and returns it during the build process.
     *
     * @param  object  The mimePart to add the image to
     * @param  array   The attachment information
     * @return object  The image mimePart object
     * @access private
     */
    function &_addAttachmentPart(&$obj, $value)
    {
        $params['content_type'] = $value['c_type'];
        $params['encoding']     = $value['encoding'];
        $params['disposition']  = 'attachment';
        $params['dfilename']    = $value['name'];
        $params['cid']          = $value['cid'];
        $obj->addSubpart($value['body'], $params);
    }
}


/**
 * Send mail via smtp server
 * @param string $to mail addresses (, separate)
 * @param string $cc mail addresses (, separate)
 * @param string $bcc mail addresses (, separate)
 * @param string $from mail address
 * @param string $subject mail subject
 * @param Mail_mime &$mimemail mail mime object 
 * @return string error message : if no error: empty if no error
 */
function mysendmail($to,$from,$cc,$bcc,$subject,&$mimemail) {
 
  
  $rcpt = array($to,$cc,$bcc);

  
  $mimemail->setFrom($from);
  $mimemail->addCc($cc);
  $xh['To']=$to;
  /* Create a new Net_SMTP object. */
  if (! ($smtp = new Net_SMTP($host))) {
    die("Unable to instantiate Net_SMTP object\n");
  }
  $smtp->setDebug(false);
  /* Connect to the SMTP server. */
  if (PEAR::isError($e = $smtp->connect())) {
    return ($e->getMessage() );
  }

  /* Send the 'MAIL FROM:' SMTP command. */
  if (PEAR::isError($smtp->mailFrom($from))) {
    return ("Unable to set sender to <$from>");
  }
  
  /* Address the message to each of the recipients. */
  foreach ($rcpt as $v) {
    if ($v) {
      if (PEAR::isError($res = $smtp->rcptTo($v))) {
	return ("Unable to add recipient <$v>: " . $res->getMessage() );
      }
    }
   }
  setlocale(LC_TIME, 'C');

  $body=$mimemail->get();
  $xh['Date']=strftime("%a, %d %b %Y %H:%M:%S %z",time());
  //  $xh['Content-type']= "multipart/related";
  $xh['Subject']=$subject;
  $data="";
  $h=$mimemail->headers($xh);
  $h['Content-Type']=str_replace("mixed","related",$h['Content-Type']);

  foreach ($h as $k=>$v) {
    $data.="$k: $v\r\n";
  }
  
  $data.="\r\n".$body;

  
  /* Set the body of the message. */
  if (PEAR::isError($smtp->data($data))) {
    return ("Unable to send data");
  }

  /* Disconnect from the SMTP server. */
  $smtp->disconnect();
}
?>
