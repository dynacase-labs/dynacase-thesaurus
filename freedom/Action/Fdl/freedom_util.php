<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_util.php,v 1.52 2004/06/11 16:13:37 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


// ---------------------------------------------------------------
// $Id: freedom_util.php,v 1.52 2004/06/11 16:13:37 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Fdl/freedom_util.php,v $
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

//

// ------------------------------------------------------
// construction of a sql disjonction
// ------------------------------------------------------
function GetSqlCond2($Table, $column) 
// ------------------------------------------------------
{
  $sql_cond="";
  if (count($Table) > 0)
    {
      $sql_cond = "(($column = '$Table[0]') ";
      for ($i=1; $i< count($Table); $i++)
	{
	  $sql_cond = $sql_cond."OR ($column = '$Table[$i]') ";
	}
      $sql_cond = $sql_cond.")";
    }

  return $sql_cond;
}


function GetSqlCond($Table, $column, $integer=false) 
// ------------------------------------------------------
{
  $sql_cond="";
  if (count($Table) > 0)
    {
      if ($integer) { // for integer type 
	$sql_cond = "$column in (";      
	$sql_cond .= implode(",",$Table);
	$sql_cond .= ")";
      } else {// for text type 
	$sql_cond = "$column in ('";      
	$sql_cond .= implode("','",$Table);
	$sql_cond .= "')";
      }
    }

  return $sql_cond;
}


/** 
 * optimize for speed : memorize object for future use
 * @global array $_GLOBALS["gdocs"] 
 * @name $gdocs
 */


/**
 * return document object in type concordance
 * @param Doc &$doc empty object document
 * @param string $dbaccess database specification
 * @param int $id identificator of the object
 * @param array $res array of result issue to QueryDb {@link QueryDb::Query()}
 * @param resource $dbid the database connection resource
 * @global array optimize for speed 
 * 
 * @return bool false if error occured
 */
function newDoc(&$doc,$dbaccess, $id='',$res='',$dbid=0) {

  global $gdocs;// optimize for speed

  
  if ($dbaccess=="") {
    // don't test if file exist or must be searched in include_path 
    include("dbaccess.php");
           
  }

  //    print("doctype:".$res["doctype"]);
  $classname="";
  if (($id == '') && ($res == "")) {
    include_once("FDL/Class.DocFile.php");
    $doc=new DocFile($dbaccess);

    return (true);
  }
  $fromid="";
  $gen=""; // path GEN or not
  if ($id > 0) {

    if (isset($gdocs[$id])) {
      $doc = $gdocs[$id]; // optimize for speed
      return true;
    }
  
    $dbid=getDbid($dbaccess);

        
    $fromid= getFromId($dbaccess,$id);
    if ($fromid > 0) {
      $classname= "Doc$fromid";
      $gen="GEN";
    }else if ($fromid == -1) $classname="DocFam"; 
    

    
  } else if ($res != '') {
    $fromid=$res["fromid"];
    $doctype=$res["doctype"];
    if ($doctype=="C") $classname= "DocFam"; 
    else if ($fromid > 0) {$classname= "Doc".$res["fromid"];$gen="GEN";}
    else  $classname=$res["classname"];
  }
	    
  if ($classname != "") {
    include_once("FDL$gen/Class.$classname.php");
    //    print "new $classname($dbaccess, $id, $res, $dbid)<BR>";
    $doc=new $classname($dbaccess, $id, $res, $dbid);
    if (($id > 0) && (count($gdocs) < MAXGDOCS))    $gdocs[$id]=&$doc;

    return (true);
  } else {
    include_once("FDL/Class.DocFile.php");
    $doc=new DocFile($dbaccess, $id, $res, $dbid);

    return (true);
  }
} 


/**
 * create a new document object in type concordance
 *
 * the document is set with default values and default profil of the family 
 * @param string $dbaccess database specification
 * @param string $fromid identificator of the family document (the number or internal name)
 * @param bool $control if false don't control the user hability to create this kind of document
 * @return Doc may be return false if no hability to create the document
 */
function createDoc($dbaccess,$fromid,$control=true) {

  if (! is_numeric($fromid)) $fromid=getFamIdFromName($dbaccess,$fromid);
  if ($fromid > 0) {
    include_once("FDL/Class.DocFam.php");
    $cdoc = new DocFam($dbaccess, $fromid);

    if ($control) {
      $err = $cdoc->control('create');
      if ($err != "") return false;
    }

    
    $classname = "Doc".$fromid;
    include_once("FDLGEN/Class.$classname.php");
    $doc = new $classname($dbaccess);
    
    $doc->revision = "0";
    $doc->fileref = "0";
    $doc->doctype = $doc->defDoctype;// it is a new  document (not a familly)
    $doc->cprofid = "0"; // NO CREATION PROFILE ACCESS

    $doc->fromid = $fromid;
    $doc->setProfil($cdoc->cprofid); // inherit from its familly
    $doc->setCvid($cdoc->ccvid); // inherit from its familly	
    $doc->icon = $cdoc->icon; // inherit from its familly	
    $doc->usefor = $cdoc->usefor; // inherit from its familly
    $doc->wid=$cdoc->wid;
    
    $doc->setDefaultValues($cdoc->getDefValues());
    $doc->ApplyMask();
    return ($doc);
    
  }
  return new Doc($dbaccess);

}
/**
 * return document table value
 * @param string $dbaccess database specification
 * @param int $id identificator of the object
 * 
 * @return array false if error occured
 */
function getFromId($dbaccess, $id) {

  if (!($id > 0)) return false;
  if (! is_numeric($id)) return false;
  $dbid=getDbid($dbaccess);   
  $fromid=false;
  $result = pg_query($dbid,"select  fromid from docfrom where id=$id;");

  if (pg_numrows ($result) > 0) {
    $arr = pg_fetch_array ($result, 0,PGSQL_ASSOC);

    $fromid= $arr["fromid"];
  }
  
  return $fromid;    
} 
/**
 * return document table value
 * @param string $dbaccess database specification
 * @param int $id identificator of the object
 * @param array $sqlfilters add sql supply condition
 * 
 * @return array false if error occured
 */
function getTDoc($dbaccess, $id,$sqlfilters=array()) {
  global $action;
  global $SQLDELAY,$SQLDEBUG;

  if (!($id > 0)) return false;
  $dbid=getDbid($dbaccess);   
  $table="doc";
  $fromid= getFromId($dbaccess, $id);
  if ($fromid > 0) $table="doc$fromid";
  else if ($fromid == -1) $table="docfam";

  $sqlcond="";
  if (count($sqlfilters)>0)    $sqlcond = "and (".implode(") and (", $sqlfilters).")";

  $userid=$action->user->id;
  if ($SQLDEBUG) $sqlt1=microtime(); // to test delay of request
  $sql="select *,getuperm($userid,profid) as uperm from only $table where id=$id $sqlcond;";
  $result = pg_query($dbid,$sql); 
  if ($SQLDEBUG) {
       global $TSQLDELAY;
       $SQLDELAY+=microtime_diff(microtime(),$sqlt1);// to test delay of request
       $TSQLDELAY[]="t=>".microtime_diff(microtime(),$sqlt1)."s=>$sql";
  }
  if (pg_numrows ($result) > 0) {
    $arr = pg_fetch_array ($result, 0, PGSQL_ASSOC);

    return $arr;
  }
  return false;  
} 
/**
 * return the value of an array item
 *
 * @param array $t the array where get value
 * @param string $k the index of the value
 * @param string $d default value if not found or if it is empty
 * @return string
 */
function getv(&$t,$k,$d="") {
  if (isset($t[$k]) && ($t[$k] != "")) return $t[$k];
  if (strpos($t["attrids"],"£$k") !== 0) {
    
    $tvalues = explode("£",$t["values"]);
    $tattrids = explode("£",$t["attrids"]);
      
    while(list($ka,$va) = each($tattrids)) {
      $t[$va]=$tvalues[$ka];
      if ($va == $k) {
	if ($tvalues[$ka]!="") return $tvalues[$ka];
	break;
      }
    }
  }
  return $d;
}

/** 
 * use to usort attributes
 * @param BasicAttribute $a
 * @param BasicAttribute $b
 */
function tordered($a, $b) {
  
  if (isset($a->ordered) && isset($b->ordered)) {
	if (intval($a->ordered) == intval($b->ordered)) return 0;
	if (intval($a->ordered) > intval($b->ordered)) return 1;
	return -1;
  }
  if (isset($a->ordered) ) return 1;
  if (isset($b->ordered) ) return -1;
  return 0;
	
}


/**
 * return the identificator of a family from internal name
 *
 * @param string $dbaccess database specification
 * @param string $name internal family name

 * @return int 0 if not found
 */
function getFamIdFromName($dbaccess, $name) {
  include_once("FDL/Class.DocFam.php");
  global $tFamIdName;

  if (! isset($tFamIdName)) {
    $q = new QueryDb($dbaccess, "DocFam");
    $ql=$q->Query(0,0,"TABLE");
    
    while(list($k,$v) = each($ql)) {
      if ($v["name"] != "") $tFamIdName[$v["name"]]=$v["id"];
    }
  }

  if (isset($tFamIdName[$name])) return $tFamIdName[$name];
  return 0; 
  
}

function setFamidInLayout(&$action) {
  
  global $tFamIdName;

  if (! isset($tFamIdName))  getFamIdFromName($action->GetParam("FREEDOM_DB"),"-");
  
  reset($tFamIdName);
  while(list($k,$v) = each($tFamIdName)) {
    $action->lay->set("IDFAM_$k", $v);
  }
}


// --------------------------------------------------------------------------
// return freedom document in concordance with what user id
// I               
// O               
// I/O             
// Return          
// Date            jun, 05 2003 - 13:51:04
// Author          Eric Brison	(Anakeen)
// --------------------------------------------------------------------------
function getDocFromUserId($dbaccess,$userid) {
  if ($userid == "") return false;
  include_once("FDL/Lib.Dir.php");
  $tdoc=array();
  $user = new User("",$userid);
  if (! $user->isAffected()) return false;
  if ($user->isgroup == "Y") {
    $filter = array("us_whatid = $userid");
    $tdoc = getChildDoc($dbaccess, 0,0,"ALL", $filter,1,"LIST",
			getFamIdFromName($dbaccess,"IGROUP"));
  } else {
    $filter = array("us_whatid = $userid");
    $tdoc = getChildDoc($dbaccess, 0,0,"ALL", $filter,1,"LIST",
			getFamIdFromName($dbaccess,"IUSER"));
  }
  if (count($tdoc) == 0) return false;
  return $tdoc[0];
}


function ComputeVisibility($vis, $fvis) {
  if ($vis == "I") return $vis;
  if ($fvis == "H") return $fvis;
  if (($fvis == "R") && ($vis != "H")) return $fvis;

  return $vis;

}

/**
 * return doc array of latest revision of initid
 *
 * @param string $dbaccess database specification
 * @param string $initid initial identificator of the  document 
 * @param array $sqlfilters add sql supply condition
 * @return array values array if found. False if initid not avalaible
 */
function getLatestTDoc($dbaccess, $initid,$sqlfilters=array()) {
  global $action;

  if (!($initid > 0)) return false;
  $dbid=getDbid($dbaccess);   
  $table="doc";
  $fromid= getFromId($dbaccess, $initid);
  if ($fromid > 0) $table="doc$fromid";
  else if ($fromid == -1) $table="docfam";
    
  $sqlcond="";
  if (count($sqlfilters)>0)    $sqlcond = "and (".implode(") and (", $sqlfilters).")";

  $userid=$action->user->id;
  $result = pg_exec($dbid,"select *,getuperm($userid,profid) as uperm  from only $table where initid=$initid and locked != -1 $sqlcond;");
  if (pg_numrows ($result) > 0) {
    $arr = pg_fetch_array ($result, 0, PGSQL_ASSOC);

    return $arr;
  }
  return false;  
} 

//============================== XML =======================

function fromxml($xml,&$idoc){
  global $action;
  $fp = $xml;

  global $value; //used to stock value of one attribut (is string type)
    $value="";
    global $tabvalues; //used to stock document attribute values
    $tabvalues=array();
    global $is_array;
   
    global $i;
    global $depth_index;
    global $title;// 
    $depth_index=0;//used for knowing the curent xml level. 0 at the begining
    $i=0;
    global $attr_idoc;// used for idoc attribute (idoc and idoclist)
    $attr_idoc=false;
    global $list;// used for list attribute (textlist and idoclist)
    global $tempidoc;//is need to acces to $idoc in startElement() 
    $tempidoc=$idoc;


    //these two next functions are used for idoc attributes
     function recreate_balise_ouvrante($name, $attrs){
       //printf("ici ");
      $balise="<$name ";
      while (list($att,$valeur) = each($attrs)) {
	$balise.=" $att=\"$valeur\"";
      }
      $balise.=">";
      //printf("balise_ouvrante");
      return $balise;
    }

    function recreate_balise_fermante($name){
      return "</$name>";
    }


    function startElement($parser, $name, $attrs) {
      //this function is called when parser find a start element in the xml.
      global $depth_index;
      $depth_index++;
      global $action;
      global $title;
      global $attr_idoc;
      global $is_array;
      global $value;
      global $tempidoc;

    	if ($depth_index==1) {
	  $title=$attrs["TITLE"];
          }
     
	if ($attr_idoc){  
	  $value.=recreate_balise_ouvrante($name,$attrs);//to recover xml of idoc or listidoc attribute.
	} 

	if ($depth_index==3){
	
	  $attribute=$tempidoc->GetAttribute($name);

	  $is_array=false;
	  // $attr_idoc=false;
	  $is_array= $attribute->inArray();
	  if ($attribute->type=="idoc"){ $attr_idoc=true;}
	  if ($attribute->repeat){ $is_array=true;}
		

	}
    }




    function endElement($parser, $name) {
      //this function is called when parser find a end element in the xml.
      global $value;
      global $tabvalues;
      global $i;
      global $list;
      global  $depth_index;
      global $attr_idoc;
      global $is_array;
      
   
      if ($depth_index==3){
	if (!$is_array){//case of single attribut
	  
	  if ($attr_idoc){
	    $tabvalues[$name]=base64_encode($value);// in case of idoc attribute, value(a xml) of the attribute is coded
	  }
	  else{ $tabvalues[$name]=$value;
	  }
	  
	}
	else { 
	  $tabvalues[$name]="something";//the value is not important but $tabvalues[$name] must exist
	  //case of list attribut
	  if ($attr_idoc){$list[$name][$i]=base64_encode($value);}
	  else {$list[$name][$i]=$value;}
	  // printf("icic");
	  $i++;
	}
	$value="";
	if ($attr_idoc){$attr_idoc=false;}  
      }
   
      
   
      if($attr_idoc){//to recover xml of idoc attribute
	$value.=recreate_balise_fermante($name);
	//printf("la   ");printf($value);
      }
      
      $depth_index--; 
    }
    
    
    function characterData($parser, $data) {
      global $value; 
      if (chop($data)!=""){
	$value.=$data;
	
      }
      
    }
    
    $xml_parser = xml_parser_create();
    // use case-folding so we are sure to find the tag in $map_array
    xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
    xml_set_element_handler($xml_parser, "startElement", "endElement");
    xml_set_character_data_handler($xml_parser, "characterData");
    
    
    
    if (!xml_parse($xml_parser,$fp)){
      die(sprintf("XML error: %s at line %d",
		  xml_error_string(xml_get_error_code($xml_parser)),
		  xml_get_current_line_number($xml_parser)));
    }
    xml_parser_free($xml_parser);
    
    
    
    while ($attribut=each($tabvalues)){
      //printf($attribut[0]);
      //printf(" : ");
      //printf(sizeof($list[$attribut[0]]));
      //printf("\n");
      if (sizeof($list[$attribut[0]])!=0){//
	//printf("tableau_sup_a_zero");
	$value="";
	$ii=0;
	while ($x=each($list[$attribut[0]])){
	  $ii++;
	   if(($ii) != 1){
	    $value.="\n";
	   }
	    $value.=$x[1];
	}

	//printf($value);
	$idoc->SetValue($attribut[0],$value);
	
      }
      else{
	$idoc->SetValue($attribut[0],$attribut[1]);
	
      }
      
      
    }
    $idoc->title=$title;
    return  $idoc;
}


function recup_argument_from_xml($xml,$nom_arg){

  $title=stristr($xml,"$nom_arg=");
  $title=strstr($title,"\"");
  $title=substr($title,1);
  $fin=strpos($title,"\"");
  $title=substr($title,0,$fin);
  //printf($title);
  return $title;
}











?>
