<?php
// ---------------------------------------------------------------
// $Id: histo.php,v 1.3 2001/12/21 13:58:35 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Attic/histo.php,v $
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
// $Log: histo.php,v $
// Revision 1.3  2001/12/21 13:58:35  eric
// modif pour incident
//
// Revision 1.2  2001/11/21 13:12:55  eric
// ajout caractéristique creation profil
//
// Revision 1.1  2001/11/21 08:40:34  eric
// ajout historique
//
// Revision 1.4  2001/11/19 18:04:22  eric
// aspect change
//
// Revision 1.3  2001/11/16 18:04:39  eric
// modif de fin de semaine
//
// Revision 1.2  2001/11/15 17:51:50  eric
// structuration des profils
//
// Revision 1.1  2001/11/09 09:41:14  eric
// gestion documentaire
//

// ---------------------------------------------------------------

include_once("FREEDOM/Class.Doc.php");
function histo(&$action) 
{
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);
  $comment = GetHttpVars("comment",_("no comment"));

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");

  $doc= new Doc($dbaccess,$docid);
  $action->lay->Set("title",$doc->title);

  $ldoc = $doc->GetRevisions();

  $trdoc= array();
  while(list($k,$rdoc) = each($ldoc)) {
    $owner = new User("", $rdoc->owner);
    $trdoc[$k]["owner"]= $owner->firstname." ".$owner->lastname;
    $trdoc[$k]["revision"]= $rdoc->revision;
    $trdoc[$k]["comment"]= nl2br($rdoc->comment);
    $trdoc[$k]["id"]= $rdoc->id;
    $trdoc[$k]["divid"]= $k;

    if ($action->GetParam("CORE_LANG") == "fr") { // date format depend of locale
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
