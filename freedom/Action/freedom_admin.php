<?php
// ---------------------------------------------------------------
// $Id: freedom_admin.php,v 1.2 2001/11/15 17:51:50 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Attic/freedom_admin.php,v $
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
// $Log: freedom_admin.php,v $
// Revision 1.2  2001/11/15 17:51:50  eric
// structuration des profils
//
// Revision 1.1  2001/11/09 09:41:14  eric
// gestion documentaire
//
// Revision 1.5  2001/10/10 16:01:31  eric
// modif pour les droits d'accès
//
// Revision 1.4  2001/07/11 15:59:39  eric
// gestion erreur ldap
//
// Revision 1.3  2001/06/22 09:46:12  eric
// support attribut multimédia
//
// Revision 1.2  2001/06/19 16:08:17  eric
// correction pour type image
//
// Revision 1.1  2001/06/13 14:39:53  eric
// Freedom address book
//
// ---------------------------------------------------------------
include_once("FREEDOM/Class.Doc.php");
include_once("FREEDOM/Class.DocAttr.php");
include_once("FREEDOM/Class.DocValue.php");
include_once("FREEDOM/Class.FreedomLdap.php");
// -----------------------------------
function freedom_admin(&$action) {
// -----------------------------------

  // Get all the params      
  $id=GetHttpVars("id");
  // Set Css
  $cssfile=$action->GetLayoutFile("freedom.css");
  $csslay = new Layout($cssfile,$action);
  $action->parent->AddCssCode($csslay->gen());


    $action->lay->Set("CR",".");
    $action->lay->Set("CRKO",".");
}


function freedom_updatetitle(&$action)
{
  $dbaccess = $action->GetParam("FREEDOM_DB");
  // Set Css
  $cssfile=$action->GetLayoutFile("freedom.css");
  $csslay = new Layout($cssfile,$action);
  $action->parent->AddCssCode($csslay->gen());
  $bdfreedom = newDoc($dbaccess);
  $bdfreedom->UpdateTitles();

  $action->lay->Set("CR",$action->text("updatetitle")."OK");
  $action->lay->Set("CRKO","");

  
}





function freedom_updateldap(&$action)
{

  $dbaccess = $action->GetParam("FREEDOM_DB");
  // Set Css
  $cssfile=$action->GetLayoutFile("freedom.css");
  $csslay = new Layout($cssfile,$action);
  $action->parent->AddCssCode($csslay->gen());
  $query = new QueryDb($dbaccess,"Freedom");

      
    
  $table1 = $query->Query();

     
  if ($query->nb > 0)
    {
      $oldap=new FreedomLdap($action);
      $bdfreedomattr = new DocAttr($dbaccess);
      $query_val = new QueryDb($dbaccess,"DocValue");
      $infocr="<ol>";
      $infocrko="<ol>";
      $nb_update=0;
      $nb_updateko=0;

      // for each freedom card : update ldap values
      while(list($k,$v) = each($table1)) 
	{
	  if ($table1[$k]->visibility != "N")
	    {
	      

	      $err = $oldap->update($table1[$k]->id);

	      if ($err == "errldapconnect")
		{
		  $infocrko .= $action->text($err);
		  break;
		}
	  
		

	      if ($err == "") {
		$infocr .= "<li>".$table1[$k]->title."</li>";
		$nb_update++;
	      }
	      else {
		$infocrko .= "<li>".$table1[$k]->title."</li>";
		$nb_updateko++;
	      }
	    } 
	}
      $infocr .= "</ol>";
      $infocrko .= "</ol>";
	  
    }
      
  $action->lay->Set("CR",$nb_update." ".$action->text("updateldapok").$infocr);
  $action->lay->Set("CRKO",$nb_updateko." ".$action->text("updateldapko").$infocrko);
}
?>
