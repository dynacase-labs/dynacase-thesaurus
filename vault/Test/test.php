<?php
include_once("VAULT/Class.VaultFile.php");

$CORE_LOGLEVEL = "DWEFI";
global $CORE_LOGLEVEL;


function _($txt) {
  return $txt;
}
$dbaccess = "host=localhost user=anakeen port=5432 dbname=vault";

$v = new VaultFile($dbaccess);

$msg = $v->Store("test.php", 1, $id);
echo "Stockage id=".$id." msg=[$msg]\n";

//$msg = $v->Store("/tmp/vault.log", 0, $id);
//echo "Stockage id=".$id." msg=[$msg]\n";

//for ($i=0; $i<100; $i++) {
//  $msg = $v->Store("/tmp/freedom/vault.log", 0, $id);
//  echo "Stockage id=".$id." msg=[$msg]\n";
//}


//$msg = $v->Store("/tmp/freedom", 0, $id);
//echo "Stockage id=".$id." msg=[$msg]\n";


//$v->Stats($s);
//print_r($s);

/*  $c = $v->ListFiles($l); */
/*  echo "Nombre de fichier : ".$c."\n"; */
/*  print_r($l); */

/*  echo " ------------------------- test show -----------------------\n"; */
/*  $msg = $v->Show(12, $infos); */
/*  echo "Info sur 12 msg=[$msg]\n"; */
/*  echo "<".__FILE__.":".__LINE__.">";  */
/*  print_r($infos); */
/*  $msg = $v->Show(29, $infos); */
/*  echo "Info sur 12 msg=[$msg]\n"; */
/*  echo "<".__FILE__.":".__LINE__.">";  */
/*  print_r($infos); */

/*  echo " ------------------------- test Retrieve -----------------------\n"; */
/*  $msg = $v->Retrieve(12, $infos); */
/*  echo "Retrieve sur 12 msg=[$msg]\n"; */
/*  echo "<".__FILE__.":".__LINE__.">";  */
/*  print_r($infos); */
/*  unset($infos); */
/*  $msg = $v->Retrieve(121, $infos); */
/*  echo "Retrieve sur 121 msg=[$msg]\n"; */
/*  echo "<".__FILE__.":".__LINE__.">";  */
/*  print_r($infos);   */
/*  $msg = $v->Retrieve(13, $infos); */
/*  echo "Retrieve sur 13 msg=[$msg]\n"; */
/*  echo "<".__FILE__.":".__LINE__.">";  */
/*  print_r($infos);   */
?>


