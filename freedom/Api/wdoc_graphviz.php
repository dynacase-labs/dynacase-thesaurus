<?php
/**
 * Generate worflow graph
 *
 * @author Anakeen 2007
 * @version $Id: wdoc_graphviz.php,v 1.1 2007/01/12 17:37:09 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



// refreah for a classname
// use this only if you have changed title attributes

include_once("FDL/Lib.Attr.php");
include_once("FDL/Class.DocFam.php");

$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}


$docid = GetHttpVars("docid",0); // special docid
$type = GetHttpVars("type"); // type of graph

$label=($type=="complet");
$doc=new_doc($dbaccess,$docid);

$rankdir="LR";
$size="8,8";
if ($label) {
  $rankdir="TB";
  $size="";  
 }

foreach ($doc->cycle as $k=>$v) {
  $tmain='';
  if (isset($doc->autonext[$v["e1"]]) && ($doc->autonext[$v["e1"]]==$v["e2"])) $tmain='color=darkgreen,style="setlinewidth(3)",arrowsize=1.0';


  if ($label) { 
    $m1=$doc->transitions[$v["t"]]["m1"];
    $m2=$doc->transitions[$v["t"]]["m2"];
    if ($m1) {
      if ($tmain) $tmain.=",";
      $tmain.="taillabel=$m1";
    }
    if ($m2) {
      if ($tmain) $tmain.=",";
      $tmain.="headlabel=$m2";
    }
  $line[]=sprintf('"%s" -> "%s" [labelfontcolor="#555555",decorate=false, color=darkblue, fontsize=8, label="%s" %s];',
		   str_replace(" ","\\n",_($v["e1"])),
		   str_replace(" ","\\n",_($v["e2"])),
		  _($v["t"]),$tmain);
  } else {
   
    $line[]=sprintf('"%s" -> "%s" [color=darkblue %s];',
		  str_replace(" ","\\n",(_($v["e1"]))),
		  str_replace(" ","\\n",(_($v["e2"]))),$tmain);
  }
  //  $line[]='"'.utf8_encode(_($v["e1"])).'" -> "'.utf8_encode(_($v["e2"])).' [label="'..'";';
}
$line[]='"'.str_replace(" ","\\n",_($doc->firstState)).'" [shape = doublecircle];';;
$states=$doc->getStates();
foreach ($states as $k=>$v) {
  $color=$doc->getColor($v);
  if ($color)  $line[]='"'.str_replace(" ","\\n",_($v)).'" [ color="'.$color.'" ];';
}

$dot="digraph \"".$doc->title."\" {
        size=\"$size\";
	rankdir=$rankdir;
        splines=false;
	node [shape = circle, style=filled, fixedsize=true,width=1.5];\n";



$dot.= implode($line,"\n");
$dot.="\n}";

print utf8_encode($dot);
?>