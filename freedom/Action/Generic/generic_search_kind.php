<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: generic_search_kind.php,v 1.7 2003/08/18 15:47:03 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: generic_search_kind.php,v 1.7 2003/08/18 15:47:03 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_search_kind.php,v $
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


include_once("FDL/Class.DocSearch.php");
include_once("FDL/freedom_util.php");  
include_once("GENERIC/generic_util.php");  





// -----------------------------------
function generic_search_kind(&$action) {
  // -----------------------------------
   

  // Get all the params      
  $kid=GetHttpVars("kid"); // kind id to search
  $aid=GetHttpVars("aid"); // attribute to search
  $dirid=GetHttpVars("catg"); // folder or research to search


  
  $dbaccess = $action->GetParam("FREEDOM_DB");


  $famid = getDefFam($action);
  $fdoc = new Doc($dbaccess, $famid);

  $attr = $fdoc->getAttribute($aid);
  $enum=$attr->getEnum();
  $kindname=$enum[$kid];

  $dir = new Doc($dbaccess, $dirid);

  $sdoc = createDoc($dbaccess,5); //new DocSearch($dbaccess);
  $sdoc->doctype = 'T';// it is a temporary document (will be delete after)
  $sdoc->title = sprintf(_("search %s"),$keyword);
  if (($dirid == 0) || ($dir->id == getDefFld($action))) $sdoc->title = sprintf(_("search %s is %s"),
							     $attr->labelText,$kindname );
  else $sdoc->title = sprintf(_("search %s is %s in %s"),
			      $attr->labelText,$kindname,$dir->title );

  $sdoc->Add();
  
  $searchquery="";
  $sdirid = 0;
  if ($dir->defDoctype == 'S') { // case of search in search doc
    $sdirid = $dir->id;
  } else { // case of search in folder
    if ($dir->id != getDefFld($action))
      $sdirid = $dirid;

  }

  if (strrpos($kid,'.') !== false)   $kid = substr($kid,strrpos($kid,'.')+1); // last reference





  $sqlfilter[]= "locked != -1";
  //  $sqlfilter[]= "doctype='F'";
  $sqlfilter[]= "usefor = 'N'";


  // searches for all fathers kind
  $a = $fdoc->getAttribute($aid);
  $enum = $a->getEnum();
  $tkids=array();;
  while (list($k, $v) = each($enum)) {
    if (in_array($kid,explode(".",$k))) {
      $tkids[] = substr($k,strrpos(".".$k,'.'));
    }
  }
  if ($a->type == "enum") {
    if ($a->repeat) {
      $sqlfilter[] = "in_textlist($aid,'".
	implode("') or in_textlist($aid,'",$tkids)."')";
    } else {
      $sqlfilter[] = "$aid='".
	implode("' or $aid='",$tkids)."'";    
    }
  }

  $query=getSqlSearchDoc($dbaccess, 
			 $sdirid,  
			 $famid, 
			 $sqlfilter);

  $sdoc->AddQuery($query);

  redirect($action,GetHttpVars("app"),"GENERIC_LIST&dirid=".$sdoc->id."&catg=".$dirid);
  
  
}


?>