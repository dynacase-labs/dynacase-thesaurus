<?php


// refreah for a classname
// use this only if you have changed title attributes

include_once("FDL/Class.Doc.php");
include_once("FDL/Lib.Dir.php");




$appl = new Application();
$appl->Set("FDL",	   $core);


$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}


$whatid = GetHttpVars("whatid",""); // document

  
$query = new QueryDb("","User");

if ($whatid>0) {
  $query->AddQuery("id=$whatid");
}

      
    
$table1 = $query->Query(0,0,"TABLE");

if ($query->nb > 0)	{

  printf("\n%d user to update\n", count($table1));
  $card=count($table1);
  $doc = createDoc($dbaccess,$famId,false);
  while(list($k,$v) = each($table1)) 	    {	     
    // search already created card
      $title = strtolower($v["lastname"]. " ". $v["firstname"]);
    // first in IUSER
      if ($v["isgroup"] == "Y") {
	$filter = array("grp_whatid = ".$v["id"]);
	$tdoc = getChildDoc($dbaccess, 0,0,"ALL", $filter,1,"LIST",
			    getFamIdFromName($dbaccess,"IGROUP"));
      } else {
	$filter = array("us_whatid = ".$v["id"]);
	$tdoc = getChildDoc($dbaccess, 0,0,"ALL", $filter,1,"LIST",
			    getFamIdFromName($dbaccess,"IUSER"));
      }
    if (count($tdoc) > 0) {
      
      $tdoc[0]->refresh();
      $err=$tdoc[0]->modify();
      if ($err != "") print "$err\n";
      else printf( _("%s updated\n"),$tdoc[0]->title);
      
    } else {
      // search in all usercard same title
      $filter = array("lower(title) = '$title'");
      $tdoc = getChildDoc($dbaccess, 0,0,"ALL", $filter,1,"LIST",
			  getFamIdFromName($dbaccess,"USER"));
      if (count($tdoc) > 0) {
	if (count($tdoc) > 1) {
	  printf( _("find %s more than one, created aborded\n"),$title);
	} else {
	  $tdoc[0]->convert(getFamIdFromName($dbaccess,"IUSER"),
			    array("US_WHATID"=>$v["id"]));
	  printf( _("%s migrated\n"),$title);
	  
	}
      } else {
	// create new card
	if ($v["isgroup"]=="Y") {
	  $iuser = createDoc($dbaccess,getFamIdFromName($dbaccess,"IGROUP"));
	  $iuser->setValue("GRP_WHATID",$v["id"]);
	  $iuser->Add();
	  $iuser->refresh();
	  $iuser->modify();
	  printf( _("%s igroup created\n"),$title);
	} else {
	  $iuser = createDoc($dbaccess,getFamIdFromName($dbaccess,"IUSER"));
	  $iuser->setValue("US_WHATID",$v["id"]);
	  $iuser->Add();
	  $iuser->refresh();
	  $iuser->modify();
	  printf( _("%s iuser created\n"),$title);
	}
	
      }

      
    }

  }	  
}      
    

?>