
// ---------------------------------------------------------------
// $Id: Method.DetailSearch.php,v 1.1 2003/01/24 14:10:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Attic/Method.DetailSearch.php,v $
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


var $defaultedit= "FREEDOM:EDITDSEARCH";

var $tfunc=array("~*" => "include",
		 "=" => "equal",
		 "!=" => "not equal",
		 "!~*" => "not include");
var $tol=array("and" => "and",
	       "or" => "or");


function ComputeQuery($keyword="",$famid=-1,$latest=false,$sensitive=false,$dirid=-1) {
    
  if ($dirid > 0) {

    $cdirid = getRChildDirId($this->dbaccess, $dirid);
      
       
  } else $cdirid=0;;

  $filters=array();
  // if ($latest)       $filters[] = "locked != -1";
  $filters[] = "usefor = 'N'";
  $keyword= str_replace("^","£",$keyword);
  $keyword= str_replace("$","£",$keyword);
  if ($keyword != "") {
    if ($sensitive) $filters[] = "values ~ '$keyword' ";
    else $filters[] = "values ~* '$keyword' ";
  }
 
  $tol = explode("\n",$this->getValue("SE_OLS"));
  $tkey = explode("\n",$this->getValue("SE_KEYS"));
  $taid = explode("\n",$this->getValue("SE_ATTRIDS"));
  $tf = explode("\n",$this->getValue("SE_FUNCS"));
  
  $cond="";
  $tol[0]="";
  while(list($k,$v) = each($tkey)) {
    $cond .= $tol[$k]." ".$taid[$k]." ".trim($tf[$k])." '".trim($tkey[$k])."' ";
  }


  $filters[]=$cond;
  $query = getSqlSearchDoc($this->dbaccess, $cdirid, $famid, $filters,false,$latest);
  return $query;
}




function SpecRefresh() {
  if ($this->getValue("se_latest") != "") {
    $query=$this->ComputeQuery($this->getValue("se_key"),
			       $this->getValue("se_famid"),
			       $this->getValue("se_latest")=="yes",
			       $this->getValue("se_case")=="yes",
			       $this->getValue("se_idfld"));

    $this->AddQuery($query);
  }
}


function editdsearch() {
  global $action;
  // -----------------------------------

  $famid = GetHttpVars("sfamid",$this->getValue("SE_FAMID",1));

  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/edittable.js");
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FREEDOM/Layout/editdsearch.js");

  $tclassdoc=GetClassesDoc($this->dbaccess, $action->user->id);

  while (list($k,$cdoc)= each ($tclassdoc)) {
    $selectclass[$k]["idcdoc"]=$cdoc->initid;
    $selectclass[$k]["classname"]=$cdoc->title;
    if ($cdoc->initid == $famid) {
      $selectclass[$k]["selected"]="selected";
      $this->lay->set("selfam",$cdoc->title);
    } else $selectclass[$k]["selected"]="";
  }
  
  $this->lay->SetBlockData("SELECTCLASS", $selectclass);
  $this->setFamidInLayout();


  // display attributes
  $tattr=array();

  $fdoc=new Doc($this->dbaccess, $famid);
  $zpi=$fdoc->GetNormalAttributes();

  while (list($k,$v) = each($zpi)) {
    $tattr[]=array("attrid"=> $v->id,
		   "attrname" => $v->labelText);
  }
  $this->lay->SetBlockData("ATTR", $tattr);
  
  while (list($k,$v) = each($this->tfunc)) {
    $tfunc[]=array("funcid"=> $k,
		   "funcname" => $v);
  }
  $this->lay->SetBlockData("FUNC", $tfunc);

  while (list($k,$v) = each($this->tol)) {
    $tol[]=array("olid"=> $k,
		 "olname" => $v);
  }
  $this->lay->SetBlockData("OL", $tol);


  if ($this->getValue("SE_LATEST" == "no"))     $this->lay->Set("select_all","selected");
  else $this->lay->Set("select_all","");



  //-----------------------------------------------
  // display already condition written
  $tol = explode("\n",$this->getValue("SE_OLS"));
  $tkey = explode("\n",$this->getValue("SE_KEYS"));
  $taid = explode("\n",$this->getValue("SE_ATTRIDS"));
  $tf = explode("\n",$this->getValue("SE_FUNCS"));
  
  $cond="";
 
  $tcond=array();
  reset($tkey);

  return;

  if ((count($tkey) > 1) || ($tkey[0] != "")) {

    while(list($k,$v) = each($tkey)) {
      $tcond[$k]= array("OLCOND"   => "olcond$k",
			"ATTRCOND" => "attrcond$k",
			"FUNCCOND" => "funccond$k",
			"key" => $v);
    
      reset($zpi);
      $tattr=array();
      while (list($ki,$vi) = each($zpi)) {
	$tattr[]=array("attr_id"=> $vi->id,
		       "attr_selected" => ($taid[$k]==$vi->id)?"selected":"",
		       "attr_name" => $vi->labelText);
      }
      $this->lay->SetBlockData("attrcond$k", $tattr);

      $tfunc=array();
      reset($this->tfunc);
      while (list($ki,$vi) = each($this->tfunc)) {
	$tfunc[]=array("func_id"=> $ki,
		       "func_selected" => ($tf[$k]==$ki)?"selected":"",
		       "func_name" => $vi);
      }
      $this->lay->SetBlockData("funccond$k", $tfunc);

      $tols=array();
      reset($this->tol);
      while (list($ki,$vi) = each($this->tol)) {
    
	$tols[]=array("ol_id"=> $ki,
		      "ol_selected" => ($tol[$k]==$ki)?"selected":"",
		      "ol_name" => $vi);
      }
      $this->lay->SetBlockData("olcond$k", $tols);

    }
  }
  if (count($tcond) > 0)  $this->lay->SetBlockData("CONDITIONS", $tcond);
  // Compute value to be inserted in a  layout


  $this->editattr();
}