<?php

include "Class.FdlServices.php";

$s= new SoapServer(null, array('uri'=>'http://freedom/services'));

$s->setClass("FdlServices",$_SERVER["PHP_AUTH_USER"]);

if ($_SERVER["REQUEST_METHOD"]=="POST") {
  $s->handle();
 } else {
  header("Content-Type: text/plain");
  echo "fonctions:";
  foreach ($s->getFunctions() as $f) {
    echo "$f\n";
  }
 }
?>