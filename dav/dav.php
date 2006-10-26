<?php
ini_set("include_path", ".:/usr/share/what:/usr/share/what/WHAT:/usr/share/pear");
$d1=microtime();
include_once("DAV/Class.Dav.php");

define("UPDTCOLOR",'[1;32;40m');
define("STOPCOLOR",'[0m');
$s=new HTTP_WebDAV_Server_Filesystem();

error_log("====== ".$_SERVER['REQUEST_METHOD']." ========");
//error_log("dav:   filename=(".$_GET['filename'].")");

//error_log("dav:   path_info=(".$_SERVER["PATH_INFO"].")");
$_SERVER['PATH_INFO'] = "/".$_GET['filename'];
//error_log("dav:     `-> path_info=(".$_SERVER["PATH_INFO"].")");

#error_log("dav:   request_uri=(".$_SERVER['REQUEST_URI'].")");
#$_SERVER['REQUEST_URI'] = "/".$_GET['filename'];
#error_log("dav:     `-> request_uri=(".$_SERVER['REQUEST_URI'].")");

global $action;
whatInit();

$d2=microtime();

$dt=microtime_diff($d1,$d2);
$s->http_auth_realm = "DAV Connection";
$s->ServeRequest("/var/www/html/");
$d2=microtime();
$d=microtime_diff($d1,$d2);

error_log("================ $d $dt=====".$_SERVER['PHP_AUTH_USER']."===================");

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
    $action->user=new User(); //create user as admin
    $action->user->setLoginName($_SERVER['PHP_AUTH_USER']);
    //$action->user->setLoginName("eric.brison");
}

?>
