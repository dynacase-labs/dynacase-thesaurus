<?php
// ---------------------------------------------------------------
// $Id: freedom_bgimport.php,v 1.2 2002/10/08 10:28:17 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_bgimport.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2002
// O*O  Anakeen development team
//  O   dev@anakeen.com
// ---------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------




include_once("FDL/Class.Dir.php");


// -----------------------------------
function freedom_bgimport(&$action) {
  // -----------------------------------
  global $HTTP_POST_FILES;

  // Get all the params      

  $dirid=GetHttpVars("dirid");
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $policy = GetHttpVars("policy","keep"); 
  $analyze = (GetHttpVars("analyze","N")=="Y"); 
  $to = GetHttpVars("to"); 



  if (isset($HTTP_POST_FILES["file"]))    
    {
      // importation 
      $file = $HTTP_POST_FILES["file"]["tmp_name"];
      $filename = $HTTP_POST_FILES["file"]["name"];

      
    } 

  
  $wsh = "nice -n +10 ".$action->GetParam("CORE_PUBDIR")."/wsh.php";

  $cmd[] = "cp $file $file.1";

  

  $cmd[] = "$wsh --userid={$action->user->id} --api=freedom_import --htmlmode=Y --dirid=$dirid --file=$file.1 >$file.2 ";

  
  $subject=sprintf(_("result of import  %s"), $filename);
  $cmd[] = "metasend  -b -S 4000000  -F 'freedom' -t '$to' -s \"$subject\"  -m 'text/html' -e 'quoted-printable' -f  $file.2";
  $cmd[]="/bin/rm -f $file.?";

  $scmd="(";
  $scmd.=implode(";",$cmd);
  
  
  $scmd .= ") > /dev/null &";


  session_write_close(); // necessary to close if not background cmd 
  exec($scmd, $result, $err);  
  @session_start();


  if ($err == 0) 
    $action->lay->set("text", sprintf(_("Import %s is in progress. When update will be finished an email to &lt;%s&gt; will be sended with result rapport"), $filename , $to));
  else
    $action->lay->set("text", sprintf(_("update of %s catalogue has failed,"), $filename ));
		      





}




?>
