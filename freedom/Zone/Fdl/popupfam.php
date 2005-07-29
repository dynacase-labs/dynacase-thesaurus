<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: popupfam.php,v 1.14 2005/07/29 16:20:09 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: popupfam.php,v 1.14 2005/07/29 16:20:09 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/popupfam.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2001
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

include_once("FDL/Class.Doc.php");
// -----------------------------------
function popupfam(&$action,&$tsubmenu) {
  // -----------------------------------
  // ------------------------------
  // define accessibility
  $docid = GetHttpVars("id");
  $abstract = (GetHttpVars("abstract",'N') == "Y");

  $action->lay->Set("SEP",false);
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new_Doc($dbaccess, $docid);

  if ($doc->doctype=="C") return; // not for familly


  $kdiv=1; // only one division

  $action->lay->Set("id", $docid);

  include_once("FDL/popup_util.php");



  $lmenu = $doc->GetMenuAttributes();
  if (! $lmenu) return;

  $tmenu = array();
  $km=0;

  foreach($lmenu as $k=>$v) {
    
    $confirm=false;
    $control=false;


    if ($v->link[0] == '?') { 
      $v->link=substr($v->link,1);
      $confirm=true;
    }
    if ($v->link[0] == 'C') { 
      $v->link=substr($v->link,1);
      $control=true;
    }
    if (ereg('\[(.*)\](.*)', $v->link, $reg)) {      
      $v->link=$reg[2];
      $tlink[$k]["target"] = $reg[1];
    } else {
      $tlink[$k]["target"] = $v->id;
    }
    $tlink[$k]["idlink"] = $v->id;
    $tlink[$k]["descr"] = $v->labelText;
    $tlink[$k]["url"] = addslashes($doc->urlWhatEncode($v->link));
    $tlink[$k]["confirm"]=$confirm?"true":"false";
    $tlink[$k]["control"]=$control;
    $tlink[$k]["tconfirm"]=sprintf(_("Sure %s ?"),addslashes($v->labelText));
    $tlink[$k]["visibility"]=MENU_ACTIVE;
    if ($v->precond != "") {
      if (substr($v->precond,0,2)=="::") {
	if (ereg("::([^\(]+)\(([^\)]*)\)",$v->precond, $reg)) {
	  $method=$reg[1];
	  if (method_exists($doc,$method)) {
	    $tiargs=array();
	    $res=call_user_method_array($method,$doc,$tiargs);

	    $tlink[$k]["visibility"]=$res;
	  }
	}
      }
    }
    $tmenu[$km++] = $v->id;
    popupAddItem('popupcard',  $v->id); 
  }
  if (count($tmenu) ==  0) return;
  // ---------------------------
  // definition of popup menu

  // ---------------------------
  // definition of sub popup menu);  
  foreach($lmenu as $k=>$v) {   
    $sm=$v->getOption("submenu");
    if ($sm != "") {
      $smid=base64_encode($sm);
      $tsubmenu[$smid]=array("idmenu"=>$smid,
			   "labelmenu"=>$sm);
      popupSubMenu('popupcard',$v->id,$smid);
    }
  }


  while(list($k,$v) = each($tmenu)) {

    if ($tlink[$v]["visibility"]==MENU_INVISIBLE) {
      Popupinvisible('popupcard',$kdiv,$v);
    } else {
      if ($tlink[$v]["url"] != "") {
	if ($tlink[$v]["visibility"]==MENU_INACTIVE) {
	  if ($tlink[$v]["control"])  PopupCtrlInactive('popupcard',$kdiv,$v);     
	  else  PopupInactive('popupcard',$kdiv,$v);
	} else {
	  if ($tlink[$v]["control"])  PopupCtrlActive('popupcard',$kdiv,$v);     
	  else  Popupactive('popupcard',$kdiv,$v);
	}
	
      } else PopupInactive('popupcard',$kdiv,$v);
  }
  }

  
  $noctrlkey=($action->getParam("FDL_CTRLKEY","yes")=="no");
  if ($noctrlkey) popupNoCtrlKey();            

  $action->lay->SetBlockData("ADDLINK",$tlink);
  $action->lay->Set("SEP",true);// to see separatot
}