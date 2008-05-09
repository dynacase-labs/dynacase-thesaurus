<?php
/**
 * Detailled search
 *
 * @author Anakeen 2000 
 * @version $Id: Method.DetailSearch.php,v 1.62 2008/05/09 10:12:26 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */




var $defaultedit= "FREEDOM:EDITDSEARCH";#N_("include") N_("equal") N_("equal") _("not equal") N_("is empty") N_("is not empty") N_("one value equal")
var $defaultview= "FREEDOM:VIEWDSEARCH"; #N_("not include") N_("begin by") N_("not equal") N_("&gt; or equal") N_("&lt; or equal") N_("one word equal") N_("content file word") N_("content file expression")

var $top=array(  
	       "~*"=>array("label"=>"include",
			   "type"=>array("text","longtext","htmltext","ifile","array","file","image")),
	       "@@"=>array("label"=>"content file word",
			   "type"=>array("file")),
	       "~@"=>array("label"=>"content file expression",
			   "type"=>array("file")),
	       "=" => array("label"=>"equal"),  
	       "~^" => array("label"=>"begin by",
			     "type"=>array("text","longtext")),            
	       "!=" => array("label"=>"not equal"),       
	       "!~*" => array("label"=>"not include",
			      "type"=>array("text","longtext","htmltext")),       
	       ">" => array("label"=>"&gt;",
			    "type"=>array("int","float","date","time","timestamp","money")),       
	       "<" => array("label"=>"&lt;",
			    "type"=>array("int","float","date","time","timestamp","money")),       
	       ">=" => array("label"=>"&gt; or equal",
			     "type"=>array("int","float","date","time","timestamp","money")),       
	       "<=" => array("label"=>"&lt; or equal",
			     "type"=>array("int","float","date","time","timestamp","money")),   
	       "is null" => array("label"=>"is empty"),   
	       "is not null" => array("label"=>"is not empty"),   
	       "~y" => array("label"=>"one word equal",
			     "type"=>array("array")));    
	       
public $tol=array("and" => "and",              #N_("and")
		  "or" => "or");               #N_("or")


/**
  * return sql query to search wanted document
  */
function ComputeQuery($keyword="",$famid=-1,$latest="yes",$sensitive=false,$dirid=-1, $subfolder=true) {
    
  if ($dirid > 0) {

      if ($subfolder)  $cdirid = getRChildDirId($this->dbaccess, $dirid);
      else $cdirid=$dirid;      
       
  } else $cdirid=0;;

  $filters=$this->getSqlGeneralFilters($keyword,$latest,$sensitive);

  $cond=$this->getSqlDetailFilter();
  if ($cond === false) return array(false);
  $distinct=false;
  if ($latest=="lastfixed") $distinct=true;
  if ($cond != "") $filters[]=$cond;
  $query = getSqlSearchDoc($this->dbaccess, $cdirid, $famid, $filters,$distinct,$latest=="yes",$this->getValue("se_trash"),false);

  return $query;
 }
/**
 */
function getSqlCond($col,$op,$val="") {
  
  switch($op) {
  case "is null":
    $oa=$this->searchfam->getAttribute($col);
    $atype=$oa->type;
  case "int":
  case "double":
  case "money":
    $cond = sprintf(" (%s is null or %s = 0) ",$col,$col);
    break;
    switch ($atype) {
    case "date":
    case "time":
      $cond = sprintf(" (%s is null) ",$col);
      break;
    default:
      $cond = sprintf(" (%s is null or %s = '') ",$col,$col);
    }
    break;
  case "is not null":
    $cond = " ".$col." ".trim($op)." ";
    break;
  case "~*":
    if (trim($val) != "") $cond .= " ".$col." ".trim($op)." '".pg_escape_string(trim($val))."' ";
    break;
  case "~^":
    if (trim($val) != "") $cond .= " ".$col."~* '^".pg_escape_string(trim($val))."' ";
    break;
  case "~y":
    if (! is_array($val)) $val=$this->_val2array($val);
    if (count($val) > 0) $cond .= " ".$col." ~ '\\\\y(".pg_escape_string(implode('|',$val)).")\\\\y' ";
	
    break;
  case "~@":	
    if (trim($val) != "") {
      $cond .= " ".$col.'_txt'." ~ '".strtolower($val)."' ";	
    }
    break;
  case "@@":
    if (trim($val) != "") {
      $tstatickeys=explode(' ',$val);
      if (count($tstatickeys) > 1) {
	$keyword.= str_replace(" ","&",trim($val));
      } else {
	$keyword=trim($val);
      }
      $cond .= " ".$col.'_vec'." @@ to_tsquery('fr','.".unaccent(strtolower($keyword))."') ";
    }	
    break;
  default:
    $oa=$this->searchfam->getAttribute($col);
    $atype=$oa->type;
    switch ($atype) {
    case "enum": 
      $enum = $oa->getEnum(); 
      if (strrpos($val,'.') !== false)   $val = substr($val,strrpos($val,'.')+1); 
      $tkids=array();;
      foreach($enum as $k=>$v) {
	if (in_array($val,explode(".",$k))) {
	  $tkids[] = substr($k,strrpos(".".$k,'.'));
	}
      }

      if ($op=='=') {
	if ($oa->repeat) {
	  $cond .= " ".$col." ~ '\\\\y(".pg_escape_string(implode('|',$tkids)).")\\\\y' ";
	} else {
	  $cond .= " $col='". implode("' or $col='",$tkids)."'";    
	}
      } elseif ($op=='!=') {
	if ($oa->repeat) {
	  $cond1 = " ".$col." !~ '\\\\y(".pg_escape_string(implode('|',$tkids)).")\\\\y' ";

	} else {
	  $cond1 = " $col !='". implode("' and $col != '",$tkids)."'";    
	}
	$cond= "($cond1) or ($col is null)";
      }

      break;
    default:
      $cond .= " ".$col." ".trim($op)." '".pg_escape_string(trim($val))."' ";
    }
    
      
  }
  return $cond;
}

/**
 * return array of sql filter needed to search wanted document
 */
function getSqlDetailFilter() {  
  $ol = $this->getValue("SE_OL");  
  $tkey = $this->getTValue("SE_KEYS");
  $taid = $this->getTValue("SE_ATTRIDS");
  $tf = $this->getTValue("SE_FUNCS");
  
  if ($ol == "") {
    // try in old version
    $ols=$this->getTValue("SE_OLS");  
    $ol=$ols[1];
    if ($ol) {
      $this->setValue("SE_OL",$ol);
      $this->modify();
    }
  }
  if ($ol == "") $ol="and";
  $cond="";
  if ((count($taid) > 1) || ($taid[0] != "")) {
    if (! $this->searchfam) {
      $this->searchfam=new_doc($this->dbaccess,$this->getValue("se_famid"));
    }
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
	if ($rv == "-") return (false);
	$tkey[$k]=$rv;
      }
      if ($taid[$k] == "revdate") {
	list($dd,$mm,$yyyy) = explode("/",$tkey[$k]);
	if ($yyyy > 0) $tkey[$k]=mktime (0,0,0,$mm,$dd,$yyyy);
      }
    }
    
    foreach ($taid as $k=>$v) {
      $cond1=$this->getSqlCond($taid[$k],trim($tf[$k]),$tkey[$k]);
      if ($cond == "") $cond=$cond1." ";
      elseif ($cond1!="") $cond.=$ol.$cond1." ";

    }
  }

  if (trim($cond)=="") $cond="true";
  return $cond;
}

/**
 * return true if the search has parameters
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
 * return true if the search need parameters
 */
function needParameters() {
  $tkey = $this->getTValue("SE_KEYS");

  if ((count($tkey) > 1) || ($tkey[0] != "")) {

    foreach ($tkey as $k=>$v) {
     
       if ($v[0]=='?') {
	 if (getHttpVars(substr($v,1),"-") == "-") return true;
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
   $this->viewattr();
  //-----------------------------------------------
  // display already condition written
  
  $tkey = $this->getTValue("SE_KEYS");
  $taid = $this->getTValue("SE_ATTRIDS");
  $tf = $this->getTValue("SE_FUNCS");

  if ((count($taid) > 1) || ($taid[0] != "")) {

    $fdoc=new_Doc($this->dbaccess, $this->getValue("SE_FAMID",1));
    $zpi=$fdoc->GetNormalAttributes();
    $zpi["state"]->labelText=_("state");
    $zpi["title"]->labelText=_("doctitle");
    $zpi["revdate"]->labelText=_("revdate");
    $zpi["owner"]->labelText=_("id owner");
    $zpi["svalues"]->labelText=_("any values");
  
    foreach ($taid as $k=>$v) {
      $label=$zpi[$taid[$k]]->labelText;
      if ($label=="") $label=$taid[$k];
      $tcond[]["condition"]=sprintf("%s %s %s",				    
				    $label,
				    _($this->top[$tf[$k]]["label"]),
 				    ($tkey[$k]!="")?_($tkey[$k]):$tkey[$k]);
       if ($tkey[$k][0]=='?') {
 	$tparm[substr($tkey[$k],1)]=$taid[$k];
       }				    
    }
    $this->lay->SetBlockData("COND", $tcond);
  }
  $this->lay->Set("ddetail", "");

}


function paramdsearch($target="_self",$ulink=true,$abstract=false) {
  // Compute value to be inserted in a  layout
   $this->viewattr();
  //-----------------------------------------------
  // display already condition written
  
  $tkey = $this->getTValue("SE_KEYS");
  $taid = $this->getTValue("SE_ATTRIDS");
  $tf = $this->getTValue("SE_FUNCS");

  if ((count($taid) > 1) || ($taid[0] != "")) {

    $fdoc=new_Doc($this->dbaccess, $this->getValue("SE_FAMID",1));
    $zpi=$fdoc->GetNormalAttributes();
    $zpi["state"]->labelText=_("state");
    $zpi["title"]->labelText=_("doctitle");
    $zpi["revdate"]->labelText=_("revdate");
    $zpi["owner"]->labelText=_("id owner");
    $zpi["svalues"]->labelText=_("any values");
  
    foreach ($taid as $k=>$v) {      
       if ($tkey[$k][0]=='?') {
	 $tparm[substr($tkey[$k],1)]=$taid[$k];
       }				    
    }
    $this->lay->SetBlockData("COND", $tcond);
  }
 
  $this->lay->Set("ddetail", "");
  if (count($tparm) > 0) {
    include_once("FDL/editutil.php");
    global $action;
    editmode($action);

    $doc= createDoc($this->dbaccess,$this->getValue("SE_FAMID",1),false);
    foreach ($tparm as $k=>$v) {
       
     $ttransfert[]=array("idi"=>$v,
			 "idp"=>$k,
			 "value"=>getHttpVars($k));
     $tinputs[$k]["label"]=$zpi[$v]->labelText;
     if ($zpi[$v]->visibility=='R') $zpi[$v]->mvisibility='W';
     if ($zpi[$v]->visibility=='S') $zpi[$v]->mvisibility='W';
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
    $this->lay->set("stext",_("send search"));
    $this->lay->set("saction",getHttpVars("saction","FREEDOM_VIEW"));
    $this->lay->set("sapp",getHttpVars("sapp","FREEDOM"));
    $this->lay->set("sid",getHttpVars("sid","dirid"));
    $this->lay->set("starget",getHttpVars("starget",""));    
    $this->lay->set("icon",$this->getIcon());    
  } 
}
  // -----------------------------------

function editdsearch() {
  global $action;
  // -----------------------------------

  $famid = GetHttpVars("sfamid",$this->getValue("SE_FAMID",1));
  $onlysubfam = GetHttpVars("onlysubfam"); // restricy to sub fam of
  $dirid = GetHttpVars("dirid");
  $this->lay->set("ACTION",$action->name);

  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/edittable.js");
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FREEDOM/Layout/editdsearch.js");


  if ($dirid > 0) {
      $dir = new_Doc($this->dbaccess, $dirid);
      if (method_exists($dir,"isAuthorized")) {	
	if ($dir->isAuthorized($classid)) { 
	  // verify if classid is possible
	  if ($dir->norestrict) $tclassdoc=GetClassesDoc($this->dbaccess, $action->user->id,$classid,"TABLE");
	  else {
	    $tclassdoc=$dir->getAuthorizedFamilies();
	    $this->lay->set("restrict",true);
	  }
	} else  {
	  $tclassdoc=$dir->getAuthorizedFamilies();
	  $first = current($tclassdoc);
	  $famid1 = ($first["id"]);
	  $this->lay->set("restrict",true);
	  $tfamids=array_keys($tclassdoc);
	  if (! in_array($famid,$tfamids)) $famid=$famid1;
	}
      }
      else {
	$tclassdoc = GetClassesDoc($this->dbaccess, $action->user->id,$classid,"TABLE");
      }
    } else {
    if ($onlysubfam) {	
      $alsosub=true;
	if (! is_numeric($onlysubfam))  $onlysubfam = getFamIdFromName($this->dbaccess,$onlysubfam);
	$cdoc = new_Doc($this->dbaccess,$onlysubfam);
	$tsub=$cdoc->GetChildFam($cdoc->id,false);
	if ($alsosub) {
	  $tclassdoc[$classid] = array("id"=>$cdoc->id ,
				       "title"=>$cdoc->title);
	  $tclassdoc = array_merge($tclassdoc,$tsub);
	} else {
	  $tclassdoc=$tsub;
	}
	$first = current($tclassdoc);
	if ($classid=="") $classid = $first["id"];

      } else  $tclassdoc = GetClassesDoc($this->dbaccess, $action->user->id,$classid,"TABLE");
    }


  $this->lay->set("onlysubfam",$onlysubfam);
  foreach ($tclassdoc as $k=>$cdoc) {
    $selectclass[$k]["idcdoc"]=$cdoc["id"];
    $selectclass[$k]["classname"]=$cdoc["title"];
    if (abs($cdoc["id"]) == abs($famid)) {
      $selectclass[$k]["selected"]="selected";
      if ($famid < 0)	$this->lay->set("selfam",$cdoc["title"]." "._("(only)"));
      else $this->lay->set("selfam",$cdoc["title"]);
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
		   "owner" => _("id owner"),
		   "svalues"=> _("any values"));
  
  while (list($k,$v) = each($internals)) {
    if ($k=="revdate") $type="date";
    else if ($k=="owner") $type="docid";
    else $type="text";			    
    $tattr[]=array("attrid"=> $k,
		   "attrtype"=>$type,
		   "attrname" => $v);
  }

  $fdoc=new_Doc($this->dbaccess, abs($famid));
  $zpi=$fdoc->GetNormalAttributes();
  foreach($zpi as $k=>$v) {
    if ($v->type == "array") continue;
    if ($v->inArray() && ($v->type!='file')) $type="array";
    else $type=$v->type;
    $tattr[]=array("attrid"=> $v->id,
		   "attrtype"=>$type,
		   "attrname" => $v->labelText);
  }
  $this->lay->SetBlockData("ATTR", $tattr);
  
  foreach($this->top as $k=>$v) {
    $display='';
    if (isset($v["type"])) {
      $ctype=implode(",",$v["type"]);
      if (! in_array('text',$v["type"])) $display='none'; // first is title
    } else $ctype="";
       
    
    $tfunc[]=array("funcid"=> $k,
		   "functype"=>$ctype,
		   "funcdisplay"=>$display,
		   "funcname" => _($v["label"]));
  }
  $this->lay->SetBlockData("FUNC", $tfunc);

  foreach ($tfunc as $k=>$v) {
    if (($v["functype"])!="") unset($tfunc[$k]);
  }
  $this->lay->SetBlockData("FUNCSTATE", $tfunc);
  $this->lay->Set("icon",$fdoc->getIcon());


  if ($this->getValue("SE_LATEST") == "no")     $this->lay->Set("select_all","selected");
  else $this->lay->Set("select_all","");


  //-----------------------------------------------
  // display state
  if ($fdoc->wid > 0) {
    $wdoc=new_Doc($this->dbaccess, $fdoc->wid);
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

  if ((count($taid) > 1) || ($taid[0] != "")) {
    foreach($taid as $k=>$va) {
      $v=$tkey[$k];
      $oa=$fdoc->getAttribute($taid[$k]);
      $tcond[$k]= array("OLCOND"   => "olcond$k",
			"ATTRCOND" => "attrcond$k",
			"FUNCCOND" => "funccond$k",
			"ISENUM" => (($taid[$k]=="state")||($oa->type=="enum")),
			"SSTATE" => "sstate$k",
			"key" => $v);
    
      $tattr=array();
       if ($taid[$k]=="state") {
	$tstates=array();
	$stateselected=false;
	foreach($states as $ks=>$vs) {
	  $tstates[] = array("sstateid"=>$vs,
			     "sstate_selected" => ($vs==$v)?"selected":"",
			     "sstatename"=>_($vs));
	  if ($vs==$v) $stateselected=true;
	}
	if (! $stateselected) $tcond[$k]["ISENUM"]=false;
	$this->lay->SetBlockData("sstate$k",$tstates );
	$tattr[]=array("attr_id"=> $taid[$k],
		       "attr_selected" => "selected",
		       "attr_name" => _("state"));
       } else {
	 if ($oa->type=="enum") {	
	   $te=$oa->getEnum();
	   $tstates=array();
	   $enumselected=false;
	   foreach ($te as $ks=>$vs) {
	     $tstates[] = array("sstateid"=>$ks,
				"sstate_selected" => ($ks==$v)?"selected":"",
				"sstatename"=>$vs);
	     if ($ks==$v) $enumselected=true;
	   }
	   $this->lay->SetBlockData("sstate$k",$tstates );
	   if (! $enumselected) $tcond[$k]["ISENUM"]=false;	
	 }

	 foreach($internals as $ki=>$vi) {
	   if ($ki=="revdate") $type="date";
	   else if ($ki=="owner") $type="docid";
	   else $type="text";	   	   
	   $tattr[]=array("attr_id"=> $ki,
			  "attr_type"=>$type,
			  "attr_selected" => ($taid[$k]==$ki)?"selected":"",
			  "attr_name" => $vi);
	 }
	 foreach($zpi as $ki=>$vi) {
	   if ($vi->inArray() && ($vi->type!='file')) $type="array";
	   else $type=$vi->type;
	   $tattr[]=array("attr_id"=> $vi->id,
			  "attr_type"=>$type,
			  "attr_selected" => ($taid[$k]==$vi->id)?"selected":"",
			  "attr_name" => $vi->labelText);
	 }
       }
      $this->lay->SetBlockData("attrcond$k", $tattr);

      $tfunc=array();

      foreach($this->top as $ki=>$vi) {
	$oa=$fdoc->getAttribute($taid[$k]);
	$type=$oa->type;
	if ($type=="") {
	  if ($taid[$k]=="title") $type="text";
	  if ($taid[$k]=="revdate") $type="date";
	  if ($taid[$k]=="owner") $type="docid";
	  if ($taid[$k]=="svalues") $type="text";	  
	} else {
	  if ($oa->inArray() && ($oa->type!='file')) $type="array";
	}
	$display='';
	$ctype='';
	if (isset($vi["type"])) {
	  if (! in_array($type,$vi["type"])) $display='none';
	  $ctype=implode(",",$vi["type"]);
	}
	$tfunc[]=array("func_id"=> $ki,
		       "func_selected" => ($tf[$k]==$ki)?"selected":"",
		       "func_display"=>$display,
		       "func_type"=>$ctype,
		       "func_name" => _($vi["label"]));
      }
      $this->lay->SetBlockData("funccond$k", $tfunc);

      $tols=array();
      foreach($this->tol as $ki=>$vi) {    
	$tols[]=array("ol_id"=> $ki,
		      "ol_selected" => ($tol[$k]==$ki)?"selected":"",
		      "ol_name" => _($vi));
      }
      $this->lay->SetBlockData("olcond$k", $tols);

    }
  }
  if (count($tcond) > 0)  $this->lay->SetBlockData("CONDITIONS", $tcond);
  // Add select for enum attributes
  
  $tenums=array();
  foreach($zpi as $k=>$v) {
    if (($v->type == "enum")|| ($v->type == "enumlist")) {
      $tenums[]=array("SELENUM"=>"ENUM$k",
		      "attrid"=>$v->id);
      $tenum=$v->getEnum();
      $te=array();
      foreach ($tenum as $ke=>$ve) {
	$te[]=array("enumkey"=>$ke,
		    "enumlabel"=>$ve);
      }
      $this->lay->setBlockData("ENUM$k",$te);
    }
  }

  $this->lay->setBlockData("ENUMS",$tenums);

  $this->lay->Set("id", $this->id);
  $this->editattr();
}
?>