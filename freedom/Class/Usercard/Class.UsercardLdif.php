<?php
/**
 * Class use to import and  export Ldif format (like LDAP) with FREEDOM USER Family
 *
 * @author Anakeen 2001
 * @version \$Id: Class.UsercardLdif.php,v 1.9 2005/02/01 16:23:25 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
/**
 */
// ---------------------------------------------------------------
// $Id: Class.UsercardLdif.php,v 1.9 2005/02/01 16:23:25 eric Exp $
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
// 59 Temple Place, Suite 330, Boston, MA 0US_SOCIETY1-1307 USA
// ---------------------------------------------------------------




/**
 * Class use to import and  export Ldif format (like LDAP) with FREEDOM USER Family
 *
 * @deprecated use Method.User.php instead
 */
Class UsercardLdif 
{
  var $import = array(
		      // Person Class
		      "sn"                              => "US_LNAME",
		      "cn"                              => "",
		      "userPassword"                    => "US_PASSWD",
		      "telephonenumber"                 => "US_PHONE",
		      
		      // organizationalPerson
		      "title"           		=> "", 
		      "x121Address"     		=> "", 
		      "registeredAddress"               => "", 
		      "destinationIndicator"            => "", 
		      "preferredDeliveryMethod"         => "", 
		      "telexNumber"                     => "", 
		      "telexTerminalIdentifier"         => "", 
		      "internationaliSDNNumner"         => "", 
		      "facsimiletelephonenumber"        => "US_FAX",
		      "street"                          => "",
		      "postOfficeBox"                   => "",
		      "postalcode"                      => "US_WORKPOSTALCODE",
		      "postalAddress"                   => "US_WORKADDR",
		      "physicalDeliveryOfficeName"      => "",
		      "ou"                              => "",
		      "st"                              => "",
		      "l"                               => "US_WORKTOWN",
		      
		      // InetOrgPerson
		      "audio"                           => "",
		      "businessCategory"                => "",
		      "carLicense"                      => "",
		      "departmentNumber"                => "US_SERV",
		      "displayName"                     => "",
		      "employeeNumber"                  => "US_MATRICULE",
		      "employeeType"                    => "US_TYPE",
		      "givenName"                       => "US_FNAME",
		      "homePhone"                       => "US_HOMEPHONE",
		      "homePostalAddress"               => "",
		      "initials"                        => "US_INITIALS",
		      "jpegPhoto"                       => "US_PHOTO",
		      "labeledURI"                      => "US_WORKWEB",
		      "mail"                            => "US_MAIL",
		      "manager"                         => "",
		      "mobile"                          => "US_MOBILE",
		      "o"                               => "US_SOCIETY",
		      "pager"                           => "",
		      "photo"                           => "",
		      "roomNumber"                      => "US_LOCALISATION",
		      "secretary"                       => "",
		      "uid"                             => "US_WHATID",
		      "userCertificate"                 => "",
		      "x500uniqueIdentifier"            => "",
		      "preferredLanguage"               => "",
		      "userSMIMECertificate"            => "",
		      "userPKCS12"                      => "");
  
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
