<?php
// ---------------------------------------------------------------
// $Id: deldirfile.php,v 1.1 2001/11/09 09:41:13 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Attic/deldirfile.php,v $
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
// $Log: deldirfile.php,v $
// Revision 1.1  2001/11/09 09:41:13  eric
// gestion documentaire
//
// ---------------------------------------------------------------

include_once("FREEDOM/Class.Doc.php");
include_once("FREEDOM/Class.QueryDir.php");
include_once("FREEDOM/Class.QueryDirV.php");
include_once("FREEDOM/freedom_util.php");  



// -----------------------------------
function deldirfile(&$action) {
  // -----------------------------------


  //print_r($HTTP_POST_VARS);

  // Get all the params      
  $dirid=GetHttpVars("dirid");
  $docid=GetHttpVars("docid");

  //  print "deldirfile :: dirid:$dirid , docid:$docid";


  $dbaccess = $action->GetParam("FREEDOM_DB");


  $dir = new Doc($dbaccess,$dirid);// use initial id for directories
  $dirid=$dir->initid;

  $qfv = new QueryDirV($dbaccess, $dirid);

  if (!($qfv->isAffected())) $action->exitError(sprintf(_("cannot delete link : link not found for doc %d in directory %d"),$docid, $dirid));

  $qids = $qfv->getQids($docid);
  // search query
  $qf = new QueryDir($dbaccess, $qids[0]);
  if (!($qf->isAffected())) $action->exitError(sprintf(_("cannot delete link : initial query not found for doc %d in directory %d"),$docid, $dirid));
  

  if ($qf->qtype != "S") $action->exitError(sprintf(_("cannot delete link for doc %d in directory %d : the document comes from a user query. Delete initial query if you want delete this document"),$docid, $dirid));
  $qf->Delete();

  
  $qf->RefreshDir($dirid);

  
  
  redirect($action,GetHttpVars("app"),"FREEDOM_LIST&dirid=$dirid");
}




?>
