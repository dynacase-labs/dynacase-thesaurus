<?php
/**
 * Document Attributes
 *
 * @author Anakeen 2000 
 * @version $Id: Class.DocAttribute.php,v 1.34 2006/11/16 16:42:37 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 */
 /**
 */



Class BasicAttribute {
  public $id;
  public $docid;
  public $labelText;
  public $visibility; // W, R, H, O, M, I
  public $options;
  public $type; // text, longtext, date, file, ...

  function __construct($id, $docid, $label ) {
    $this->id=$id;
    $this->docid=$docid;
    $this->labelText=$label;
  }
  /**
   * return value of option $x
   */
  function getOption($x) {
    if (!isset($this->_topt)) {
      $topt=explode("|",$this->options);
      $this->_topt=array();
      foreach ($topt as $k=>$v) {
	list($vn,$vv)=explode("=",$v);
	$this->_topt[$vn]=$vv;
      }
    }
    
    return $this->_topt[$x];
  
  }

  
  /**
   * to see if an attribute is n item of an array
   */
  function inArray() {
    if (get_class($this) == "NormalAttribute") {
      if ($this->fieldSet->type=="array") return true;
    }
    return false;
   }
  
}

Class NormalAttribute extends BasicAttribute {
  public $needed; // Y / N
  public $format; // C format
  public $eformat; // format for edition : list,vcheck,hcheck
  public $repeat; // true if is a repeatable attribute
  public $isInTitle;
  public $isInAbstract;
  public $fieldSet; // field set object
  public $link; // hypertext link
  public $phpfile;
  public $phpfunc;
  public $elink; // extra link
  public $ordered;
  public $phpconstraint; // special constraint set
  public $usefor; // = Q if parameters
  function __construct($id, $docid, $label, $type, $format, $repeat, $order, $link,
			   $visibility, $needed,$isInTitle,$isInAbstract,
			   &$fieldSet,$phpfile,$phpfunc,$elink,$phpconstraint="",$usefor="",$eformat="",$options="") {
    $this->id=$id;
    $this->docid=$docid;
    $this->labelText=$label;
    $this->type=$type;
    $this->format=$format;
    $this->eformat=$eformat;
    $this->ordered=$order;
    $this->link=$link;
    $this->visibility=$visibility;
    $this->needed=$needed;
    $this->isInTitle =$isInTitle;
    $this->isInAbstract=$isInAbstract;
    $this->fieldSet=&$fieldSet;
    $this->phpfile=$phpfile;
    $this->phpfunc=$phpfunc;
    $this->elink=$elink;
    $this->phpconstraint=$phpconstraint;
    $this->usefor=$usefor;
    $this->repeat=$repeat;
    $this->options=$options;


  }

  
  function getEnum() {   
    global $__tenum; // for speed optimization
    global $__tlenum;

    if (isset($__tenum[$this->id])) return $__tenum[$this->id]; // not twice
 
    if (($this->type == "enum") || ($this->type == "enumlist")) {
      // set the enum array
      $this->enum=array();
      $this->enumlabel=array();

      if (($this->phpfile != "") && ($this->phpfile != "-")) {
	// for dynamic  specification of kind attributes
	if (! include_once("EXTERNALS/$this->phpfile")) {
	  global $action;
	  $action->exitError(sprintf(_("the external pluggin file %s cannot be read"), $this->phpfile));
	}
	if (ereg("(.*)\((.*)\)", $this->phpfunc, $reg)) {	 
	  $args=explode(",",$reg[2]);
	  $this->phpfunc = call_user_func_array($reg[1],$args);	  
	} else {
	  AddWarningMsg(sprintf(_("invalid syntax for [%s] for enum attribute"),$this->phpfunc));
	}
      }

      $sphpfunc = str_replace("\\.", "-dot-",$this->phpfunc); // to replace dot & comma separators
      $sphpfunc  = str_replace("\\,", "-comma-",$sphpfunc);
      
      $tenum = explode(",",$sphpfunc);

      while (list($k, $v) = each($tenum)) {
	list($n,$text) = explode("|",$v);
	list($n1,$n2) = explode(".",$n);

	$text=str_replace( "-dot-",".",$text);
	$text=str_replace( "-comma-",",",$text);
	$n=str_replace( "-dot-",".",$n);
	$n=str_replace( "-comma-",",",$n);
	$n1=str_replace( "-dot-",".",$n1);
	$n1=str_replace( "-comma-",",",$n1);


	if ($n2 != "") $this->enum[$n]=$this->enum[$n1]."/".$text;
	else $this->enum[$n]=$text;
	if ($n2 != "") $this->enumlabel[substr($n,strrpos($n,'.')+1)]=$this->enum[$n];
	else $this->enumlabel[$n]=$this->enum[$n];
      }
    }
    $__tenum[$this->id]=$this->enum;
    $__tlenum[$this->id]=$this->enumlabel;
    return $this->enum;
  }

  function getEnumLabel($enumid="") {  
    global $__tlenum;

    $this->getEnum();
    if (isset($__tlenum[$this->id])){
      if ($enumid=="") return $__tlenum[$this->id]; // not twice
      else if (isset($__tlenum[$this->id][$enumid])) return $__tlenum[$this->id][$enumid];
      else return $enumid;
    }
    
  
  }

 
}


Class FieldSetAttribute extends BasicAttribute {

  public $fieldSet; // field set object
  function __construct($id, $docid, $label, $visibility="",$usefor="",$type="frame", &$fieldSet=null ) {
    $this->id=$id;
    $this->docid=$docid;
    $this->labelText=$label;
    $this->visibility=$visibility;
    $this->usefor=$usefor;
    $this->type=$type;
    $this->fieldSet=&$fieldSet;
  }
}

Class MenuAttribute extends BasicAttribute {
  public $link; // hypertext link
  public $ordered;
  public $precond; // pre-condition to activate menu

  function __construct($id, $docid, $label, $order, $link, $visibility="", $precond="",$options="") {
    $this->id=$id;
    $this->docid=$docid;
    $this->labelText=$label;
    $this->ordered=$order;
    $this->link=$link;
    $this->visibility=$visibility;
    $this->options=$options;
    $this->precond=$precond;
    $this->type="menu";

  }

}

Class ActionAttribute extends BasicAttribute {

  public $wapplication; // the what application name
  public $waction; // the what action name
  public $ordered;
  public $precond; // pre-condition to activate action
  function __construct($id, $docid, $label, $order,$visibility="",$wapplication="",$waction="", $precond="",$options="" ) {
    $this->id=$id;
    $this->docid=$docid;
    $this->labelText=$label;
    $this->visibility=$visibility;
    $this->ordered=$order;
    $this->waction=$waction;
    $this->wapplication=$wapplication;
    $this->options=$options;
    $this->precond=$precond;
    $this->type="action";
  }
  function getLink($docid) {
    $l=getParam("CORE_STANDURL");
    $batch=($this->getOption("batchfolder")=="yes");
    if ($batch) {
      $l.="&app=FREEDOM&action=BATCHEXEC&sapp=".$this->wapplication;
      $l.="&saction=".$this->waction;
      $l.="&id=".$docid;
    } else {
      $l.="&app=".$this->wapplication;
      $l.="&action=".$this->waction;
      $l.="&id=".$docid;
    }
    return $l;
  }
}
?>
