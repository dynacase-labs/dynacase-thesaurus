<?php
// ==========================================================================
// default attribute migration

// Author          Eric Brison	(Anakeen)
// Date            May, 23 2003 - 11:13:08
// Last Update     $Date: 2003/05/23 15:30:03 $
// Version         $Revision: 1.1 $
// ==========================================================================


$ConnId = pg_connect ("dbname=freedom user=anakeen");
$ResId = pg_query ($ConnId,"select attrids,values,fromid from doc where usefor='D'" );

$zou="rognougnou";
print "update docfam set defval='$zou';\n";
while ($row = pg_fetch_array($ResId))
{

  $ta = explode("£",$row["attrids"]);
  $tv = explode("£",$row["values"]);
  $fromid=$row["fromid"];
  while(list($k,$v) = each($ta))   {
    
    if ($v != "") {
      
      print "update docfam set defval=defval||'[$v|".$tv[$k]."]'" . "where  id=$fromid;\n";
    }
  }

} 
print "update docfam set defval='' where defval='$zou';\n";
print " update docfam set defval=str_replace(defval,'$zou','') where defval != ''\n";


pg_close ($ConnId);

// EOF
?>