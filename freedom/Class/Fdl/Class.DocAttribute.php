<?php
/**
 * Document Attributes
 *
 * @author Anakeen 2000 
 * @version $Id: Class.DocAttribute.php,v 1.23 2005/04/01 17:21:56 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



Class BasicAttribute {
  var $id;
  var $docid;
  var $labelText;
  var $visibility; // W, R, H, O, M, I

  function BasicAttribute($id, $docid, $label ) {
    $this->id=$id;
    $this->docid=$docid;
    $this->labelText=$label;
  }

  
  /**
   * to see if an attribute is n item of an array
   */
   function inArray() {
     if (get_class($this) == "normalattribute") {
       if ($this->fieldSet->type=="array") return true;
     }
     return false;
   }
  
}

Class NormalAttribute extends BasicAttribute {
  var $needed; // Y / N
  var $type; // text, longtext, date, file, ...
  var $format; // C format
  var $eformat; // format for edition : list,vcheck,hcheck
  var $repeat; // true if is a repeatable attribute
  var $isInTitle;
  var $isInAbstract;
  var $fieldSet; // field set object
  var $link; // hypertext link
  var $phpfile;
  var $phpfunc;
  var $elink; // extra link
  var $ordered;
  var $phpconstraint; // special constraint set
  var $usefor; // = Q if parameters
  function NormalAttribute($id, $docid, $label, $type, $format, $repeat, $order, $link,
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
    $this->repeat=$repeat || $this->inArray();
    $this->options=$options;


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

  function FieldSetAttribute($id, $docid, $label, $visibility="",$usefor="" ) {
    $this->id=$id;
    $this->docid=$docid;
    $this->labelText=$label;
    $this->visibility=$visibility;
    $this->usefor=$usefor;
  }
}

Class MenuAttribute extends BasicAttribute {
  var $link; // hypertext link
  var $ordered;
  var $precond; // pre-condition to activate menu

  function MenuAttribute($id, $docid, $label, $order, $link, $visibility="", $precond="" ) {
    $this->id=$id;
    $this->docid=$docid;
    $this->labelText=$label;
    $this->ordered=$order;
    $this->link=$link;
    $this->visibility=$visibility;

  }

}

Class ActionAttribute extends BasicAttribute {

  var $wapplication; // the what application name
  var $waction; // the what action name
  var $ordered;
  function ActionAttribute($id, $docid, $label, $order,$visibility="",$wapplication="",$waction="" ) {
    $this->id=$id;
    $this->docid=$docid;
    $this->labelText=$label;
    $this->visibility=$visibility;
    $this->waction=$waction;
    $this->wapplication=$wapplication;
  }
}
?>
