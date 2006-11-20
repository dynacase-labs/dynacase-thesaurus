<?php
ini_set("include_path", ".:/usr/share/what:/usr/share/what/WHAT:/usr/share/pear");
$d1=microtime();
include_once("DAV/Class.FdlDav.php");
include_once("Lib.Common.php");

$s=new HTTP_WebDAV_Server_Freedom();

//error_log("======[ ".$_SERVER['REQUEST_METHOD']." ]========");
//error_log("dav:   filename=(".$_GET['filename'].")");

//error_log("dav:   path_info=(".$_SERVER["PATH_INFO"].")");
$_SERVER['PATH_INFO'] = "/".$_GET['filename'];
$_SERVER['SCRIPT_NAME'] = "";
//error_log("dav:     `-> path_info=(".$_SERVER["PATH_INFO"].")");

#error_log("dav:   request_uri=(".$_SERVER['REQUEST_URI'].")");
#$_SERVER['REQUEST_URI'] = "/".$_GET['filename'];
#error_log("dav:     `-> request_uri=(".$_SERVER['REQUEST_URI'].")");

global $action;

error_log("======[ ".$_SERVER['REQUEST_METHOD']." ]=[ ".$_SERVER['PATH_INFO']." ]=======");

if ($_SERVER['PHP_AUTH_USER']=="") {
  $path=$_SERVER['PATH_INFO'];
    if (ereg("/vid-([0-9]+)-([0-9]+)-([^/]+)",$path,$reg)) {
      $docid=$reg[1];
      $vid=$reg[2];
      $sid=$reg[3];
      error_log("dav: -> $docid  $vid $sid");
      $login=$s->getLogin($docid,$vid,$sid);
      error_log("dav LOGIN: -> $login");
      

    }
 } else {
  $login=$_SERVER['PHP_AUTH_USER'];
 }
if (! $login) {	
  if (((($path == "/")||($path == "/freedav")) && ($_SERVER['REQUEST_METHOD']=="OPTIONS")) ||
      ((($path == "/")||($path == "/freedav")) && ($_SERVER['REQUEST_METHOD']=="PROPFIND"))) {
    // keep without authenticate
  } else {
  //	header('HTTP/1.0 401 Unauthorized');
  	header('HTTP/1.0 403 Forbidden');
  	exit;
  }
 } else {
  whatInit($login);
 }

$d2=microtime();

$dt=microtime_diff($d1,$d2);
$s->http_auth_realm = "FREEDOM Connection";
$s->ServeRequest();
$d2=microtime();
$d=microtime_diff($d1,$d2);

error_log("================ $d $dt=====".$login."===================");

function whatInit($login) {
  global $action;
include_once('Class.User.php');
 include_once('Class.Session.php');

    $CoreNull="";
    $core = new Application();
    $core->Set("CORE",$CoreNull);
    $core->session=new Session();
    $action=new Action();
    $action->Set("",$core);
    $action->user=new User(); //create user as admin
    $action->user->setLoginName($login);
    //$action->user->setLoginName("eric.brison");
}

?>
