<?php
$d1=microtime();
include_once("DAV/Class.FdlDav.php");
include_once("Lib.Common.php");



//error_log("dav:   path_info=(".$_SERVER["PATH_INFO"].")");
$_SERVER['PATH_INFO'] = "/".$_GET['filename'];
$_SERVER['SCRIPT_NAME'] = "";

global $action;

error_log("======[ ".$_SERVER['REQUEST_METHOD']." ]=[ ".$_SERVER['PATH_INFO']." ]=======");
whatInit();
$s=new HTTP_WebDAV_Server_Freedom($action->getParam("WEBDAV_DB"));
if ($_SERVER['PHP_AUTH_USER']=="") {
  $path=$_SERVER['PATH_INFO'];
    if (ereg("/vid-([0-9]+)-([0-9]+)-([^/]+)",$path,$reg)) {
      $docid=$reg[1];
      $vid=$reg[2];
      $sid=$reg[3];
      //error_log("dav: -> $docid  $vid $sid");
      $login=$s->getLogin($docid,$vid,$sid);
      //error_log("dav LOGIN: -> $login");
      

    }
 } else {
  $login=$_SERVER['PHP_AUTH_USER'];
 }
if (! $login) {	
  if (((($path == "/")||(strtolower($path) == "/freedav")) && ($_SERVER['REQUEST_METHOD']=="OPTIONS")) ||
      ((($path == "/")||(strtolower($path) == "/freedav")) && ($_SERVER['REQUEST_METHOD']=="PROPFIND"))) {
    // keep without authenticate
  } else {
  //	header('HTTP/1.0 401 Unauthorized');
  	header('HTTP/1.0 403 Forbidden');
  	exit;
  }
 } else {
  whatLogin($login);
 }

$d2=microtime();

$dt=microtime_diff($d1,$d2);

$s->http_auth_realm = "FREEDOM Connection";
$s->db_freedom=$action->getParam("FREEDOM_DB");
$s->racine=$action->getParam("WEBDAV_ROOTID",9);
$s->ServeRequest();
$d2=microtime();
$d=microtime_diff($d1,$d2);

error_log("================ $d $dt=====".$login."===================");

function whatInit() {
  global $action;
  include_once('Class.User.php');
  include_once('Class.Session.php');

  $CoreNull="";
  $core = new Application();
  $core->Set("CORE",$CoreNull);
  $core->session=new Session();
  $action=new Action();
  $action->Set("",$core);

  // i18n
  $lang=$action->Getparam("CORE_LANG");
  setlocale(LC_MESSAGES,$lang);  
  setlocale(LC_MONETARY, $lang);
  setlocale(LC_TIME, $lang);
  bindtextdomain ("what", DEFAULT_PUBDIR."/locale");
  bind_textdomain_codeset("what", 'ISO-8859-15');
  textdomain ("what");
}

function whatLogin($login) {
  global $action;
  include_once('Class.User.php');
  include_once('Class.Session.php');

  if ($login!="") {
    $action->user=new User(); //create user 
    $action->user->setLoginName($login);
  }
}
?>
