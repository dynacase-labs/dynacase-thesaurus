<?php
/**
 * Redirector for generic
 *
 * @author Anakeen 2000 
 * @version $Id: fusers_iuser.php,v 1.1 2004/08/12 10:24:44 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("FDL/Lib.Dir.php");


function fusers_iuser(&$action) 
{
  $bar=uniqid("/tmp/wbar");
  wbar(1,-1,"lancement",$bar);
 
  $cmd = getWshCmd();

  $cmd .= "--bar=$bar --api=usercard_iuser ";

  bgexec(array($cmd),$result,$err);


  redirect($action,"CORE","PROGRESSBAR&bar=$bar");
}

function fusers_ldapinit(&$action) 
{
  $bar=uniqid("/tmp/wbar");
  wbar(1,-1,"lancement",$bar);
 
  $cmd = getWshCmd();

  $cmd .= "--bar=$bar --api=usercard_ldapinit ";
  bgexec(array($cmd),$result,$err);

  redirect($action,"CORE","PROGRESSBAR&bar=$bar");
}
?>
