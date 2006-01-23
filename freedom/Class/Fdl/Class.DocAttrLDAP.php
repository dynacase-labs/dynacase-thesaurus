<?php
/**
 * Mapping Attributes between LDAP & FREEDOM
 *
 * @author Anakeen 2005
 * @version $Id: Class.DocAttrLDAP.php,v 1.3 2006/01/23 17:08:41 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
/**
 */

include_once("Class.DbObj.php");
Class DocAttrLDAP extends DbObj {
  public $fields = array ( "famid", // family id
			   "ldapname",  // 
			   "ldapmap", // 
			   "ldapclass",
			   "index"); 

  /**
   * identificator of the family document
   * @public int
   */
  public $famid;  
			  
  /**
   * identificator of the LDAP attribute
   * @public string
   */
  public $ldapname;		  
  /**
   * map function
   * @public string
   */
  public $ldapmap;		  
  /**
   * LDAP class of attribute
   * @public string
   */
  public $ldapclass;		  
  /**
   * indice to indicate the card reference in case of multi-card LDAP for one document
   * @public character
   */
  public $index;


  public $id_fields = array ("famid","ldapname","index");

  public $dbtable = "docattrldap";


  public $sqlcreate = "create table docattrldap (famid  int not null,                   
                    ldapname text not null,
                    ldapmap text,
                    ldapclass text,
                    index char);
create index i_docattrldap on docattrldap(famid,ldapname);";


}

?>