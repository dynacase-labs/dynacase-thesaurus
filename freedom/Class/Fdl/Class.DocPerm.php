<?php
/**
 * Document permissions
 *
 * @author Anakeen 2000 
 * @version $Id: Class.DocPerm.php,v 1.11 2004/08/09 08:07:06 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 */
 /**
 */





$CLASS_DOCPERM_PHP = '$Id: Class.DocPerm.php,v 1.11 2004/08/09 08:07:06 eric Exp $';
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
                     docid int check (docid > 0),
                     userid int check (userid > 1),
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

  function preInsert() {
    if ($this->userid==1) return _("not perm for admin");   
    if (($this->upacl==0) && ($this->unacl==0)) return _("not pertinent");   
    if ($this->unacl==="") $this->unacl="0";
    if ($this->cacl==="") $this->cacl="0";
  }

  function getUperm($docid, $userid) {
    $q = new QueryDb($this->dbaccess, "docperm");
    $t = $q -> Query(0,1,"TABLE","select getuperm($userid,$docid) as uperm");

    return $t[0]["uperm"];
  }
  function recomputeControl() {
    if ($this->docid > 0) 
      $this->exec_query("select getuperm(userid,docid) as uperm from docperm where docid=".$this->docid);
  }
  // --------------------------------------------------------------------
  function ControlU ($pos) {
    // --------------------------------------------------------------------     
        
    if ( ! isset($this->uacl)) {       
      if ($this->upacl == 0) {
	if ( ! isset($this->gacl)) {       
	  $q = new QueryDb($this->dbaccess, "docperm");
	  $t = $q -> Query(0,1,"TABLE","select computegperm({$this->userid},{$this->docid}) as uperm");

	  $this->gacl=$t[0]["uperm"];
	  $this->uacl = $this->gacl;
	}
      } else $this->uacl = $this->getUperm($this->docid,$this->userid);

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
