<?php
// ---------------------------------------------------------------
// $Id: Class.UsercardVcard.php,v 1.14 2003/05/12 12:15:27 eric Exp $
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
// 59 Temple Place, Suite 330, Boston, MA 0US_SOCIETY1-1307 USA
// ---------------------------------------------------------------



Class UsercardVcard 
{
  var $import = array(
		      "FN" => "",

		      "N" => "US_LNAME;US_FNAME",
		      "N;GIVEN" => "US_FNAME",
		      "N;FAMILY"=> "US_LNAME",		
		      "N;MIDDLE" => "",
		      "N;PREFIX" => "",
		      "N;SUFFIX" => "",
		      "SOUND" => "",
		      "BDAY" => "",
		      "NOTE" => "",
		      "TZ" => "",
		      "GEO" => "",
		      "URL" => "US_WORKWEB",
		      "URL;WORK" => "US_WORKWEB",
		      "PUBKEY" => "",
		      "ORG" => "US_SOCIETY;US_UNIT",
		      "ORG;NAME" => "US_SOCIETY",
		      "ORG;UNIT" => "",
		      "TITLE" => "US_TYPE",
			
		      "ADR;TYPE;WORK" => "",
		      "ADR;TYPE;HOME" => "",
		      "TEL;PREFER" => "",
		      "EMAIL;INTERNET" => "US_MAIL",
		      "EMAIL;INTERNET;WORK" => "US_MAIL",
		      "EMAIL;PREF;INTERNET" => "US_MAIL",
		      "EMAIL;INTERNET;HOME" => "",
			
		      "ADR;WORK" => "0;0;US_WORKADDR;US_WORKTOWN;0;US_WORKPOSTALCODE;US_COUNTRY",
		      "ADR;WORK;STREET" => "US_WORKADDR",
		      "ADR;WORK;LOCALITY" => "US_WORKTOWN", 
		      "ADR;WORK;REGION" => "", 
		      "ADR;WORK;POSTALCODE" => "US_WORKPOSTALCODE",
		      "ADR;WORK;COUNTRYNAME" => "US_COUNTRY",
		      "EXT" => "",
		      "LABEL" => "",

		      "ADR;HOME" => "0;0;320;325;0;322",
		      "ADR;HOME;STREET" => "320",
		      "ADR;HOME;LOCALITY" => "325",
		      "ADR;HOME;REGION" => "",
		      "ADR;HOME;POSTALCODE" => "322",
		      "ADR;HOME;COUNTRYNAME" => "",
			
		      "TEL;WORK" => "US_PHONE",
		      "TEL;WORK;VOICE" => "US_PHONE",
		      "TEL;HOME" => "",
		      "TEL;VOICE" => "",
		      "TEL;FAX" => "US_FAX",
		      "TEL;WORK;FAX" => "US_FAX",
		      "TEL;MSG" => "",
		      "TEL;CELL" => "US_MOBILE",
		      "TEL;CELL;VOICE" => "US_MOBILE",
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
	      if (ereg ("([A-Z;]*);ENCODING=QUOTED-PRINTABLE:(.*)", $line, $reg)){
		  $tattr[$reg[1]]=quoted_printable_decode(rtrim($reg[2]));
	      } elseif (ereg ("([A-Z;]*);CHARSET=UTF-8:(.*)", $line, $reg)){
		  $tattr[$reg[1]]=utf8_decode(rtrim($reg[2]));
	      } elseif (ereg ("([A-Z;]*):(.*)", $line, $reg)){
	      //line like TEL;WORK:05.61.15.54.54
		  $tattr[$reg[1]]=str_replace("\\n","\n",rtrim($reg[2]));
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
	
		// if is single value (no regexp)
		if (ereg("^[0-9A-Z_]*$",$this->import[$k]))
		  {
		    // suppress http
		    if ($this->import[$k] == "US_WORKWEB") $tattr[$this->import[$k]]=str_replace("http://","",$v);
		    else $tattr[$this->import[$k]]=$v;

		  }
		else
		  {
		    // regexp case
		      // example A;B;C;D;E;F
			$complxreg="([^;]*)[;]{0,1}([^;]*)[;]{0,1}([^;]*)[;]{0,1}([^;]*)[;]{0,1}([^;]*)[;]{0,1}([^;]*)[;]{0,1}";
		   
		    if (ereg($complxreg,
			     $this->import[$k], $reg))
		      { 
			if (ereg($complxreg, $v , $regv))
			  {
			    for ($ir=1;$ir<7;$ir++) {
			      if ($reg[$ir] == "US_WORKWEB") $tattr[$reg[$ir]]=str_replace("http://","",$regv[$ir]);
			      else $tattr[$reg[$ir]]= $regv[$ir];
			    }
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
	      $v=strtolower($v);
	      if (isset($tattr[$v]))
		fputs($this->fd,$k.":".str_replace("\n","\\n",$tattr[$v])."\n");
	      
	      else { // multi fields
		       $lidattr = explode(";", $v);
		     if ((is_array($lidattr)) && (count($lidattr) > 1)){
		       fputs($this->fd,"$k:");
		       while(list($k2,$idattr) = each($lidattr)) {
			 
			 if (isset($tattr[$idattr])) fputs($this->fd,str_replace("\n","\\n",$tattr[$idattr]));
			 if ($k2 < count($lidattr) - 1) fputs($this->fd,";");
		       }
		       fputs($this->fd,"\n");
		     }
		   }
	      
	    }
	}
      fputs($this->fd,"END:VCARD\n\n");
    }
}

?>
