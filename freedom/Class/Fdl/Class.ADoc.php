<?php
/**
 * Attribute Document Object Definition
 *
 * @author Anakeen 2002
 * @version $Id: Class.ADoc.php,v 1.8 2005/06/28 13:53:13 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
/**
 */

include_once("FDL/Class.DocAttribute.php");
/**
 * Attribute Document Class
 *
 */
Class ADoc  {

   function ADoc () {
     $this->attr["FIELD_HIDDENS"] = new FieldSetAttribute("FIELD_HIDDENS",0, "hiddens");
   }

   function getAttr($id) {
     if (isset($this->attr[$id])) return $this->attr[$id];
     if (isset($this->attr[strtolower($id)])) return $this->attr[$id];
     
     return false;
   }

   function GetNormalAttributes($onlyopt=false)
    {      
      $tsa=array();
      if (isset($this->attr)) {
	reset($this->attr);
	while (list($k,$v) = each($this->attr)) {
	  if ((strtolower(get_class($v)) == "normalattribute") && 
	      ((($v->usefor != "Q")&&(!$onlyopt)) || (($v->usefor == "O")&&($onlyopt))))   $tsa[$v->id]=$v;
	}
      }
      return $tsa;      
    } 

   function getParamAttributes()
    {      
      $tsa=array();
      if (isset($this->attr)) {
	reset($this->attr);
	while (list($k,$v) = each($this->attr)) {
	  if ((strtolower(get_class($v)) == "normalattribute") && ($v->usefor == "Q")) $tsa[$v->id]=$v;
	}
      }
      return $tsa;      
    } 
   function getArrayElements($id) {
     $a = $this->getAttr($id);
     if ($a && ($a->type == "array")) {
       if ($a->usefor != "Q") {
	 $tsa=$this->GetNormalAttributes();
       } else {
	 $tsa=$this->getParamAttributes();
       }
       $ta=array();
       foreach ($tsa as $k=>$v) {
	 if ($v->fieldSet->id == $id) $ta[$v->id]=$v;
       }
       return $ta;
       
     }
     return false;
   }

   
}
?>
