<?php
/**
 * Search Document
 *
 * @author Anakeen 2008
 * @version $Id: Class.SearchDoc.php,v 1.2 2008/05/20 09:52:31 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 */
/**
 */


include_once("FDL/Lib.Dir.php");


Class SearchDoc {  
  /**
   * family identificator filter
   * @public string
   */
  public $fromid;
  /**
   * folder identificator filter
   * @public int
   */
  public $dirid=0;
  /**
   * number of results : set "ALL" if no limit
   * @public int
   */
  public $slice="ALL";
  /**
   * index of results begins
   * @public int
   */
  public $start=0;
  /**
   * sql filters
   * @public array
   */
  public $filters=array();
 
  /**
   * search in sub-families set false if restriction to top family
   * @public bool
   */
  public $only=false;
  /**
   * 
   * @public bool
   */
  public $distinct=false;
  /**
   * order of result : like sql order
   * @public string
   */
  public $order="title";
  /**
   * to search in trash : [no|also|only]
   * @public string
   */
  public $trash="no";
  /**
   * restriction to latest revision
   * @public bool
   */
  public $latest=true;
  /**
   * user identificator : set to current user by default
   * @public int
   */
  public $userid=0;

  /**
   * result type [ITEM|TABLE]
   * @private string
   */
  private $mode="TABLE";
  private $count=-1;
  private $index=0;
  private $result;

  /**
   * initialize with family
   *
   * @param string $dbaccess database coordinate
   * @param string $fromid family identificator to filter
   * 
   */
  public function __construct($dbaccess, $fromid=0) {
    $this->dbaccess=$dbaccess;
    $this->fromid=$fromid;
    $this->orderby='title';
    $this->userid=getUserId();
  }

  /**
   * count results
   * ::search must be call before
   *
   * @return int 
   * 
   */
  public function count() {
    if ($this->count==-1) {
      if ($this->mode=="ITEM") {
	$this->count=countDocs($this->result);
      } else {
	$this->count=count($this->result);
      }
    }
    return $this->count;
  }
  /**
   * send search
   *
   * @return array of documents
   * 
   */
  public function search() {
    if ($this->only) {
      if (is_numeric($this->fromid)) $this->fromid=-(abs($this->fromid));
      else {
	$this->fromid=getFamIdFromName($this->dbaccess,$this->fromid);
	$this->fromid=-(abs($this->fromid));
      }
    }
    $this->index=0;
    $this->result = getChildDoc($this->dbaccess, 
				$this->dirid,
				$this->start,
				$this->slice, $this->filters,$this->userid,$this->mode,
				$this->fromid,$this->distinct,$this->orderby, $this->latest, $this->trash);
    return $this->result;
  }  

  /**
   * can, be use in 
   * ::search must be call before
   *
   * @return int 
   */
  public function nextDoc() {
    if ($this->mode=="ITEM") {
      return getNextDoc($this->dbaccess,$this->result);
    } else {
      return $this->result[$this->index++];
    }   
  }  

  /**
   * add a condition in filters
   *
   * @return void
   */
  public function addFilter($filter) {
    if ($filter != "") $this->filters[]=$filter;
  }  

  /**
   * add a condition in filters
   *
   * @return void
   */
  public function noViewControl() {
    $this->userid=1;
  }
  /**
   * the return of ::search will be array of document's object
   *
   * @return void
   */
  public function setObjectReturn() {
    $this->mode="ITEM";
  }
  /**
   * the return of ::search will be array of values
   *
   * @return void
   */
  public function setValueReturn() {
    $this->mode="TABLE";
  }


}


?>