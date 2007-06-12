<?php
/**
 * Utilities functions for manipulate files from VAULT
 *
 * @author Anakeen 2007
 * @version $Id: Lib.Vault.php,v 1.5 2007/06/12 14:28:28 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

include_once("VAULT/Class.VaultFile.php");
include_once("VAULT/Class.VaultEngine.php");

function initVaultAccess() {
  static $FREEDOM_VAULT=false;;
  if (! $FREEDOM_VAULT) {
    include_once("VAULT/Class.VaultFile.php");
    $dbaccess=getParam("FREEDOM_DB");
    $FREEDOM_VAULT= new VaultFile($dbaccess, strtoupper(getDbName($dbaccess)));
  }
  return $FREEDOM_VAULT;
}


/**
 * Generate a conversion of a file
 * The result is store in vault itself
 * @param string $engine the convert engine identificator (from VaultEngine Class)
 * @param int $idfile vault file identificator (original file)
 * @param int &$gen_idfile vault identificator of new stored file
 * @return string error message (empty if OK)
 */
function vault_generate($engine,$idfile,&$gen_idfile) {
  
  $FREEDOM_VAULT=initVaultAccess();
  $gen_idfile=0;
  $vf=new VaultDiskStorage($FREEDOM_VAULT->dbaccess,$idfile);
  if ($vf->isAffected()) {
    $nvid=$vf->getEngineFile($engine);
    if ($nvid > 0) $gen_idfile=$nvid;
    else {
      $mime=$vf->mime_s;
      $eng=new VaultEngine($vf->dbaccess,array($engine,$mime));
      if (! $eng->isAffected()) {
	$eng=$eng->GetNearEngine($engine,$mime);	
      }
      if ($eng) {
	//print "Using ".$eng->command."(". $eng->comment;
	$err=$vf->executeEngine($eng,$gen_idfile);
	//if ($err) 	print "Error:<b>$err</b> ";
      }
      
    }
  }
  return $err;
}

/**
 * return various informations for a file stored in VAULT 
 * @param int $idfile vault file identificator 
 * @param string $teng_name transformation engine name
 * @return array 
 */
function vault_properties($idfile,$teng_name="") {
  
  $FREEDOM_VAULT=initVaultAccess();  
  $FREEDOM_VAULT->Show($idfile, $info,$teng_name);
  return $info;
}

/**
 * return context of a file
 * @param int $idfile vault file identificator 
 * @return array 
 */
function vault_get_content($idfile) {
  $FREEDOM_VAULT=initVaultAccess();
  $v=new VaultDiskStorage($FREEDOM_VAULT->dbaccess,$idfile);

  if ($v->isAffected()) {
    $path=$v->getPath();
    if (file_exists($path)) return file_get_contents($path);
  }
  return false;
}

function sendLatinTransformation($dbaccess,$docid,$attrid,$index,$vid) {
  if (($docid >0)  && ($vid>0)) {
    if (include_once("TE/Class.TEClient.php")) {
      global $action;
      include_once("FDL/Class.TaskRequest.php");
      $of=new VaultDiskStorage($dbaccess,$vid);
      $filename=$of->getPath();
      //      error_log("sendLatinTransformation $filename");
      $au=getParam("CORE_URLINDEX");
      if ($au != "") $urlindex=getParam("CORE_URLINDEX").'?sole=Y';
      else {
	$scheme=getParam("CORE_ABSURL");
	if ($scheme=="") $urlindex='?sole=Y';
	else $urlindex=getParam("CORE_ABSURL").getParam("CORE_STANDURL");
      }
      $callback=$urlindex."&app=FDL&action=SETTXTFILE&docid=$docid&attrid=".$attrid."&index=$index";
      $ot=new TransformationEngine(getParam("TE_HOST"),getParam("TE_PORT"));
      $err=$ot->sendTransformation('latin',$vid,$filename,$callback,$info);
      if ($err != "") AddWarningMsg($err);
      $tr=new TaskRequest($dbaccess);
      $tr->tid=$info["tid"];
      $tr->fkey=$vid;
      $tr->status=$info["status"];
      $tr->comment=$info["comment"];
      $tr->uid=$action->user->id;
      $tr->uname=$action->user->firstname." ".$action->user->lastname;
      $err=$tr->Add();
    }
  }
}

?>