
// ---------------------------------------------------------------
// $Id: Method.Mask.php,v 1.1 2003/03/05 16:49:28 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Freedom/Method.Mask.php,v $
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


var $defaultedit= "FREEDOM:EDITMASK";
var $defaultview= "FREEDOM:VIEWMASK";

function SpecRefresh() {
 
  //  gettitle(D,AR_IDCONST):AR_CONST,AR_IDCONST
  $this->refreshDocTitle("MSK_FAMID","MSK_FAM");

  
  return $err;
}

function getLabelVis() {
  return $labelvis = array("-" => " ",
		    "R" => _("read only"),
		    "W" => _("read write"),
		    "O" => _("write only"),
		    "H" => _("hidden"),
		    "S" => _("read disabled"));
}


function getVisibilities() {
  $tvisid = explode("\n",$this->getValue("MSK_VISIBILITIES"));
  $tattrid = explode("\n",$this->getValue("MSK_ATTRIDS"));

  $tvisibilities=array();
  while (list($k,$v)= each ($tattrid)) {
    $tvisibilities[$v]=$tvisid[$k];    
  }
  return $tvisibilities;
}

function viewmask($target="_self",$ulink=true,$abstract=false) {
 
  $docid = $this->getValue("MSK_FAMID",1);

  $tvisibilities=$this->getVisibilities();
  $this->lay->Set("docid",$docid);

  $doc= new Doc($this->dbaccess,$docid);


  // display current values
  $tmask=array();
  
 
  $labelvis = $this->getLabelVis();
  
    
 
		     

  //    ------------------------------------------
  //  -------------------- NORMAL ----------------------
  $tattr = $doc->GetNormalAttributes();
  
  uasort($tattr,"tordered"); 
  reset($tattr);
  while(list($k,$attr) = each($tattr))  {
    $tmask[$k]["attrname"]=$attr->labelText;
    $tmask[$k]["visibility"]=$labelvis[$attr->visibility];
    $tmask[$k]["bgcolor"]="";
    if (isset($tvisibilities[$attr->id])) {
      $tmask[$k]["vislabel"] = $labelvis[$tvisibilities[$attr->id]];
      if ($tvisibilities[$attr->id] != "-") $tmask[$k]["bgcolor"]=getParam("CORE_BGCOLORALTERN");
    } else $tmask[$k]["vislabel"] = $labelvis["-"];


    if ($attr->docid == $docid) {
      $tmask[$k]["disabled"]="";
    } else {
      $tmask[$k]["disabled"]="disabled";
    }


    $tmask[$k]["framelabel"]=$attr->fieldSet->labelText;

  }
          

  $this->lay->SetBlockData("MASK",$tmask);  
}



function editmask() {
 
  $docid = $this->getValue("MSK_FAMID",1);


  $this->lay->Set("docid",$docid);

  $doc= new Doc($this->dbaccess,$docid);


  $tvisibilities=$this->getVisibilities();
  
  $selectclass=array();
  $tclassdoc = GetClassesDoc($this->dbaccess, $this->userid);
  while (list($k,$cdoc)= each ($tclassdoc)) {
    $selectclass[$k]["idcdoc"]=$cdoc->id;
    $selectclass[$k]["classname"]=$cdoc->title;
    $selectclass[$k]["selected"]="";
  }


  $selectframe= array();

  $nbattr=0; // if new document 

  // display current values
  $newelem=array();

   

  // selected the current class document
  while (list($k,$cdoc)= each ($selectclass)) {

    if ($docid == $selectclass[$k]["idcdoc"]) {

      $selectclass[$k]["selected"]="selected";
    }
    
  }

  $this->lay->SetBlockData("SELECTCLASS", $selectclass);


  $ka = 0; // index attribute

  
  $labelvis=$this->getLabelVis();
  
    
  while(list($k,$v) = each($labelvis))  {

    $selectvis[] = array("visid" =>$k ,
			 "vislabel" => $v);
  }
		     

  //    ------------------------------------------
  //  -------------------- NORMAL ----------------------
  $tattr = $doc->GetNormalAttributes();
  
  uasort($tattr,"tordered"); 
  reset($tattr);
  while(list($k,$attr) = each($tattr))  {
    $newelem[$k]["attrid"]=$attr->id;
    $newelem[$k]["attrname"]=$attr->labelText;
    $newelem[$k]["visibility"]=$labelvis[$attr->visibility];
    
    $newelem[$k]["neweltid"]=$k;
    

    if ($attr->docid == $docid) {
      $newelem[$k]["disabled"]="";
    } else {
      $newelem[$k]["disabled"]="disabled";
    }


    $newelem[$k]["framelabel"]=$attr->fieldSet->labelText;


    reset($selectvis);
    while(list($kopt,$opt) = each($selectvis))  {
      if ($opt["visid"] == $tvisibilities[$attr->id]) {
	$selectvis[$kopt]["selected"]="selected"; 
      } else{
	$selectvis[$kopt]["selected"]=""; 
      }
		  
    }


    $newelem[$k]["SELECTVIS"]="SELECTVIS_$k";
    $this->lay->SetBlockData($newelem[$k]["SELECTVIS"],
			     $selectvis);
	      
    $ka++;
  }
          

  $this->lay->SetBlockData("NEWELEM",$newelem);

  $this->editattr();
}