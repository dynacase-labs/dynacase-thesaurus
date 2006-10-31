<?php
require_once("nusoap.php");

$verbose = true;

function toolhead($verb=true) {
  global $verbose;
  if (!$verb) {
    $verbose = false;
    return;
  } 
  echo "<style>";
  echo "div { border:1px solid black; background-color:#EFEFEF; }";
  echo "pre { padding:1em;border:1px solid black; background-color:#deffcc; }";
  echo "#debugt, #clientt { padding:1em;border:1px solid black; background-color:yellow; }";
  echo "}";
  echo "</style>";
}

function toolinitsoap($wsdl = "http://obeone.tlse.i-cesam.com/freedomWS/", $user = "marc", $pass = "anakeen") {
  if ($user=="" || $pass=="") {
    echo "<h4>No authentication !</h4>";
    exit;
  }
  $c = new soapclient($wsdl);
  $c->setCredentials($user, $pass);
  $err = $c->getError();
  if ($err) {
    echo '<h4>Constructor error</h4><pre>' . $err . '</pre>';
    exit;
  }
  if ($verbose) echo "<h4>Client initialized ($wsdl, $user, $pass) </h4><pre>";

  return $c;

}


function toolresult($c, $r) {
  if ($c->fault) { 
    echo '<h4>Fault</h4><pre>';
    print_r($r);
    echo '</pre>';
  } else {
    // Check for errors
    $err = $c->getError();
    if ($err) {
      // Display the error
      echo '<h4>Error</h4>';
      echo '<pre>' . $err . '</pre>';
      echo '<h4>Client reponse</h4><pre id="rep">' . $c->response . '</pre>';
    } else {
      // Display the result
      echo '<fieldset id="rep"><legend>Result</legend>';
      print_r($r);
      echo '</fieldset>';

      echo '<fieldset id="rep"><legend onclick="document.getElementById(\'clientt\').style.display=\'block\'">Infos</legend>';
      echo '<pre style="display:none" id="clientt">';
      print_r($c->response);
      echo '</pre>';
      echo '</fieldset>';
    }
  }
  echo '<fieldset id="rep"><legend onclick="document.getElementById(\'debugt\').style.display=\'block\'">Debug</legend>';
  echo '<pre style="display:none" id="debugt">';
  print_r($c->getDebug());
  echo '</pre>';
  echo '</fieldset>';
}

?>
