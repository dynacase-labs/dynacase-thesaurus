<?php
/**
 * Retrieve and store file in Vault
 *
 * @author Anakeen 2004
 * @version $Id: Class.VaultFile.php,v 1.15 2006/11/30 17:39:01 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package VAULT
 */
 /**
 */

include_once("VAULT/Class.VaultDiskStorage.php");
include_once("VAULT/Class.VaultCache.php");
include_once("Class.Log.php");
define("VAULT_MAXENTRIESBYDIR",1000);
Class VaultFile {

  function __construct($access, $vaultname="Sample", $idf=-1) {

    
    if (!isset($chrono)) $this->chrono = FALSE;
    else $this->chrono = $chrono;
    $this->idf      = $idf;
    $this->name     = $vaultname;
    $this->logger = new Log("", "vault", $this->name);
    if ($this->chrono)  $this->logger->warning("Running with chrono !!!!");
    $this->dbaccess = $access;

    $this->u_owner  = HTTP_USER;
    $this->g_owner  = HTTP_USER;
    $this->f_mode   = 0600;
    $this->d_mode   = 0700;
    $this->type     = "fs";
    $this->use_cache = TRUE;
    switch ($this->type) {
    case "fs" :
      $this->use_cache = FALSE;
      $this->logger->debug("Set Storage Type to FS");
      $this->storage = new VaultDiskStorage($this, $fs);
      break;
    default:
      // Not implemented yet
    }
    if ($this->use_cache) {
      // Please, test before use....
      $this->logger->debug("Instanciate Cache...");
      $this->cache = new VaultCache($this, $cache_def);
    }
  }
 
  // ---------------------------------------------------------
  function Show($id_file, &$infos) {
  // ---------------------------------------------------------
    if ($this->chrono) $this->logger->start("Show");
    $msg = $this->storage->Show($id_file, $infos);
    if ($msg!='') $this->logger->error($msg);
    if ($this->chrono) $this->logger->end("Show");
    return($msg);
  }

  // ---------------------------------------------------------
  function Retrieve($id_file, &$infos) {
  // ---------------------------------------------------------
    if ($this->chrono) $this->logger->start("Retrieve");
    if (isset($info)) unset($infos);
    if ($this->use_cache) {
      $msg = $this->cache->Show($id_file, $infosC);
      if ($msg != '') {
	$msg = $this->storage->Show($id_file, $infosS);
	if ( $msg != '' ) {
	  $msg = $this->cache->StoreIn($id_file, $info["path"], $info["size"]);
	  if ($msg == '') {
	    $msg = $this->cache->Show($id_file, $infosC);
	    $info = $infoC;
	    return '';
	  } else { 
	    $this->logger->warning("Cache insertion failure [$msg].");
	    $info = $infoS;
	    return '';
	  }
	} else {
	  $this->logger->error($msg);
	  $info = NULL;
	  return($msg);
	}
      } else {
	$info = $infosC;
	return('');
      }
    } else {
      $msg = $this->storage->Show($id_file, $infos);
      if ($msg!='') $this->logger->error($msg);
      if ($this->chrono) $this->logger->end("Retrieve");
      return($msg);
    }
  }

  // ---------------------------------------------------------
  function Store($infile, $public_access, &$id) {
  // ---------------------------------------------------------

    if ($this->chrono) $this->logger->start("Store");
    $id = -1;
    if (!file_exists($infile) || !is_readable($infile) || !is_file($infile)) {
      $this->logger->error("Can't access file [".$infile."].");
      $msg = _("can't access file");
    } else {
      if (!is_bool($public_access)) {
	$public_access  = FALSE;
	$this->logger->warning("Access mode forced to RESTRICTED for ".$infile."].");
      }
      $msg = $this->storage->Store($infile, $public_access, $id);
      $this->logger->error($msg);
    }
    if ($this->chrono) $this->logger->end("Store");
    return($msg);
  }

  // ---------------------------------------------------------
  function Save($infile, $public_access, $id) {
  // ---------------------------------------------------------

    if ($this->chrono) $this->logger->start("Save");
    
    if (!is_bool($public_access)) 
     {
	$public_access  = FALSE;
	$this->logger->warning("Access mode forced to RESTRICTED for ".$infile."].");
     }

    $msg = $this->storage->Save($infile, $public_access, $id);
    $this->logger->error($msg);
    
    if ($this->chrono) $this->logger->end("Save");
    return($msg);
  }

  /**
   * Modification of properties if file
   */
  function Rename($id_file,$newname) {
  // ---------------------------------------------------------

    if ($this->chrono) $this->logger->start("Modify");
    
    if (!is_bool($public_access)) 
     {
	$public_access  = FALSE;
	$this->logger->warning("Access mode forced to RESTRICTED for ".$infile."].");
     }
    if ($newname != "") {
      $oldname=$this->storage->name;
      $msg = $this->storage->Show($id_file, $infos);
      $this->storage->name=$newname;
      $msg = $this->storage->Modify();
      if ($msg =="") {
	$pio=pathinfo($oldname);
	$pin=pathinfo($newname);
	$epio=$pio['extension'];
	if ($epio=="") $epio="nop";
	$epin=$pin['extension'];
	if ($epin=="") $epin="nop";
	if ($epio != $epin) {
	  // need rename physically file
	  $path=pathinfo($infos->path);
	  if (ereg("(.*)/([0-9]+)\.[^\.]*",$infos->path,$reg)) {
	    $newpath=$reg[1]."/".$reg[2].".".$epin;
	    rename($infos->path,$newpath);
	  }
	}
      }
      $this->logger->error($msg);
    }
    
    if ($this->chrono) $this->logger->end("Modify");
    return($msg);
  }

  // ---------------------------------------------------------
  function Stats(&$s) {
  // ---------------------------------------------------------
    if ($this->chrono) $this->logger->start("Stats");
    $this->storage->Stats($s);
    if ($this->chrono) $this->logger->end("Stats");
    return '';
  }

  // ---------------------------------------------------------
  function ListFiles(&$s) {
  // ---------------------------------------------------------
    if ($this->chrono) $this->logger->start("ListFiles");
    $this->storage->ListFiles($s);
    if ($this->chrono) $this->logger->end("ListFiles");
    return '';
  }

  // ---------------------------------------------------------
  function Destroy($id) {
  // ---------------------------------------------------------
    if ($this->chrono) $this->logger->start("Destroy");
    if ($this->use_cache) $this->cache->Delete($id);
    $msg = $this->storage->Destroy($id);
    if ($msg!='') $this->logger->error($msg);
    if ($this->chrono) $this->logger->end("Destroy");
    return $msg;
  }

}
?>