<?php
/**
 * Retrieve and store file in Vault
 *
 * @author Anakeen 2004
 * @version $Id: Class.VaultFile.php,v 1.9 2005/09/27 13:33:52 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package VAULT
 */
 /**
 */

include_once("VAULT/Class.VaultDiskStorage.php");
include_once("VAULT/Class.VaultCache.php");
include_once("Class.Log.php");

Class VaultFile {

  function __construct($access, $vaultname="Sample", $idf=-1) {

    if (!include("VAULT/".$vaultname.".vault")) {
      return;
    }
    if (!isset($chrono)) $this->chrono = FALSE;
    else $this->chrono = $chrono;
    $this->idf      = $idf;
    $this->name     = $vaultname;
    $this->logger = new Log("", "vault", $this->name);
    if ($this->chrono)  $this->logger->warning("Running with chrono !!!!");
    $this->dbaccess = $access;
    $this->u_owner  = $u_owner;
    $this->g_owner  = $g_owner;
    $this->f_mode   = $f_mode;
    $this->d_mode   = $d_mode;
    $this->type     = $vault_type;
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
  function Save($infile, $public_access, $id, $pathname) {
  // ---------------------------------------------------------

    if ($this->chrono) $this->logger->start("Save");
    
    if (!is_bool($public_access)) 
     {
	$public_access  = FALSE;
	$this->logger->warning("Access mode forced to RESTRICTED for ".$infile."].");
     }
    $msg = $this->storage->Save($infile, $public_access, $id, $pathname);
    $this->logger->error($msg);
    
    if ($this->chrono) $this->logger->end("Save");
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