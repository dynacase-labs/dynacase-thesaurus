<?php
/**
 * Detailled search
 *
 * @author Anakeen 2000 
 * @version $Id: Method.DetailSearch.php,v 1.27 2004/10/08 07:45:11 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */




var $defaultedit= "FREEDOM:EDITDSEARCH";
var $defaultview= "FREEDOM:VIEWDSEARCH";

var $tfunc=array("~*" => "include",         #N_("include")
		 "=" => "equal",            #N_("equal")
		 "!=" => "not equal",       #N_("not equal")
		 "!~*" => "not include",       #N_("not include")
		 ">" => "&gt;",       #N_("not equal")
		 "<" => "&lt;",       #N_("not equal")
		 ">=" => "&gt; or equal",       #N_("&gt; or equal")
		 "<=" => "&lt; or equal",   #N_("&lt; or equal")
		 "is null" => "is empty",   #N_("is empty")
		 "is not null" => "is not empty");       #N_("is not empty")
var $tol=array("and" => "and",              #N_("and")
	       "or" => "or");               #N_("or")


function ComputeQuery($keyword="",$famid=-1,$latest="yes",$sensitive=false,$dirid=-1, $subfolder=true) {
    
  if ($dirid > 0) {

      if ($subfolder)  $cdirid = getRChildDirId($this->dbaccess, $dirid);
      else $cdirid=$dirid;      
       
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
  $distinct=false;
  if ($latest == "fixed") {
    $filters[] = "locked = -1";
    $filters[] = "lmodify = 'L'";   
  }

  $tol = $this->getTValue("SE_OLS");
  $tkey = $this->getTValue("SE_KEYS");
  $taid = $this->getTValue("SE_ATTRIDS");
  $tf = $this->getTValue("SE_FUNCS");
  
  $cond="";
  if ((count($taid) > 1) || ($taid[0] != "")) {
    // special loop for revdate
    foreach($tkey as $k=>$v) {
      if (strtolower(substr($v,0,5))=="::get") { // only get method allowed
	// it's method call
	$rv = $this->ApplyMethod($v);
	$tkey[$k]=$rv;
      }
      if (substr($v,0,1)=="?") {
	// it's a parameter
	$rv = getHttpVars(substr($v,1),"-");
	if ($rv == "-") return array(false);
	$tkey[$k]=$rv;
      }
      if ($taid[$k] == "revdate") {
	list($dd,$mm,$yyyy) = explode("/",$tkey[$k]);
	$tkey[$k]=mktime (0,0,0,$mm,$dd,$yyyy);
      }
    }
    
    foreach ($tol as $k=>$v) {
      if ($cond == "") $tol[$k]="";;
      switch($tf[$k]) {
      case "is null":
	$cond .= $tol[$k].sprintf(" (%s is null or %s = '') ",$taid[$k],$taid[$k]);
	break;
      case "is not null":
	$cond .= $tol[$k]." ".$taid[$k]." ".trim($tf[$k])." ";
	break;
      case "~*":
	if ($tkey[$k] != "") $cond .= $tol[$k]." ".$taid[$k]." ".trim($tf[$k])." '".pg_escape_string(trim($tkey[$k]))."' ";
	break;
      default:
	$cond .= $tol[$k]." ".$taid[$k]." ".trim($tf[$k])." '".pg_escape_string(trim($tkey[$k]))."' ";
      
      }
    }
  }


  if ($cond != "") $filters[]=$cond;


  $query = getSqlSearchDoc($this->dbaccess, $cdirid, $famid, $filters,$distinct,$latest=="yes");

  return $query;
}

/**
 * return true if the search need parameters
 */
function isParameterizable() {
  $tkey = $this->getTValue("SE_KEYS");

  if ((count($tkey) > 1) || ($tkey[0] != "")) {

    foreach ($tkey as $k=>$v) {
     
       if ($v[0]=='?') {
	 return true;
	 //if (getHttpVars(substr($v,1),"-") == "-") return true;
       }
				    
    }
  }
  return false;
}

/**
 * Add parameters 
 */
function urlWhatEncodeSpec($l) {
  $tkey = $this->getTValue("SE_KEYS");

  if ((count($tkey) > 1) || ($tkey[0] != "")) {

    foreach ($tkey as $k=>$v) {
     
       if ($v[0]=='?') {
	 if (getHttpVars(substr($v,1),"-") != "-") {
	   $l.='&'.substr($v,1)."=".getHttpVars(substr($v,1));
	 }
       }
				    
    }
  }
  
  return $l;
}

/**
 * add parameters in title
 */
function getSpecTitle() {
  $tkey = $this->getTValue("SE_KEYS");
  $l="";
  if ((count($tkey) > 1) || ($tkey[0] != "")) {
    $tl=array();
    foreach ($tkey as $k=>$v) {
     
       if ($v[0]=='?') {
	 $vh=getHttpVars(substr($v,1),"-");
	 if (($vh != "-") && ($vh != "")) {
	   $tl[]= getHttpVars(substr($v,1));
	 }
       }
				    
    }
    if (count($tl)> 0) {
      $l=" (".implode(", ",$tl).")";
    }
  }
  return $this->title.$l;
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
    $zpi["state"]->labelText=_("state");
    $zpi["title"]->labelText=_("doctitle");
    $zpi["revdate"]->labelText=_("revdate");
  
    $tol[0]=" ";
    foreach ($tkey as $k=>$v) {
      $tcond[]["condition"]=sprintf("%s %s %s %s",
				    _($tol[$k]),
				    $zpi[$taid[$k]]->labelText,
				    _($this->tfunc[$tf[$k]]),
 				    ($tkey[$k]!="")?_($tkey[$k]):$tkey[$k]);
       if ($v[0]=='?') {
 	$tparm[substr($v,1)]=$taid[$k];
       }
				    
    }
    $this->lay->SetBlockData("COND", $tcond);
  }
  $this->lay->Set("ddetail", "");
  if (count($tparm) > 0) {
    include_once("FDL/editutil.php");
    global $action;
    editmode($action);

    $doc= createDoc($this->dbaccess,$this->getValue("SE_FAMID",1));
    foreach ($tparm as $k=>$v) {
       
     $ttransfert[]=array("idi"=>$v,
			 "idp"=>$k,
			 "value"=>getHttpVars($k));
     $tinputs[$k]["label"]=$zpi[$v]->labelText;
     if ($zpi[$v]->visibility=='R') $zpi[$v]->mvisibility='W';
     if (isset($zpi[$v]->id)) {
       $zpi[$v]->isAlone=true;
       $tinputs[$k]["inputs"]=getHtmlInput($doc,$zpi[$v],getHttpVars($k));
     } else {
       $aotxt=new BasicAttribute($v,$doc->id,"eou");
       if ($v=="revdate") $aotxt->type="date";
       $tinputs[$k]["inputs"]=getHtmlInput($doc,$aotxt,getHttpVars($k));
     }
   }
    $this->lay->setBlockData("PARAM",$tinputs);
    $this->lay->setBlockData("TRANSFERT",$ttransfert);
    $this->lay->setBlockData("PINPUTS",$ttransfert);
    $this->lay->Set("ddetail", "none");
    $this->lay->setBlockData("VPARAM1",array(array("zou")));
    $this->lay->setBlockData("VPARAM2",array(array("zou")));
    $this->lay->setBlockData("VPARAM3",array(array("zou")));
    $this->lay->set("saction",getHttpVars("saction","FREEDOM_VIEW"));
    $this->lay->set("sapp",getHttpVars("sapp","FREEDOM"));
    $this->lay->set("sid",getHttpVars("sid","dirid"));
    $this->lay->set("starget",getHttpVars("starget","flist"));
    
  } 
}
  // -----------------------------------

function editdsearch() {
  global $action;
  // -----------------------------------

  $famid = GetHttpVars("sfamid",$this->getValue("SE_FAMID",1));
  $dirid = GetHttpVars("dirid");
  $this->lay->set("ACTION",$action->name);

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
  $internals=array("title" => _("doctitle"),
		   "revdate" => _("revdate"),
		   "values"=> _("any values"));
  
  while (list($k,$v) = each($internals)) {
    $tattr[]=array("attrid"=> $k,
		   "attrname" => $v);
  }

  $fdoc=new Doc($this->dbaccess, $famid);
  $zpi=$fdoc->GetNormalAttributes();

  while (list($k,$v) = each($zpi)) {
    if ($v->type == "array") continue;
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


  if ($this->getValue("SE_LATEST") == "no")     $this->lay->Set("select_all","selected");
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
	reset($internals);
	while (list($ki,$vi) = each($internals)) {
	  $tattr[]=array("attr_id"=> $ki,
			 "attr_selected" => ($taid[$k]==$ki)?"selected":"",
			 "attr_name" => $vi);
	}
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
?>