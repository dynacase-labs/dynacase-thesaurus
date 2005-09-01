<?php
/**
 * User calendar visibility
 *
 * @author Anakeen 2000 
 * @version $Id: Class.UCalVis.php,v 1.1 2005/09/01 16:49:01 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage 
 */
 /**
 */

Class UCalVis extends DbObj {

  var $dbtable="ucalvis";
  var $isCacheble= false;

  var $fields = array ("ucalvis_ucal", 
		       "ucalvis_uwid", 
		       "ucalvis_ufid", 
		       "ucalvis_gwid", 
		       "ucalvis_gfid", 
		       "ucalvis_mode" );
  var $id_fields = array ("ucalvis_ucal", "ucalvis_ufid", "ucalvis_gfid");
  var $order_by="ucalvis_ufid";
  
  var $sqlcreate = "
create table ucalvis (
                     ucalvis_ucal int , 
                     ucalvis_uwid int , 
                     ucalvis_ufid int , 
                     ucalvis_gwid int , 
                     ucalvis_gfid int , 
                     ucalvis_mode int );
create unique index idx_ucalvis on ucalvis(ucalvis_ucal,ucalvis_ufid,ucalvis_gfid);";


  function cleanUCal($u_fid=-1, $u_cal=-1) {

    if ($u_fid == -1 || $u_cal == -1) return false;
    $qs = "delete from ".$this->dbtable." where ucalvis_ucal=$u_cal and ucalvis_ufid=$u_fid";
    $this->exec_query($qs);
    return true;
  }

  function isUCalInit($ufid, $ucal) {
    $qs = "select count(ucalvis_uwid) from ".$this->dbtable
      .   " where ucalvis_ucal=".$ucal
      .   " and ucalvis_ufid=".$ufid;
    $this->exec_query($qs);
    $nx = $this->numrows();
    if ($this->numrows()>1) return true;
    return false;
  }
    
  function  getCalVisForGroups($ufid=-1, $g_fid=-1, $u_cal=0, $mode=0, $ufilter="", $limit=25) {
    global $action;

    $ufid = ($ufid==-1 ? $action->user->fid : $ufid);
   
    if ($g_fid == -1) return false;
    $glist = "";
    if (is_array($g_fid)) {
      foreach ($g_fid as $k => $v) $glist .= ($glist==""?"":",") . $v;
    } else {
      $glist = $g_fid;
    }


    $sqs = "select distinct(ucalvis_ufid) from ucalvis "
      .    " where (ucalvis_ufid!=".$ufid.") "
      .    " and (ucalvis_ucal=".$u_cal." ) "
      .    " and (ucalvis_gfid in (".$glist.") or ucalvis_gwid=2) ";
    $sqs .= ($mode==1 ? " and ucalvis_mode=$mode" : "");

    if ($ufilter!="") {
      $fil = " and ( title ~* '".$ufilter."') ";
    }
    $ucalret = array();
    $qs = "select * from doc128 where (id in ($sqs)) ".$fil;
    
    $this->exec_query($qs);
    $nx = $this->numrows();
    if ($nx>0) {
      for ($c=0; $c<$nx;$c++) {
 	$ucalret[] = $this->fetch_array($c,PGSQL_ASSOC);
      }
    }
//     echo "qs = [$qs]<br>";
//     echo "sqs = [$sqs]<br>";
//     print_r2($ucalret);
    return $ucalret;
  }


} // Enf od Class
?>