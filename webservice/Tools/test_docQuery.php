<?php

require_once('/usr/share/nusoap/nusoap.php');
require_once('Lib.Http.php');

echo "<style>";
echo "div { border:1px solid black; background-color:#EFEFEF; }";
echo "pre#rep { padding:1em;border:1px solid black; background-color:yellow; }";
echo "}";
echo "</style>";

$wsdl = "http://obeone.tlse.i-cesam.com/freedomWS/";
$user = "marc";
$pass = "anakeen";

# echo "<h4>Wsdl=[$wsdl] User=[$user] Pass=[$pass]</h4>";

if ($user=="" || $pass=="") {
  echo "<h4>No authentication !</h4>";
  exit;
}
  

$c = new soapclient($wsdl);
$c->setCredentials($user, $pass);

$err = $c->getError();
if ($err) echo '<h4>Constructor error</h4><pre>' . $err . '</pre>';

// $param = array('docid' => 1323, 'docrev' => '1');
$param = array("start"=>0, "slice" => 20, "famid" => "USER_PORTAL", "allrev" => false, "trash" => false, "orderby" => "title" );
$r = $c->call('docQuery', $param);
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
                echo '<h4>Result</h4><pre id="rep">';
                print_r($r);
                echo '</pre>';
                echo '<h4>Client reponse</h4><pre id="rep">' . $c->response . '</pre>';
        }
}
echo "<h4>Debug info</h4>";
echo '<pre id="rep">';
print_r($c->getDebug());
echo "</pre>";

exit;


?>
