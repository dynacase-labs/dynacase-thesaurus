// ---------------------------------------------------------------
// $Id: Method.Action_impl.php,v 1.1 2003/06/27 07:40:45 mathieu Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Freedom/Method.Action_impl.php,v $
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

var $defaultedit="FREEDOM:EDIT_IMPL";

function edit_impl($target="finfo",$ulink=true,$abstract="Y") {
  global $action;
 include_once("FDL/editutil.php");
 //$action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FREEDOM/Layout/workflow.js");
 
 $this->lay->Set("famid",552);

  $this->lay->Set("docid", $this->id);
  $this->lay->Set("TITLE", $this->title);
  
  $title=$this->Getattribute("BA_TITLE");
  $this->lay->Set("name1",$title->labelText);
  $value = $this->GetValue($title->id);
  $this->lay->Set("inputtype1",getHtmlInput($this,$title,$value));
		  
		  
  $descrip=$this->Getattribute("AI_ACTION");
  $this->lay->Set("name2",$descrip->labelText);
  $value = $this->GetValue($descrip->id);
  $this->lay->Set("inputtype2",getHtmlInput($this,$descrip,$value));
		  
  $etat=$this->Getattribute("AI_ARGS");
  $this->lay->Set("name3",$etat->labelText);
  $value = $this->GetValue($etat->id);
  $this->lay->Set("inputtype3",getHtmlInput($this,$etat,$value));

  $etat=$this->Getattribute("AI_IDACTION");
  $value = $this->GetValue($etat->id);
  $this->lay->Set("inputtype4",getHtmlInput($this,$etat,$value));


		  
}