<?php
/**
 * Mapping Attributes between LDAP & FREEDOM
 *
 * @author Anakeen 2005
 * @version $Id: Class.DocAttrLDAP.php,v 1.1 2006/01/02 13:19:26 eric Exp $
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
			   "ldapclass"); 

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


  public $id_fields = array ("famid","ldapname");

  public $dbtable = "docattrldap";


  public $sqlcreate = "create table docattrldap (famid  int not null,                   
                    ldapname text not null,
                    ldapmap text,
                    ldapclass text  );
create index i_docattrldap on docattrldap(famid,ldapname);";


}

?>