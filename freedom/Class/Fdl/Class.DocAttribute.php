<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Class.DocAttribute.php,v 1.13 2003/11/17 11:06:37 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


// ---------------------------------------------------------------
// $Id: Class.DocAttribute.php,v 1.13 2003/11/17 11:06:37 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Class.DocAttribute.php,v $
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


$CLASS_DOCATTRIBUTE_PHP = '$Id: Class.DocAttribute.php,v 1.13 2003/11/17 11:06:37 eric Exp $';




Class BasicAttribute {
  var $id;
  var $docid;
  var $labelText;
  var $visibility; // W, R, H, O, M

  function BasicAttribute($id, $docid, $label ) {
    $this->id=$id;
    $this->docid=$docid;
    $this->labelText=$label;
  }

  
   // to see if an attribute is n item of an array
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
  var $repeat; // true if is a repeatable attribute
  var $isInTitle;
  var $isInAbstract;
  var $fieldSet; // field set object
  var $link; // hypertext link
  var $phpfile;
  var $phpfunc;
  var $elink; // extra link
  var $ordered;
  function NormalAttribute($id, $docid, $label, $type, $format, $repeat, $order, $link,
			   $visibility,$needed,$isInTitle,$isInAbstract,
			   &$fieldSet,$phpfile,$phpfunc,$elink) {
    $this->id=$id;
    $this->docid=$docid;
    $this->labelText=$label;
    $this->type=$type;
    $this->format=$format;
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
    $this->repeat=$repeat || $this->inArray();


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
	$this->phpfunc = call_user_func($this->phpfunc);
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

  function getEnumLabel() {  
    global $__tlenum;

    $this->getEnum();
    if (isset($__tlenum[$this->id])) return $__tlenum[$this->id]; // not twice
    
  
  }
}


Class FieldSetAttribute extends BasicAttribute {

  function FieldSetAttribute($id, $docid, $label, $visibility="" ) {
    $this->id=$id;
    $this->docid=$docid;
    $this->labelText=$label;
    $this->visibility=$visibility;
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

?>
