<?php
// ---------------------------------------------------------------
// $Id: Class.UsercardLdif.php,v 1.3 2002/03/12 09:55:12 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Usercard/Class.UsercardLdif.php,v $
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
// $Log: Class.UsercardLdif.php,v $
// Revision 1.3  2002/03/12 09:55:12  eric
// correction pour vcard & ldap
//
// Revision 1.2  2002/02/14 18:11:42  eric
// ajout onglet et autres...
//
// Revision 1.1  2002/02/13 14:31:58  eric
// ajout usercard application
//
// Revision 1.1  2001/06/19 16:16:37  eric
// importation fichier
//
//
// ---------------------------------------------------------------



Class UsercardLdif 
{
  var $import = array(
		      // Person Class
		      "sn"                              => "201",
		      "cn"                              => "",
		      "userPassword"                    => "",
		      "telephonenumber"                 => "206",
		      
		      // organizationalPerson
		      "title"           		=> "", 
		      "x121Address"     		=> "", 
		      "registeredAddress"               => "", 
		      "destinationIndicator"            => "", 
		      "preferredDeliveryMethod"         => "", 
		      "telexNumber"                     => "", 
		      "telexTerminalIdentifier"         => "", 
		      "internationaliSDNNumner"         => "", 
		      "facsimiletelephonenumber"        => "210",
		      "street"                          => "",
		      "postOfficeBox"                   => "",
		      "postalcode"                      => "213",
		      "postalAddress"                   => "212",
		      "physicalDeliveryOfficeName"      => "",
		      "ou"                              => "",
		      "st"                              => "",
		      "l"                               => "214",
		      
		      // InetOrgPerson
		      "audio"                           => "",
		      "businessCategory"                => "",
		      "carLicense"                      => "",
		      "departmentNumber"                => "228",
		      "displayName"                     => "",
		      "employeeNumber"                  => "220",
		      "employeeType"                    => "",
		      "givenName"                       => "202",
		      "homePhone"                       => "225",
		      "homePostalAddress"               => "",
		      "Initials"                        => "",
		      "jpegPhoto"                       => "203",
		      "labeledURI"                      => "215",
		      "mail"                            => "205",
		      "manager"                         => "",
		      "mobile"                          => "207",
		      "o"                               => "211",
		      "pager"                           => "",
		      "photo"                           => "",
		      "roomNumber"                      => "223",
		      "secretary"                       => "",
		      "uid"                             => "",
		      "userCertificate"                 => "",
		      "x500uniqueIdentifier"            => "",
		      "preferredLanguage"               => "",
		      "userSMIMECertificate"            => "",
		      "userPKCS12"                      => "",
		      
		      
		      
		      
		      "objectclass" => "");
  
  function g_ReadCard(&$tattr) 
    {
      // Read a structure of import file : return array ('name', 'value')
	
	$tattr=array();
      $endCardFound = false;
      $beginCardFound = false;
      $line="";
      
      // search begin of a card : dn line
	while ( (! feof ($this->fd)) &&
	       (! $beginCardFound) )
	  {
	    $line = fgets($this->fd, 4096);
	    $beginCardFound = ereg ("dn:(.*)", $line);
	  }
      
      
      // search element of a card until : objectclass
	while ( (! feof ($this->fd)) &&
	       (! $endCardFound) )
	  {
	    if (! $endCardFound)
	      {
		//line like cellphone:05.61.15.54.54
		  if (ereg ("([a-z;]*):(.*)", $line, $reg))
		    $tattr[$reg[1]]=utf8_decode($reg[2]);
	      }
	    
	    $line = fgets($this->fd, 4096);
	    $endCardFound = ereg ("objectclass:(.*)", $line);
	  }
      
      return ( ! feof ($this->fd));
    }
}

?>
