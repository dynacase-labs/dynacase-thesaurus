
// ---------------------------------------------------------------
// $Id: Method.DetailSearch.php,v 1.8 2003/06/16 12:00:35 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Freedom/Method.DetailSearch.php,v $
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
var $defaultview= "FREEDOM:VIEWDSEARCH";

var $tfunc=array("~*" => "include",         #N_("include")
		 "=" => "equal",            #N_("equal")
		 "!=" => "not equal",       #N_("not equal")
		 "!~*" => "not include",       #N_("not equal")
		 ">" => "&gt;",       #N_("not equal")
		 "<" => "&lt;",       #N_("not equal")
		 ">=" => "&gt; or equal",       #N_("&gt; or equal")
		 "<=" => "&lt; or equal");   #N_("&lt; or equal")
var $tol=array("and" => "and",              #N_("and")
	       "or" => "or");               #N_("or")


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
 
  $tol = $this->getTValue("SE_OLS");
  $tkey = $this->getTValue("SE_KEYS");
  $taid = $this->getTValue("SE_ATTRIDS");
  $tf = $this->getTValue("SE_FUNCS");
  
  $cond="";
  $tol[0]="";
  if ((count($tkey) > 1) || ($tkey[0] != "")) {
    while(list($k,$v) = each($tkey)) {
      $cond .= $tol[$k]." ".$taid[$k]." ".trim($tf[$k])." '".trim($tkey[$k])."' ";
    }
  }


  if ($cond != "") $filters[]=$cond;


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

function viewdsearch($target="_self",$ulink=true,$abstract=false) {
  // Compute value to be inserted in a  layout
   $this->viewattr($target,$ulink, $abstract);
  //-----------------------------------------------
  // display already condition written
  $tol = $this->getTValue("SE_OLS");
  $tkey = $this->getTValue("SE_KEYS");
  $taid = $this->getTValue("SE_ATTRIDS");
  $tf = $this->getTValue("SE_FUNCS");

  if ((count($tkey) > 1) || ($tkey[0] != "")) {

  $fdoc=new Doc($this->dbaccess, $this->getValue("SE_FAMID",1));
  $zpi=$fdoc->GetNormalAttributes();
  
  $tol[0]=" ";
    while(list($k,$v) = each($tkey)) {
      $tcond[]["condition"]=sprintf("%s %s %s %s",
				    _($tol[$k]),
				    $zpi[$taid[$k]]->labelText,
				    _($this->tfunc[$tf[$k]]),
				    $tkey[$k]);
				    
    }
    $this->lay->SetBlockData("COND", $tcond);
  }
}
  // -----------------------------------

function editdsearch() {
  global $action;
  // -----------------------------------

  $famid = GetHttpVars("sfamid",$this->getValue("SE_FAMID",1));
  $dirid = GetHttpVars("dirid");

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
  $this->lay->Set("dirid",$dirid);
  $this->lay->Set("classid",$this->fromid);
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
		   "funcname" => _($v));
  }
  $this->lay->SetBlockData("FUNC", $tfunc);
  $this->lay->SetBlockData("FUNC2", $tfunc);

  while (list($k,$v) = each($this->tol)) {
    $tol[]=array("olid"=> $k,
		 "olname" => _($v));
  }
  $this->lay->SetBlockData("OL", $tol);
  $this->lay->SetBlockData("OL2", $tol);


  if ($this->getValue("SE_LATEST" == "no"))     $this->lay->Set("select_all","selected");
  else $this->lay->Set("select_all","");


  //-----------------------------------------------
  // display state
  if ($fdoc->wid > 0) {
    $wdoc=new Doc ($this->dbaccess, $fdoc->wid);
    $states=$wdoc->getStates();

    $tstates=array();
    while(list($k,$v) = each($states)) {
      $tstates[] = array("stateid"=>$v,
			 "statename"=>_($v));
    }
    $this->lay->SetBlockData("STATE",$tstates );
    $this->lay->Set("dstate","inline" );
  } else {
    $this->lay->Set("dstate","none" );
  }

  //-----------------------------------------------
  // display already condition written
  $tol = $this->getTValue("SE_OLS");
  $tkey = $this->getTValue("SE_KEYS");
  $taid = $this->getTValue("SE_ATTRIDS");
  $tf = $this->getTValue("SE_FUNCS");
  
  $cond="";
 
  $tcond=array();
  reset($tkey);


  if ((count($tkey) > 1) || ($tkey[0] != "")) {

    while(list($k,$v) = each($tkey)) {
      $tcond[$k]= array("OLCOND"   => "olcond$k",
			"ATTRCOND" => "attrcond$k",
			"FUNCCOND" => "funccond$k",
			"KEYCOND" => "keycond$k",
			"STATECOND" => "statecond$k",
			"SSTATE" => "sstate$k",
			"key" => $v);
    
      $tattr=array();
      if ($taid[$k]=="state") {
	$this->lay->SetBlockData("statecond$k", array(array("boo")));
	reset($states);
	$tstates=array();
	while(list($ks,$vs) = each($states)) {
	  $tstates[] = array("sstateid"=>$vs,
			     "sstate_selected" => ($vs==$v)?"selected":"",
			     "sstatename"=>_($vs));
	}
	$this->lay->SetBlockData("sstate$k",$tstates );
	$tattr[]=array("attr_id"=> $taid[$k],
		       "attr_selected" => "selected",
		       "attr_name" => _("state"));
      } else {
	$this->lay->SetBlockData("keycond$k", array(array("boo")));
	reset($zpi);

	while (list($ki,$vi) = each($zpi)) {
	  $tattr[]=array("attr_id"=> $vi->id,
			 "attr_selected" => ($taid[$k]==$vi->id)?"selected":"",
			 "attr_name" => $vi->labelText);
	}
      }
      $this->lay->SetBlockData("attrcond$k", $tattr);

      $tfunc=array();
      reset($this->tfunc);
      while (list($ki,$vi) = each($this->tfunc)) {
	$tfunc[]=array("func_id"=> $ki,
		       "func_selected" => ($tf[$k]==$ki)?"selected":"",
		       "func_name" => _($vi));
      }
      $this->lay->SetBlockData("funccond$k", $tfunc);

      $tols=array();
      reset($this->tol);
      while (list($ki,$vi) = each($this->tol)) {
    
	$tols[]=array("ol_id"=> $ki,
		      "ol_selected" => ($tol[$k]==$ki)?"selected":"",
		      "ol_name" => _($vi));
      }
      $this->lay->SetBlockData("olcond$k", $tols);

    }
  }
  if (count($tcond) > 0)  $this->lay->SetBlockData("CONDITIONS", $tcond);
  // Compute value to be inserted in a  layout


  $this->lay->Set("id", $this->id);
  $this->editattr();
}