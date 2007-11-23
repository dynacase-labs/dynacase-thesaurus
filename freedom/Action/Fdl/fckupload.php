<?php
/**
 * Upload image from FCKeditor
 *
 * @author Anakeen 2007
 * @version $Id: fckupload.php,v 1.1 2007/11/23 11:12:35 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/modcard.php");

/**
 * Upload image from FCKeditor
 * @param Action &$action current action
 * @global $_FILES['NewFile'] Http var : file to store
 */
function fckupload(&$action) {
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  global $_FILES;

  
  $doc=createDoc($dbaccess,"IMAGE");
  
  $k='NewFile';
  $filename=insert_file($doc->dbaccess,$k,true);

  if ($filename != "")  {    
      $doc->SetValue("img_file", $filename);
      $err=$doc->add();
      if ($err=="") {
	$doc->postmodify();
	$err=$doc->modify();

	$action->lay->set("docid",$doc->id);	
	$action->lay->set("title",$doc->title);
	 if (ereg ("(.*)\|(.*)",$filename , $reg)) {  
	   $vid=$reg[2];
	   $action->lay->set("vid",$vid);	   
	 }
      }
  }
  
}
?>