<?php
/**
 * importation of documents
 *
 * @author Anakeen 2002
 * @version $Id: freedom_import.php,v 1.8 2007/01/19 16:26:59 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WSH
 */
 /**
 */


global $appl,$action;

include_once("FDL/import_file.php");

$to = GetHttpVars("to"); 
if (GetHttpVars("htmlmode") == "Y") {
  // mode HTML
  $appl=new Application();
  $appl->Set("FREEDOM",	     $core);

  $action->Set("FREEDOM_IMPORT",$appl);

  $out= ($action->execute());
  if ($to) {
    include_once("FDL/sendmail.php");
    
    $themail = new Mail_mime();    
    $themail->setHTMLBody($out,false);
    
    $from=getMailAddr($action->user->id);
    if ($from == "")  $from = getParam('SMTP_FROM');
    if ($from == "")  $from = $action->user->login;

    $subject=sprintf(_("result of import  %s"), basename(GetHttpVars("file")));
    $err=sendmail($to,$from,$cc,$bcc,$subject,$themail);
    if ($err) print "Error:$err\n";
    
  } else {
    print $out;
  }

  
} else {
  // mode TEXT
  $appl=new Application();
  $appl->Set("FDL",	     $core);
  $action->Set("",$appl);

  $filename=GetHttpVars("file");
 
  add_import_file($action, $filename );
    
  
}

    

?>