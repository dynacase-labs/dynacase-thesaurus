<?php
// ---------------------------------------------------------------
// $Id: Class.VaultFile.php,v 1.1 2001/11/16 09:57:01 marc Exp $
// $Source: /home/cvsroot/anakeen/freedom/vault/Class/Class.VaultFile.php,v $
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
// $Log: Class.VaultFile.php,v $
// Revision 1.1  2001/11/16 09:57:01  marc
// V0_0_1 Initial release, see CHANGELOG
//
//
// ---------------------------------------------------------------
include_once("VAULT/Class.VaultDiskStorage.php");
include_once("VAULT/Class.VaultCache.php");
include_once("Class.Log.php");

Class VaultFile {

  function VaultFile($access, $vaultname="Sample", $idf=-1) {


    if (!include_once("VAULT/".$vaultname.".vault")) {
      $this = NULL;
      return;
    }
    if (!isset($chrono)) $this->chrono = FALSE;
    else $this->chrono = $chrono;
    $this->idf      = $idf;
    $this->name     = $vaultname;
    $this->logger = new Log($logfiledir."/vault-".$this->name.".log", "Vault[".$this->name."]", "");
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
	$public_access = FALSE;
	$this->logger->warning("Access mode forced to RESTRICTED for ".$infile."].");
      }
      $msg = $this->storage->Store($infile, $public_access, $id);
      $this->logger->error($msg);
    }
    if ($this->chrono) $this->logger->end("Store");
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
  function Delete($id) {
  // ---------------------------------------------------------
    $this->cache->Delete($id);
    $this->storage->Delete($id);
  }

}
