<?php
ini_set("include_path", ".:/usr/share/what:/usr/share/what/WHAT:/usr/share/pear");
include_once("HTTP/WebDAV/Server/Freedomsystem.php");

$s=new HTTP_WebDAV_Server_Filesystem();

//error_log("dav: method=(".$_SERVER['REQUEST_METHOD'].")");
//error_log("dav:   filename=(".$_GET['filename'].")");

//error_log("dav:   path_info=(".$_SERVER["PATH_INFO"].")");
$_SERVER['PATH_INFO'] = "/".$_GET['filename'];
//error_log("dav:     `-> path_info=(".$_SERVER["PATH_INFO"].")");

#error_log("dav:   request_uri=(".$_SERVER['REQUEST_URI'].")");
#$_SERVER['REQUEST_URI'] = "/".$_GET['filename'];
#error_log("dav:     `-> request_uri=(".$_SERVER['REQUEST_URI'].")");

$s->ServeRequest("/var/www/html/");


?>
