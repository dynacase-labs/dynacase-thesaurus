<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Method.Report.php,v 1.9 2004/03/16 14:10:07 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */
// ==========================================================================
// document to present a report on one family document

// Author          Eric Brison	(Anakeen)
// Date            jun, 12 2003 - 14:23:15
// Last Update     $Date: 2004/03/16 14:10:07 $
// Version         $Revision: 1.9 $
// ==========================================================================

//var $defDoctype='F';

var $defaultedit= "FREEDOM:EDITREPORT";
var $defaultview= "FREEDOM:VIEWREPORT";
function _getInternals() {
  return array("title" => _("doctitle"),
		      "revdate" => _("revdate"),
		      "revision" => _("revision"),
		      "state" => _("state"));
}
function editreport() {
  global $action;
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/selectbox.js");
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FREEDOM/Layout/editreport.js");

 
  $rfamid = GetHttpVars("sfamid",$this->getValue("SE_FAMID",1));
  $rdoc=createDoc($this->dbaccess, $rfamid,false);
  $lattr=$rdoc->GetNormalAttributes();
  $tcolumn1=array();
  $tcolumn2=array();
  while (list($k,$v) = each($lattr)) {
    //    if ($v->visibility=="H") continue;
    $tcolumn1[$v->id]=array("aid"=>$v->id,
			    "alabel"=>$v->labelText);
  }
  $tinternals = $this->_getInternals();

  // reconstruct internals for layout
  reset($tinternals);
  while (list($k,$v) = each($tinternals)) {
    $tinternals[$k]=array("aid"=>$k,
			  "alabel"=>$v);
  }
  
  // display selected column
  $tcols = $this->getTValue("REP_IDCOLS");

  while (list($k,$v) = each($tcols)) {
    if (isset($tcolumn1[$v])) {
      $tcolumn2[$v]=$tcolumn1[$v];
      unset($tcolumn1[$v]);
    }
    if (isset($tinternals[$v])) {
      $tcolumn2[$v]=$tinternals[$v];
      unset($tinternals[$v]);
    }
  }

  $this->lay->setBlockData("COLUMN1",$tcolumn1);  
  $this->lay->setBlockData("INTERNALS",$tinternals);  

  $this->lay->setBlockData("COLUMN2",$tcolumn2);

}
function viewreport($target="_self",$ulink=true,$abstract=false) {
  $this->viewattr($target,$ulink, $abstract);

  // --------------------------
  // display headers column  
  $rfamid = $this->getValue("SE_FAMID",1);
  $rdoc=createDoc($this->dbaccess, $rfamid, false);
  $lattr=$rdoc->GetNormalAttributes();
  $tcolumn1=array();
  $tcolumn2=array();
  while (list($k,$v) = each($lattr)) {
    //    if ($v->visibility=="H") continue;
    $tcolumn1[$v->id]=array("colid"=>$v->id,
			    "collabel"=>$v->labelText,
			    "rightfornumber"=>($v->type == "money")?"right":"left");
  }

  $tinternals = $this->_getInternals();
  while (list($k,$v) = each($tinternals)) {
   
    $tcolumn1[$k]=array("colid"=>$k,
			    "collabel"=>$v,
			    "rightfornumber"=>"left");
  }
  

  $tcols = $this->getTValue("REP_IDCOLS");
  while (list($k,$v) = each($tcols)) {
    $tcolumn2[$v]=$tcolumn1[$v];
    
  }
  $this->lay->setBlockData("COLS",$tcolumn2);
  include_once("FDL/Lib.Dir.php");
   

  $this->lay->set("reportstyle",$this->getValue("REP_STYLE","reportHBlue"));
  
  // --------------------------
  // display body
  $order=$this->getValue("REP_IDSORT","title");
  $tdoc = getChildDoc($this->dbaccess, $this->initid,0,"ALL",array(),$this->userid,"TABLE",$rfamid,false,$order);
  $trodd=false;
  $tcolor= $this->getTValue("REP_COLORS");
  $trow=array();
  while (list($k,$v) = each($tdoc)) {
    $rdoc->Affect($v);
    $trow[$k]=array("CELLS"=>"row$k",
		    "docid" => $rdoc->id,
		    "troddoreven"=>$trodd?"trodd":"treven");
    $trodd=!$trodd;
    $tdodd=false;
    $tcell=array();
    reset($tcolumn2);
    reset($tcolor);
    while (list($kc,$vc) = each($tcolumn2)) {
      if ($v[$kc] == "") $tcell[$kc]=array("cellval"=>"");
      else {
	switch ($kc) {
	  case "revdate" :
	    $cval = strftime ("%x %T",$v[$kc]);
	  break;
	  case "state" :
	    $cval = _($v[$kc]);
	  break;
	    
	default:
	  $cval = $rdoc->getHtmlValue($lattr[$kc],$v[$kc],$target,$ulink);
	  if ($lattr[$kc]->type == "image") $cval="<img width=\"40px\" src=\"$cval\">";
	
	}
	$tcell[$kc]=array("cellval"=>$cval,
			  "rawval"=>$v[$kc]);
      }
      $tcell[$kc]["bgcell"]=current($tcolor);next($tcolor);
      $tcell[$kc]["tdoddoreven"]=$tdodd?"tdodd":"tdeven";
      $tcell[$kc]["rightfornumber"]=($lattr[$kc]->type == "money")?"right":"left";
      $tdodd=!$tdodd;
      
    }
    $this->lay->setBlockData("row$k",$tcell);
    
    
  }
  $this->lay->setBlockData("ROWS",$trow);
  // ---------------------
  // footer

  $tfoots = $this->getTValue("REP_FOOTS");
  while (list($k,$v) = each($tfoots)) {
    switch ($v) {
    case "CARD":
      $val = count($trow);
      break;
    case "MOY":
    case "SUM":
      reset($trow);
      $val=0;
       while (list($kr,$vr) = each($trow)) {
	 $ctr = $this->lay->getBlockData($vr["CELLS"]);
	 
	 $val += $ctr[$tcols[$k]]["rawval"];
       }
       if ($v == "MOY") $val = $val/count($trow);
       $val = $rdoc->getHtmlValue($lattr[$tcols[$k]],$val,$target,$ulink);            
      break;
    
    default:
      $val="-";
    }
    $tlfoots[]=array("footval"=>$val,
		     "rightfornumber"=>$tcolumn2[$tcols[$k]]["rightfornumber"]);
    
  }
  $this->lay->setBlockData("TFOOT",$tlfoots);
}
// EOF
?>