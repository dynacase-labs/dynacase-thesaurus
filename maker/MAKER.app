<?php
// ---------------------------------------------------------------
// $Id: MAKER.app,v 1.1 2008/03/28 16:34:24 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/maker/MAKER.app,v $


$app_desc = array (
		   "name"	 =>"MAKER",		//Name
		   "short_name"	=>N_("Maker"),    	//Short name
		   "description"=>N_("Application Maker"),  //long descriptionppl
		   "access_free"=>"Y",			//Access free ? (Y,N)
		   "icon"	=>"maker.png",	//Icon
		   "displayable"=>"Y",			//Should be displayed on an app list (Y,N)
		   "with_frame"	=>"Y",			//Use multiframe ? (Y,N)
		   "childof"	=>""		        // instance of FREEDOM GENERIC application	
		   );

  
$app_acl = array (
  array(
   "name"               =>"MAKER",
   "description"        =>N_("Access to application maker"))
  
);

$action_desc = array (
  array( 
   "name"		=>"MAKER_ROOT",
   "short_name"		=>N_("entrance"),
   "acl"		=>"MAKER",
   "root"             => "Y"  )
)

		
?>
