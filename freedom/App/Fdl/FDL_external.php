<?php

include_once("FDL/Class.Dir.php");




function vault_filename($th, $fileid) {


  if (ereg ("(.*)\|(.*)", $fileid, $reg)) {	 
    // reg[1] is mime type
      $vf = new VaultFile($th->dbaccess, "FREEDOM");
    if ($vf -> Show ($reg[2], $info) == "") $fname = $info->name;
    else $fname=sprintf(_("file %d"),$th->initid);
  } else {
    $fname=sprintf(_("file %d"),$th->initid);
  }

  return array($fname);
}


?>
