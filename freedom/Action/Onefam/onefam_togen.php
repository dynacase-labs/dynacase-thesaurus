<?php
// ---------------------------------------------------------------
// $Id: onefam_togen.php,v 1.1 2002/08/28 09:39:32 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Onefam/onefam_togen.php,v $
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
include_once("FDL/Lib.Dir.php");


function onefam_togen(&$action) 
{
 
  $famid = GetHttpVars("famid",0); 
  
  if ($famid == 0) $action->exitError(_("Family is not instanciate"));

				     
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new Doc ($dbaccess, $famid);
  if (! $doc->isAffected()) $action->exitError(sprintf(_("Family (#%d) is not referenced"),$famid));
  $action->Register("DEFAULT_FAMILY", $famid);
  $action->Register("DEFAULT_FLD", $doc->dfldid);

  redirect($action,"GENERIC", "");
}

?>
