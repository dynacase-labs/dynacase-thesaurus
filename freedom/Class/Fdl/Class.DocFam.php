<?php
/**
 * Family Document Class
 *
 * @author Anakeen 2000 
 * @version $Id: Class.DocFam.php,v 1.25 2005/09/09 16:24:13 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
include_once('FDL/Class.PFam.php');

Class DocFam extends PFam {
 
  var $dbtable="docfam";

  var $sqlcreate = "
create table docfam (cprofid int , 
                     dfldid int, 
                     cfldid int, 
                     ccvid int, 
                     ddocid int,
                     methods text,
                     defval text,
                     schar char,
                     param text) inherits (doc);
create unique index idx_idfam on docfam(id);";


  var $defDoctype='C';
 
  var $defaultview= "FDL:VIEWFAMCARD";

  var $attr;

  function __construct($dbaccess='', $id='',$res='',$dbid=0) {

    $this->fields["dfldid"] ="dfldid";
    $this->fields["cfldid"] ="cfldid";
    $this->fields["ccvid"] ="ccvid";
    $this->fields["cprofid"]="cprofid";
    $this->fields["ddocid"] ="ddocid";
    $this->fields["methods"]="methods";
    $this->fields["defval"]="defval";
    $this->fields["param"]="param";
    $this->fields["schar"]="schar"; // specials characteristics R : revised on each modification
    parent::__construct($dbaccess, $id, $res, $dbid);
     
    $this->doctype='C';
    if (($this->id > 0)&& ($this->isAffected())) {
      $adoc = "Doc".$this->id;
      $GEN=getGen($dbaccess);
      include_once("FDL$GEN/Class.$adoc.php");
      $adoc = "ADoc".$this->id;
      $this->attributes = new $adoc();
      uasort($this->attributes->attr,"tordered"); 
    }
               
  }



  function PostModify() {    
    include_once("FDL/Lib.Attr.php");
    return refreshPhpPgDoc($this->dbaccess, $this->id);
  }
  function preCreated() {
    $cdoc=$this->getFamDoc();
    if ($cdoc->isAlive() ) {
     if (! $this->ccvid) $this->ccvid=$cdoc->ccvid;
     if (! $this->cprofid) $this->cprofid=$cdoc->cprofid;
     if (! $this->defval) $this->defval=$cdoc->defval;
     if (! $this->schar) $this->schar=$cdoc->schar;
    }
  }

  // -----------------------------------
  function viewfamcard($target="_self",$ulink=true,$abstract=false) {
    // -----------------------------------

    global $action;

    while (list($k,$v) = each($this->fields)) {

      $this->lay->set("$v",$this->$v);
      switch ($v) {
      case cprofid:
	if ($this->$v > 0) {
	  $tdoc = new_Doc($this->dbaccess,$this->$v);
	  $this->lay->set("cproftitle",$tdoc->title);
	  $this->lay->set("cprofdisplay","");
	} else {
	  $this->lay->set("cprofdisplay","none");
	}
	break;
      case cfldid:
	if ($this->$v > 0) {
	  $tdoc = new_Doc($this->dbaccess,$this->$v);
	  $this->lay->set("cfldtitle",$tdoc->title);
	  $this->lay->set("cflddisplay","");
	} else {
	  $this->lay->set("cflddisplay","none");
	}
	break;
      case dfldid:
	if ($this->$v > 0) {
	  $tdoc = new_Doc($this->dbaccess,$this->$v);
	  $this->lay->set("dfldtitle",$tdoc->title);
	  $this->lay->set("dflddisplay","");
	} else {
	  $this->lay->set("dflddisplay","none");
	}
	break;
      case wid:
	if ($this->$v > 0) {
	  $tdoc = new_Doc($this->dbaccess,$this->$v);
	  $this->lay->set("wtitle",$tdoc->title);
	  $this->lay->set("wdisplay","");
	} else {
	  $this->lay->set("wdisplay","none");
	}
	break;
      case ccvid:
	if ($this->$v > 0) {
	  $tdoc = new_Doc($this->dbaccess,$this->$v);
	  $this->lay->set("cvtitle",$tdoc->title);
	  $this->lay->set("cvdisplay","");
	} else {
	  $this->lay->set("cvdisplay","none");
	}
	break;
      }
    }


  }

  //~~~~~~~~~~~~~~~~~~~~~~~~~ PARAMETERS ~~~~~~~~~~~~~~~~~~~~~~~~

 /**
   * return family parameter
   * 
   * @param string $idp parameter identificator
   * @param string $def default value if parameter not found or if it is null
   * @return string parameter value
   */
  final public function getParamValue($idp, $def="") {
    return $this->getXValue("param",$idp,$def);
    
  }

 /**
   * return all family parameter
   * 
   * @return array string parameter value
   */
  function getParams() {
    return $this->getXValues("param");
  }


  /**
   * return the value of an list parameter document
   *
   * the parameter must be in an array or of a type '*list' like enumlist or textlist
   * @param string $idAttr identificator of list parameter
   * @param string $def default value returned if parameter not found or if is empty
   * @return array the list of parameter values
   */
  function GetParamTValue($idAttr, $def="",$index=-1)  {
    $t = $this->_val2array($this->getParamValue("$idAttr",$def));
    if ($index == -1) return $t;
    if (isset($t[$index])) return $t[$index];
    else return $def;
  }


 /**
   * set family parameter value
   * 
   * @param string $idp parameter identificator
   * @param string $val value of the parameter
   */
  function setParam($idp, $val) {
    return $this->setXValue("param",$idp, $val);
    
  }

  //~~~~~~~~~~~~~~~~~~~~~~~~~ DEFAULT VALUES  ~~~~~~~~~~~~~~~~~~~~~~~~

 /**
   * return family default value
   * 
   * @param string $idp parameter identificator
   * @param string $def default value if parameter not found or if it is null
   * @return string default value
   */
  function getDefValue($idp, $def="") {
    return $this->getXValue("defval",$idp,$def);
  }

 /**
   * return all family default values
   * 
   * @return array string default value
   */
  function getDefValues() {
    return $this->getXValues("defval");
  }

 /**
   * set family default value
   * 
   * @param string $idp parameter identificator
   * @param string $val value of the default
   */
  function setDefValue($idp, $val) {
    return $this->setXValue("defval",$idp, $val);

  }  

  //~~~~~~~~~~~~~~~~~~~~~~~~~ X VALUES  ~~~~~~~~~~~~~~~~~~~~~~~~

 /**
   * return family default value
   * 
   * @param string $idp parameter identificator
   * @param string $def default value if parameter not found or if it is null
   * @return string default value
   */
  function getXValue($X,$idp, $def="") {
    $tval="t$X";
    if (! isset($this->$tval)) $this->getXValues($X);
   
    $tval2=$this->$tval;
    $v = $tval2[strtolower($idp)];
    if ($v != "") return $v;
    return $def;
  }

 /**
   * return all family default values
   * 
   * @return array string default value
   */
  function getXValues($X) {
    $tval="t$X";
    $defval=$this->$X;

    $tdefattr = explode("][",substr($defval,1,strlen($defval)-2));
    $this->$tval=array();

    $txval=array();
    foreach ($tdefattr as $k=>$v) {

	$aid=substr($v, 0, strpos($v,'|'));
	$dval=substr(strstr($v,'|'),1);

	$txval[$aid]=$dval;
      }    
    $this->$tval=$txval;

    return $this->$tval;
  }

 /**
   * set family default value
   * 
   * @param string $idp parameter identificator
   * @param string $val value of the default
   */
  function setXValue($X, $idp, $val) {
    $tval="t$X";
    if (! isset($this->$tval)) $this->getXValues($X);
    $txval=$this->$tval;
    $txval[strtolower($idp)]=$val;
    $this->$tval=$txval;
    
    $tdefattr=array();
    foreach ($txval as $k=>$v) {
      $tdefattr[]="$k|$v";
    }

    $this->$X = "[".implode("][",$tdefattr)."]";
  }

}

?>
