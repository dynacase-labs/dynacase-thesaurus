<?php


$app_desc = array (
"name"		=>"URL",		//Name
"short_name"	=>N_("Url"),		//Short name
"description"	=>N_("Extern web application"),//long description
"access_free"	=>"N",			//Access free ? (Y,N)
"icon"		=>"url.png",	//Icon
"displayable"	=>"Y",			//Should be displayed on an app list (Y,N)
"with_frame"	=>"Y",			//Use multiframe ? (Y,N)
"childof"	=>""		// 	
);


$app_acl = array (
  array(
   "name"               =>"USEIT",
   "description"        =>N_("Can use this application"),
   "group_default"  => "Y")
);
   
$action_desc = array (
  array( 
   "name"		=>"SENDURL",
   "short_name"		=>N_("redirect to url"),
   "acl"		=>"USEIT",
   "root"		=>"Y"
  )
);

?>
