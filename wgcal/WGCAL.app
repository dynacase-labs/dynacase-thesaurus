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
  array ( "name" => "WGCAL_OSYNC", "description" => N_("allow outlook sync."), "group_default"  => "N" ),
  array ( "name" => "WGCAL_USER",  "description" => N_("user access"), "group_default"  => "N" ),
  array ( "name" => "WGCAL_NONE",  "description" => N_("no access"), "group_default"  => "Y" ),
  array ( "name" => "WGCAL_HIDDEN", "description" => N_("invisible"), "group_default"  => "N" )
);


$action_desc = array (
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_CSS", "layout" => "wgcal.css", "short_name" =>N_("css manager"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_MAIN", "short_name" =>N_("main view"), "toc" => "N", "root" =>"Y"),


  array( "acl" => "WGCAL_USER", "name" => "RENDEZVOUS_READ", "short_name" =>N_("Rendez-vous default view"), "toc" => "N", "root" =>"N"),


  array( "acl" => "WGCAL_USER", "name" => "WGCAL_PORTAL", "short_name" =>N_("portal view"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_TOOLBAR", "short_name" =>N_("toolbar"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_SETTOOLSTATE", "short_name" =>N_("set display/undisplay tool state in toolbar"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_SETEVENTSTATE", "short_name" =>N_("set event state"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_CREATECALENDAR", "short_name" =>N_("create a new calendar"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_NEWCALENDAR", "short_name" =>N_("add a new calendar"), "toc" => "N", "root" =>"N"),

  array( "acl" => "WGCAL_USER", "name" => "WGCAL_MENUBAR", "short_name" =>N_("menubar"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_USETPARAM", "short_name" =>N_("set user param"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_SEARCHICAL", "short_name" =>N_("search calendar"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_SEARCH", "short_name" =>N_("search event"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_ASEARCH", "short_name" =>N_("advanced search event"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_ASEARCHQUERY", "short_name" =>N_("advanced search event"), "toc" => "N", "root" =>"N"),

  array( "acl" => "WGCAL_USER", "name" => "WGCAL_SEARCHIUSER", "short_name" =>N_("search iuser"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_SEARCHCONTACT", "short_name" =>N_("search contact"), "toc" => "N", "root" =>"N"),

  // ---------------------------
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_VIEWEVENT", 
         "short_name" =>N_("view event"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_EDITEVENT", 
         "short_name" =>N_("edit event"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_CHECKCONFLICT", 
         "short_name" =>N_("edit event"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_STOREEVENT", 
         "short_name" =>N_("store event"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_DELETEEVENT", 
         "short_name" =>N_("delete event"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_DELOCCUR", 
         "short_name" =>N_("delete occurence"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_HISTO", 
         "short_name" =>N_("event history"), "toc" => "N", "root" =>"N"),

  // TODOS -----------------------------------
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_TODODONE", 
	"short_name" =>N_("mark to done todos"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_TODOEDIT", 
	"short_name" =>N_("edit todos"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_TODOSTORE", 
	"short_name" =>N_("edit todos"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_TODOVIEW", 
	"short_name" =>N_("view todos"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_ALLTODO", 
	"short_name" =>N_("view all todos"), "toc" => "N", "root" =>"N"),

  // ---------------------------
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_RESSPICKER_MAIN", 
         "short_name" =>N_("ressource picker main"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_RESSPICKER", 
         "short_name" =>N_("ressource picker"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_RESSPICKERLIST", 
         "short_name" =>N_("ressource picker : list"), "toc" => "N", "root" =>"N"),

  array( "acl" => "WGCAL_USER", "name" => "WGCAL_SELECTRESS", "short_name" =>N_("select ressource for display"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_INITRESSOURCES", "short_name" =>N_("init ressource selection"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_ADDTOPREFCALS", "short_name" =>N_("add to prefered calendars"), "toc" => "N", "root" =>"N"),

  array( "acl" => "WGCAL_USER", "name" => "WGCAL_WAITRV", "short_name" =>N_("see the waiting rv"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_GVIEW", "short_name" =>N_("calendar generic view"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_CALENDAR", "short_name" =>N_("calendar view"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_VIEW", "short_name" =>N_("calendar view"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_TEXTMONTH", "short_name" =>N_("month text"), "toc" => "N", "root" =>"N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_PREFS", "short_name" =>N_("user preferences"), "toc" => "N", "root" =>"N"),


  array( "acl" => "WGCAL_USER", "name" => "WGCAL_OSYNC", "toc" => "N"),
  array( "acl" => "WGCAL_USER", "name" => "WGCAL_APROPOS", "toc" => "N"),


  array( "acl" => "WGCAL_ADMIN", "name" => "WGCAL_CHOOSEGROUPS", "toc" => "N"),
  array( "acl" => "WGCAL_ADMIN", "name" => "WGCAL_ADDGROUPS", "toc" => "N"),
  array( "acl" => "WGCAL_ADMIN", "name" => "WGCAL_DELGROUPS", "toc" => "N"),
  

  array( "acl" => "WGCAL_USER", "name" => "WGCAL_HIDDEN", "toc" => "N")

);

?>
