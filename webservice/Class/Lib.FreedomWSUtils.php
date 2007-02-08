<?php
/**
 * Freedom Soap library : utilities
 *
 * @author Anakeen 2006
 * @version $Id: Lib.FreedomWSUtils.php,v 1.2 2007/02/08 08:22:45 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM-WEBSERVICES
 */
/**
 */

/**
 * Log message
 * @param string $msg  message
 * @param string $lvl  log level (Debug|Info|Warning|Error)
 * @param string $file file name (use in Debug level level)
 * @param string $line file line (use in Debug level level)
 * @return void
 */
function fwsLog($msg="", $lvl="I", $file="", $line="") {
  static  $logf = false;

  $debuglevel = "D"; // strtoupper(substr($_SERVER["FREEDOMWS_DEBUG"],0,1));
  $debug = ($debuglevel=="D" ? true : false );

  define_syslog_variables();
  if (!$logf) openlog("FreedomWS", LOG_PID|LOG_PERROR,LOG_USER);

  switch (strtoupper($lvl)) {
  case "W" : $logp = LOG_WARNING; break;
  case "E" : $logp = LOG_ERR; break;
  case "D" : $logp = LOG_DEBUG; break;
  default  : $logp = LOG_INFO;
  }

  if ($logp==LOG_DEBUG && !$debug ) return;
  
  $mlog = "";
  if ($debuglevel=="D") $mlog .= "[".basename($file).":".$line."] ";
  $mlog .=  $msg;
  syslog($logp, $mlog);

  return;
}

/**
 * Unserialize Freedom document array attributes
 * @param string $att  attribute
 * @return array
 */
function fwsVal2Array($v) {
   return explode("\n", str_replace("\r","",$v));
}

/**
 * initialize Freedom context
 * @return string freedom database coordinates 
 */
function _initFreedom() {
  global $action;
  global $freedomdb;

  if (! $freedomdb) {
    $CoreNull="";
    $core = new Application();
    $core->Set("CORE",$CoreNull);
    $core->session=new Session();
    $action = new Action();
    $action->Set("",$core);
    $freedomdb=$action->getParam("FREEDOM_DB");
  }
  return $freedomdb;
}
/**
 * initialize User context 
 * @return int user id number
 */
function _getUserFid() {
  global $_SERVER;
  global $action;
  
  if (! $action) _initFreedom();

  $action->user = new User(); //create user as admin  
  $action->user->setLoginName($_SERVER["PHP_AUTH_USER"]);

  return $action->user->id;
}
?>
