<?php
/**
 *  Control view Class Document
 *
 * @author Anakeen 2003
 * @version $Id: Class.CVDoc.php,v 1.1 2003/12/15 08:38:52 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: Class.CVDoc.php,v 1.1 2003/12/15 08:38:52 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Class.CVDoc.php,v $
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




include_once('FDL/Class.Doc.php');

/**
 * Control view Class
 */
Class CVDoc extends Doc {

  
  /**
   * CVDoc has its own special access depend on special views
   * by default the three access are always set
   *
   * @var array
   */
  var $acls = array("view","edit","delete");

	

  var $usefor='W';
  var $defDoctype='P';
  var $attrPrefix="CVI"; // prefix attribute
  // --------------------------------------------------------------------
 

  function CVDoc($dbaccess='', $id='',$res='',$dbid=0) {
    // first construct acl array

   
    if (isset($this->fromid)) $this->defProfFamId=$this->fromid; // it's a profil itself



    // don't use Doc constructor because it could call this constructor => infinitive loop
    DocCtrl::DocCtrl($dbaccess, $id, $res, $dbid);

    $this->setAcls();
  }


  
  function setAcls() {
    $ti = $this->getTValue("CV_IDVIEW");
    $tl = $this->getTValue("CV_LVIEW");
    $tz = $this->getTValue("CV_ZVIEW");
    $tk = $this->getTValue("CV_KVIEW");
    $tm = $this->getTValue("CV_MSKID");

    
    $ka = POS_WF;
    while (list($k, $v) = each($tk)) {
      if ($ti[$k]=="") $cvk="CV$k";
      else $cvk=$ti[$k];
      $this->dacls[$cvk]=array("pos"=>$ka,
			       "description" =>$tl[$k]);
      $this->acls[]=$cvk;
      $ka++;
    }
  }


  function getView($vid) {
    $ti = $this->getTValue("CV_IDVIEW");
    foreach ($ti as $k=>$v) {
      if ($v == $vid) {
	// found it
	$tl = $this->getTValue("CV_LVIEW");
	$tz = $this->getTValue("CV_ZVIEW");
	$tk = $this->getTValue("CV_KVIEW");
	$tm = $this->getTValue("CV_MSKID");

	return array("CV_IDVIEW"=>$v,
		     "CV_LVIEW"=>$tl[$k],
		     "CV_ZVIEW"=>$tz[$k],
		     "CV_KVIEW"=>$tk[$k],
		     "CV_MSKID"=>$tm[$k]);
	
      }
    }
    return false;
  }
  

  function postModify() {
    
    $ti = $this->getTValue("CV_IDVIEW");
    foreach ($ti as $k=>$v) {
      if ($v == "") $ti[$k]="CV$k";
    }
    $this->setValue("CV_IDVIEW",$ti);
  }
  
}

?>
