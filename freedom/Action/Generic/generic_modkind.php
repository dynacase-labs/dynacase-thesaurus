<?php
/**
 * Modify item os enumerate attributes
 *
 * @author Anakeen 2000 
 * @version $Id: generic_modkind.php,v 1.5 2005/12/02 11:03:39 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");
include_once("FDL/Lib.Attr.php");
include_once("GENERIC/generic_util.php"); 

// -----------------------------------
function generic_modkind(&$action) {
  // -----------------------------------

  
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $aid    = GetHttpVars("aid");    // attribute id
  $famid  = GetHttpVars("fid");    // family id
  $tlevel = GetHttpVars("alevel"); // levels
  $tref   = GetHttpVars("aref");   // references
  $tlabel = GetHttpVars("alabel"); // label

  $tsref=array();
  $ref="";$ple = 1;
  while (list($k, $v) = each($tref)) {
    $le= intval($tlevel[$k]);
    if ($le == 1) $ref=''; 
    else if ($ple < $le) {
      // add level ref index
      $ref = $ref  . $tref[$k-1].'.';
    } else  if ($ple > $le) {
      // suppress one or more level ref index
      for ($l=0;$l<$ple-$le;$l++)  $ref=substr($ref,0,strrpos($ref,'.')-1);
    }
    $ple = $le;
   

    $tsenum[$k] = $ref.$v."|".$tlabel[$k];
  }

  $attr = new DocAttr($dbaccess, array($famid,$aid));
  if ($attr->isAffected()) {
  
    if (ereg("\[([a-z]+)\](.*)",$attr->phpfunc, $reg)) {	 
      $funcformat=$reg[1];
    } else {	  
      $funcformat="";
    }
    $attr->phpfunc = stripslashes(implode(",",$tsenum));
    if ($funcformat != "") $attr->phpfunc="[$funcformat]".$attr->phpfunc;
    $attr->modify();

    refreshPhpPgDoc($dbaccess, $famid);
  }
		      
  $fdoc=new_doc($dbaccess,$famid);
  $a = $fdoc->getAttribute($aid);
  if ($a) { 
    $enum=$a->getenum();
    foreach ($enum as $kk=>$ki) {
	$tvkind[]=array("ktitle" => strstr($ki, '/')?strstr($ki, '/'):$ki,
			"level" =>  substr_count($kk, '.')*20,
			"kid" => $kk);
	
      }


    $action->lay->SetBlockData("vkind", $tvkind);
    
  }
  
  $action->lay->Set("desc", sprintf(_("Modification for attribute %s for family %s"),
				    $a->labelText,
				    $fdoc->title));

}


?>
