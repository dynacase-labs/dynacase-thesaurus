<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Method.PostIt.php,v 1.4 2005/02/15 15:47:56 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */




  
var $defaultview= "FDL:VIEWPOSTIT:T";
var $defaultedit= "FDL:EDITPOSTIT:T";
  
  
// -----------------------------------
function viewpostit($target="_self",$ulink=true,$abstract=false) {
  // -----------------------------------

  $tcomment = $this->getTvalue("PIT_COM");
  $tuser = $this->getTvalue("PIT_USER");
  $tdate = $this->getTvalue("PIT_DATE");
  $tcolor = $this->getTvalue("PIT_COLOR");


  $tlaycomment=array();
  while (list($k,$v) = each($tcomment)) {
    $tlaycomment[]=array("comments"=>$v,
			 "user"=>$tuser[$k],
			 "date"=>$tdate[$k],
			 "color"=>$tcolor[$k]);
  }

 
  // Out


  $this->lay->SetBlockData("TEXT",	 $tlaycomment);

}
function editpostit() {
  $this->editattr();
}
function PostModify() {
  $docid= $this->getValue("PIT_IDADOC");
  if ($docid > 0) {
    $doc= new Doc($this->dbaccess, $docid);
    if (intval($doc->postitid) == 0) {
      $doc->disableEditControl();
      $doc->postitid=$this->id;
      $doc->modify();
      $doc->enableEditControl();
    }
  }

  $ncom = $this->getValue("PIT_NCOM");
  if ($ncom != "") {

    $tcom=$this->getTValue("PIT_COM");
    $tdate=$this->getTValue("PIT_DATE");
    $tiduser=$this->getTValue("PIT_IDUSER");
    $tcolor=$this->getTValue("PIT_COLOR");

    foreach ($tcom as $k=>$v) {
      if ($v=="") {
	unset($tcom[$k]);
	unset($tdate[$k]);
	unset($tiduser[$k]);
	unset($tcolor[$k]);
      }
    }
    $nk=count($tcom);
    $tcom[$nk]=$ncom;
    $tdate[$nk]=$this->getDate();
    $tiduser[$nk]=$this->getUserId();
    $tcolor[$nk]=$this->getValue("PIT_NCOLOR");

    $this->setValue("PIT_COM",$tcom);
    $this->setValue("PIT_DATE",$tdate);
    $this->setValue("PIT_IDUSER",$tiduser);
    $this->setValue("PIT_COLOR",$tcolor);
    $this->deleteValue("PIT_NCOLOR");
    $this->deleteValue("PIT_NCOM");

    
  }
}

function PostDelete() {
  $docid= $this->getValue("PIT_IDADOC");
  if ($docid > 0) {
    $doc= new Doc($this->dbaccess, $docid);
    if ($doc->locked == -1) $doc= new Doc($this->dbaccess, $doc->latestId());
    if (intval($doc->postitid) > 0) {
      $doc->disableEditControl();
      $doc->postitid=0;
      $doc->modify();
      $doc->enableEditControl();
    }
  }
}
?>