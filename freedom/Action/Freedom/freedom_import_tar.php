<?php
/**
 * Import document descriptions
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_import_tar.php,v 1.1 2004/03/16 14:12:46 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */


include_once("FDL/import_tar.php");






function freedom_import_tar(&$action) {

  global $_FILES;
 
  $dirid = GetHttpVars("dirid"); // directory to place imported doc 
  $famid = GetHttpVars("famid"); // default import family
  $onlycsv = (GetHttpVars("onlycsv") != ""); // only files described in fdl.csv files
  $analyze = (GetHttpVars("analyze","N")=="Y"); // just analyze
  
  $uploaddir = getTarUploadDir($action);

  $dbaccess = $action->GetParam("FREEDOM_DB");
  if ($_FILES['tar']['error']!=UPLOAD_ERR_OK) {
    switch ($_FILES['tar']['error']) {
    case UPLOAD_ERR_INI_SIZE:
      $err=sprintf("The uploaded file exceeds the upload_max_filesize [%s bytes] directive in php.ini",ini_get('upload_max_filesize'));
      break;
    case UPLOAD_ERR_FORM_SIZE:
      $err="The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
      break;
    case UPLOAD_ERR_PARTIAL:
      $err="The uploaded file was only partially uploaded.";
      break;
    case UPLOAD_ERR_NO_FILE:
      $err="No file was uploaded.";
      break;
    }
    if ($err != "") $action->exitError($err);
  } else {

    
    system("mkdir -p $uploaddir");
    $uploadfile = $uploaddir . $_FILES['tar']['name'];
    if (move_uploaded_file($_FILES['tar']['tmp_name'], "$uploadfile")) {
      $report= sprintf(_("File %s is valid, and was successfully uploaded."),$_FILES['tar']['name']);
     
     
      $untardir=getTarExtractDir($action,$_FILES['tar']['name']);
     

      $status=extractTar($uploadfile,$untardir,$_FILES['tar']['type']);
      if ($status==0) $extract=sprintf(_("The file %s has been correctly extracted"),$_FILES['tar']['name']);
      else $extract=sprintf(_("The file %s cannot be extracted"),$_FILES['tar']['name']);

    } else {
      $report= _("Possible file upload attack!  Here's some debugging info:\n");
      print_r2($_FILES);
      
    }
  }

  $action->lay->set("filename",$_FILES['tar']['name']);
  $action->lay->set("report",$report);
  $action->lay->set("extract",$extract);
  $action->lay->set("dirid",$dirid);
}


function extractTar($tar,$untardir,$mime="") {
  

      
  $mime=trim(`file -ib "$tar"`);
  $mime=trim(`file -b "$tar"`);
  $mime = substr($mime,0,strpos($mime, " "));

  print "<HR>extractTar $mime";
      
     

      if ($status ==0) {
      switch ($mime) {
      case "gzip":
      case "application/x-compressed-tar":
      case "application/x-gzip":
	system("/bin/rm -fr \"$untardir\";mkdir -p \"$untardir\"",$status);
	system("cd \"$untardir\" && tar xfz $tar >/dev/null",$status);
     
	break;
      case "bzip2":
	system("/bin/rm -fr \"$untardir\";mkdir -p \"$untardir\"",$status);
	system("cd \"$untardir\" &&  tar xf $tar --use-compress-program bzip2 >/dev/null",$status);
     
	break;
      case "Zip":
      case "application/x-zip-compressed":
      case "application/x-zip":
	system("/bin/rm -fr \"$untardir\";mkdir -p \"$untardir\"",$status);
 	system("cd \"$untardir\" && unzip \"$tar\" >/dev/null",$status);
     
 	WNGBDirRename($untardir);
	break;
      default:
	$status= -2;
      }
      }
      return $status;
}

?>
