<?
global $app_acl, $app_desc,$action_desc;

$app_desc= array (
"name" 		=>"WGCAL",                 //Name
"short_name"	=>N_("workgroup calendar"),                 //Short name
"description"	=>N_("workgroup calendar"),  //long description
"access_free"	=>"N",                    //Access type (ALL,RESTRICT)
"icon"		=>"wgcal.gif",             //Icon
"with_frame"	=>"Y",			//Use multiframe ? (Y,N)
"displayable"	=>"Y"                    //Should be displayed on an app list
);

$app_acl = array (
  array ( "name" => "WGCAL_ADMIN", "description" => N_("admin access"), "group_default"  => "N" ),
  array ( "name" => "WGCAL_USER",  "description" => N_("user access"), "group_default"  => "N" ),
  array ( "name" => "WGCAL_NONE",  "description" => N_("no access"), "group_default"  => "Y" )
);


$action_desc = array (
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_CSS", "layout" => "wgcal.css", "short_name" =>N_("css manager"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_MAIN", "short_name" =>N_("main view"), "toc" => "N", "root" =>"Y"),

  array( "acl" => "WGCAL_USER", "name" => "WGCAL_TOOLBAR", "short_name" =>N_("toolbar"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_SETTOOLSTATE", "short_name" =>N_("set display/undisplay tool state in toolbar"), "toc" => "N", "root" =>"N"),

  array( "acl" => "WGCAL_USER", "name" => "WGCAL_MENUBAR", "short_name" =>N_("menubar"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_USETPARAM", "short_name" =>N_("set user param"), "toc" => "N", "root" =>"N"),


  // ---------------------------
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_EDITEVENT", 
         "short_name" =>N_("edit event"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_STOREEVENT", 
         "short_name" =>N_("store event"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_DELETEEVENT", 
         "short_name" =>N_("delete event"), "toc" => "N", "root" =>"N"),

  // ---------------------------
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_RESSPICKER_MAIN", 
         "short_name" =>N_("ressource picker main"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_RESSPICKER", 
         "short_name" =>N_("ressource picker"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_RESSPICKERLIST", 
         "short_name" =>N_("ressource picker : list"), "toc" => "N", "root" =>"N"),

  array( "acl" => "WGCAL_USER", "name" => "WGCAL_SELECTRESS", "short_name" =>N_("select ressource for display"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_INITRESSOURCES", "short_name" =>N_("init ressource selection"), "toc" => "N", "root" =>"N"),

  array( "acl" => "WGCAL_USER", "name" => "WGCAL_CALENDAR", "short_name" =>N_("calendar view"), "toc" => "N", "root" =>"N"),

  array( "acl" => "WGCAL_USER", "name" => "WGCAL_HIDDEN", "toc" => "N")
);

?>
