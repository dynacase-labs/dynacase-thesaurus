<?php
// ---------------------------------------------------------------
// $Id: Class.DocFile.php,v 1.9 2003/03/11 17:11:32 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Class.DocFile.php,v $
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

$CLASS_DOCFILE_PHP = '$Id: Class.DocFile.php,v 1.9 2003/03/11 17:11:32 eric Exp $';


include_once("FDL/Class.PDoc.php");




Class DocFile extends PDoc
{
    
  var $defDoctype='F';
  var $defClassname='DocFile';


  // suppress no numeric characters
  function suppressNotNum($s) {  
    $i=0;
    while ( ($i<strlen($s)) && ($s[$i]>='0') && ($s[$i]<='9'))  $i++;
    return substr($s,0,$i);
  }


}

?>