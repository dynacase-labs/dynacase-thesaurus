<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Class.DocPerm.php,v 1.6 2003/08/18 15:47:04 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 */
 /**
 */


// ---------------------------------------------------------------
// $Id: Class.DocPerm.php,v 1.6 2003/08/18 15:47:04 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Class.DocPerm.php,v $
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


$CLASS_DOCPERM_PHP = '$Id: Class.DocPerm.php,v 1.6 2003/08/18 15:47:04 eric Exp $';
include_once("Class.DbObj.php");

/**
 * Managing permissions of documents
 * @package FREEDOM
 *
 */
Class DocPerm extends DbObj
{
  var $fields = array ("docid",
		       "userid",
		       "upacl",
		       "unacl",
		       "cacl");

  var $sup_fields = array("getuperm(userid,docid) as uperm");
  var $id_fields = array ("docid","userid");

  var $dbtable = "docperm";

  var $order_by="docid";

  var $isCacheble= false;
  var $sqlcreate = "
create table docperm ( 
                     docid int,
                     userid int not null,
                     upacl int  not null,
                     unacl int  not null,
                     cacl int not null
                   );
create unique index idx_perm on docperm(docid, userid);
create trigger tinitacl AFTER INSERT OR UPDATE ON docperm FOR EACH ROW EXECUTE PROCEDURE initacl();";
  

  function preSelect($tid) {
    if (count($tid) == 2) {
      $this->docid=$tid[0];
      $this->userid=$tid[1];
    }
  }

  function getUperm($docid, $userid) {
    $q = new QueryDb($this->dbaccess, "docperm");
    $t = $q -> Query(0,1,"TABLE","select getuperm($userid,$docid) as uperm");

    return $t[0]["uperm"];
  }
    
  
  
  // --------------------------------------------------------------------
  function ControlU ($pos) {
    // --------------------------------------------------------------------     
        
    if ( ! isset($this->uacl)) {                  
      $this->uacl = $this->getUperm($this->docid,$this->userid);

    }
    return ($this->ControlMask($this->uacl,$pos));
  }

  // --------------------------------------------------------------------
  function ControlG ($pos) {
    // --------------------------------------------------------------------     
        
    if ( ! isset($this->gacl)) {       
      $q = new QueryDb($this->dbaccess, "docperm");
      $t = $q -> Query(0,1,"TABLE","select computegperm({$this->userid},{$this->docid}) as uperm");

      $this->gacl=$t[0]["uperm"];
    }
    
    return ($this->ControlMask($this->gacl,$pos));
  }

  
  // --------------------------------------------------------------------
  function ControlUp ($pos) {
    // --------------------------------------------------------------------     
        
    if ($this->isAffected()) {            
      return ($this->ControlMask($this->upacl,$pos));
    } 
    return false;
  }
  
  // --------------------------------------------------------------------
  function ControlUn ($pos) {
    // --------------------------------------------------------------------     
        
    if ($this->isAffected()) {            
      return ($this->ControlMask($this->unacl,$pos));
    } 
    return false;
  }

  // --------------------------------------------------------------------
  function ControlMask ($acl, $pos) {
    // --------------------------------------------------------------------     
        
    return (($acl & (1 << ($pos ))) != 0);
  }


  // --------------------------------------------------------------------
  function UnSetControl() {
  // --------------------------------------------------------------------
    $this->upacl=0;
    $this->unacl=0;
    $this->cacl=1;
  }

  // --------------------------------------------------------------------
  function SetControlP($pos) {
  // --------------------------------------------------------------------
    $this->upacl = $this->upacl | (1 << ($pos ));
  }

  // --------------------------------------------------------------------
  function SetControlN($pos) {
  // --------------------------------------------------------------------
    $this->unacl = $this->unacl | (1 << ($pos ));
    
  }
}
?>
