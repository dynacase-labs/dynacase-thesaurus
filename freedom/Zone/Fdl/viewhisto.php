<?php
/**
 * View Document History
 *
 * @author Anakeen 2000 
 * @version $Id: viewhisto.php,v 1.9 2004/01/15 16:32:17 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: viewhisto.php,v 1.9 2004/01/15 16:32:17 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/viewhisto.php,v $
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
function viewhisto(&$action) 
{
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);
  $target = GetHttpVars("target","doc_properties");
  $viewapp = GetHttpVars("viewapp","FDL");
  $viewact = GetHttpVars("viewact","FDL_CARD");
  $target = GetHttpVars("target","doc_properties");
  $comment = GetHttpVars("comment",_("no comment"));

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");

  $doc= new Doc($dbaccess,$docid);
  $action->lay->Set("title",$doc->title);
  $action->lay->Set("target",$target);
  $action->lay->Set("VIEWAPP",$viewapp);
  $action->lay->Set("VIEWACT",$viewact);

  $ldoc = $doc->GetRevisions();

  $trdoc= array();
  while(list($k,$rdoc) = each($ldoc)) {
    $owner = new User("", $rdoc->owner);
    $trdoc[$k]["owner"]= $owner->firstname." ".$owner->lastname;
    $trdoc[$k]["revision"]= $rdoc->revision;
    $trdoc[$k]["state"]= ($rdoc->state=="")?"":_($rdoc->state);
    $trdoc[$k]["COMMENT"]="COMMENT$k";
    $tc = explode("\n",$rdoc->comment);
    $tlc = array();
    $kc=0; // index comment
    foreach ($tc as $vc) {
      if (ereg("(.*)\[(.*)\](.*)",$vc,$reg)) {

	$kc++;
	$tlc[$kc]=array("cdate"=>$reg[1],
			"cauthor"=>$reg[2],
			"ccomment"=>$reg[3]);
      } else {
	$tlc[$kc]["ccomment"].="<BR>".$vc;
	if (! isset($tlc[$kc]["cdate"])) {
	  $tlc[$kc]["cdate"]="";
	  $tlc[$kc]["cauthor"]="";
	}
      }
      
    }
    $action->lay->SetBlockData("COMMENT$k",$tlc);

    $trdoc[$k]["comment"]= nl2br(htmlentities($rdoc->comment));
    $trdoc[$k]["id"]= $rdoc->id;
    $trdoc[$k]["divid"]= $k;

    if ($action->GetParam("CORE_LANG") == "fr_FR") { // date format depend of locale
      setlocale (LC_TIME, "fr_FR");
      $trdoc[$k]["date"]= strftime ("%a %d %b %Y %H:%M",$rdoc->revdate);
    } else {
      $trdoc[$k]["date"]= strftime ("%x<BR>%T",$rdoc->revdate);
    
    
    }
  }

  $action->lay->SetBlockData("TABLEBODY",$trdoc);
  // js : manage icons
  $licon = new Layout($action->GetLayoutFile("manageicon.js"),$action);
  $licon->Set("nbdiv",1);
  $action->parent->AddJsCode($licon->gen());
}

?>
