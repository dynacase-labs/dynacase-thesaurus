<?php

// ---------------------------------------------------------------
// $Id: Class.DocAttribute.php,v 1.1 2002/11/04 09:13:17 eric Exp $
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


$CLASS_DOCATTRIBUTE_PHP = '$Id: Class.DocAttribute.php,v 1.1 2002/11/04 09:13:17 eric Exp $';




Class BasicAttribute {
  var $id;
  var $labelText;

  function BasicAttribute($id, $label ) {
    $this->id=$id;
    $this->labelText=$label;
  }
}

Class NormalAttribute extends BasicAttribute {
  var $visibility; // W, R, H, O, M
  var $needed; // Y / N
  var $type; // text, longtext, date, file, ...
  var $isInTitle;
  var $isInAbstract;
  var $fieldSet; // field set object
  var $link; // hypertext link
  var $phpfile;
  var $phpfunc;
  var $elink; // extra link
  var $ordered;
  function NormalAttribute($id, $label, $type, $order, $link,
			   $visibility,$needed,$isInTitle,$isInAbstract,
			   &$fieldSet,$phpfile,$phpfunc,$elink) {
    $this->id=$id;
    $this->labelText=$label;
    $this->type=$type;
    $this->ordered=$order;
    $this->link=$link;
    $this->visibility=$visibility;
    $this->needed=$needed;
    $this->isInTitle =$isInTitle;
    $this->isInAbstract=$isInAbstract;
    $this->fieldSet=$fieldSet;
    $this->phpfile=$phpfile;
    $this->phpfunc=$phpfunc;
    $this->elink=$elink;
  }
}
Class FieldSetAttribute extends BasicAttribute {

}

Class MenuAttribute extends BasicAttribute {
  var $link; // hypertext link
  var $ordered;
  var $precond; // pre-condition to activate menu

  function MenuAttribute($id, $label, $order, $link) {
    $this->id=$id;
    $this->labelText=$label;
    $this->ordered=$order;
    $this->link=$link;
  }

}

?>
