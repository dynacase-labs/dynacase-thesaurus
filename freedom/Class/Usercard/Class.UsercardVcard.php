<?php
// ---------------------------------------------------------------
// $Id: Class.UsercardVcard.php,v 1.1 2002/02/13 14:31:58 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Usercard/Class.UsercardVcard.php,v $
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
// $Log: Class.UsercardVcard.php,v $
// Revision 1.1  2002/02/13 14:31:58  eric
// ajout usercard application
//
// Revision 1.3  2001/07/11 15:37:00  eric
// export pour outlook
//
// Revision 1.2  2001/07/05 11:41:31  eric
// ajout export format vcard
//
// Revision 1.1  2001/06/19 16:16:37  eric
// importation fichier
//
//
// ---------------------------------------------------------------

include_once('./CONTACTS/Class.ContactImport.php');

Class UsercardVcard 
{
  var $import = array(
		      "FN" => "",

		      "N" => "201;202",
		      "N;GIVEN" => "201",
		      "N;FAMILY"=> "202",		
		      "N;MIDDLE" => "",
		      "N;PREFIX" => "",
		      "N;SUFFIX" => "",
		      "SOUND" => "",
		      "BDAY" => "",
		      "NOTE" => "",
		      "TZ" => "",
		      "GEO" => "",
		      "URL" => "215",
		      "PUBKEY" => "",
		      "ORG" => "211",
		      "ORG;NAME" => "211",
		      "ORG;UNIT" => "",
		      "TITLE" => "",
			
		      "ADR;TYPE;WORK" => "",
		      "ADR;TYPE;HOME" => "",
		      "TEL;PREFER" => "",
		      "EMAIL;INTERNET" => "205",
		      "EMAIL;INTERNET;WORK" => "205",
		      "EMAIL;INTERNET;HOME" => "",
			
		      "ADR;WORK" => "0;0;212;214;0;213",
		      "ADR;WORK;STREET" => "212",
		      "ADR;WORK;LOCALITY" => "214", 
		      "ADR;WORK;REGION" => "", 
		      "ADR;WORK;POSTALCODE" => "213",
		      "ADR;WORK;COUNTRYNAME" => "",
		      "EXT" => "",
		      "LABEL" => "",

		      "ADR;HOME" => "0;0;320;325;0;322",
		      "ADR;HOME;STREET" => "320",
		      "ADR;HOME;LOCALITY" => "325",
		      "ADR;HOME;REGION" => "",
		      "ADR;HOME;POSTALCODE" => "322",
		      "ADR;HOME;COUNTRYNAME",
			
		      "TEL;WORK" => "206",
		      "TEL;HOME" => "",
		      "TEL;VOICE" => "",
		      "TEL;FAX" => "210",
		      "TEL;MSG" => "",
		      "TEL;CELL" => "207",
		      "TEL;PAGER" => "",
		      "TEL;BBS" => "",
		      "TEL;MODEM" => "",
		      "TEL;CAR" => "",
		      "TEL;ISDN" => "",
		      "TEL;VIDEO" => "",
		      "EMAIL;WORK" => "",
		      "EMAIL;HOME" => "");
  
  var $mime_type = "text/x-vcard";
  var $ext = "vcf";

  // --------------------------------------------------------------------
  function Open($filename, $mode="r") {
    // Open import/export file : return file descriptor
     $this->fd = fopen($filename,$mode);
     return ($this->fd);
 
  }

  function Close() {
    // Close import file
    if ($this->fd)
      fclose($this->fd);
 
  }
  function ReadCard(&$tattr) 
    {
      // Read a structure of import file : return array ('name', 'value')
    
      $tattr=array();
      $endCardFound = false;
      $beginCardFound = false;
      $line="";

      // search begin of a card : BEGIN:VCARD
      while ( (! feof ($this->fd)) &&
	      (! $beginCardFound) )
	{
	  $line = fgets($this->fd, 4096);
	  $beginCardFound = ereg ("BEGIN:VCARD(.*)", $line);
	}
    

      // search element of a card until : END:VCARD
      while ( (! feof ($this->fd)) &&
	      (! $endCardFound) )
	{
	  $line = fgets($this->fd, 4096);
	  $endCardFound = ereg ("END:VCARD(.*)", $line);
	  if (! $endCardFound)
	    {
	      //line like TEL;WORK:05.61.15.54.54
	      if (ereg ("([A-Z;]*):(.*)", $line, $reg)){
		//		if (isset($this->import[$reg[1]]))
		  $tattr[$reg[1]]=$reg[2];
	      }
	    }
    
	}

      return ( ! feof ($this->fd));
    }  
  // --------------------------------------------------------------------
  function Read(&$tattr) {
    // Read import file : return attribute object
 

    if ($cr = $this ->ReadCard($tbrut))
      {
	
	$tattr=array();
	while(list($k,$v) = each($tbrut)) 
	  {
		
	    if (isset($this->import[$k]) && ($this->import[$k] != ""))
	      {		
	
		// if is int (no regexp)
		if (ereg("^[0-9]*$",$this->import[$k]))
		  {
		    
		    $tattr[$this->import[$k]]=$v;

		  }
		else
		  {
		    // regexp case
		    // example 100;101
		    if (ereg("([0-9]*)([^0-9]*)([0-9]*)[^0-9]*([0-9]*)[^0-9]*([0-9]*)[^0-9]*([0-9]*)[^0-9]*([0-9]*)",
			     $this->import[$k], $reg))
		    {
		      if (ereg("([^".$reg[2]."]*)".$reg[2]."{0,1}([^".$reg[2]."]*)".$reg[2]."{0,1}([^".$reg[2]."]*)".$reg[2]."{0,1}([^".$reg[2]."]*)".$reg[2]."{0,1}([^".$reg[2]."]*)".$reg[2]."{0,1}([^".$reg[2]."]*)", $v , $regv))
			{
			  $tattr[$reg[1]]= $regv[1];
			  $tattr[$reg[3]]= $regv[2];
			  $tattr[$reg[4]]= $regv[3];
			  $tattr[$reg[5]]= $regv[4];
			  $tattr[$reg[6]]= $regv[5];
			  $tattr[$reg[7]]= $regv[6];
			}
		    }
		  }
	      }
	  }
      }
    return ($cr);
  }
  function WriteCard($title,$tattr) 
    {
      // Write a structure in export file
    
      fputs($this->fd,"BEGIN:VCARD\n");
      fputs($this->fd,"FN:".chop($title)."\n");
      reset($this->import);
      while(list($k,$v) = each($this->import))
	  {
	    if ($v != "")
	      {
		if (isset($tattr[$v]))
		  fputs($this->fd,$k.":".$tattr[$v]."\n");
		//		fputs($this->fd,"$k:$v\n");
	      }
	  }
      fputs($this->fd,"END:VCARD\n\n");
    }
}

?>
