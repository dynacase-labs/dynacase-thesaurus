<?php

include_once("FDL/Class.DocAttribute.php");
Class ADoc  {

   function ADoc () {
     $this->attr["FIELD_HIDDENS"] = new FieldSetAttribute("FIELD_HIDDENS",0, "hiddens");
   }

   function getAttr($id) {
     if (isset($this->attr[$id])) return $this->attr[$id];
     if (isset($this->attr[strtolower($id)])) return $this->attr[$id];
     
     return false;
   }

   function GetNormalAttributes()
    {      
      $tsa=array();
      if (isset($this->attr)) {
	reset($this->attr);
	while (list($k,$v) = each($this->attr)) {
	  if (get_class($v) == "normalattribute")  $tsa[$v->id]=$v;
	}
      }
      return $tsa;      
    } 

   function getArrayElements($id) {
     $a = $this->getAttr($id);
     if ($a && ($a->type == "array")) {
       $tsa=$this->GetNormalAttributes();
       $ta=array();
       while (list($k, $v) = each($tsa)) {
	 if ($v->fieldSet->id == $id) $ta[$v->id]=$v;
       }
       return $ta;
       
     }
     return false;
   }

   
}
?>
