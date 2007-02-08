<?php


  /** change password for a user
   * @param string $login the login of the user
   * @param string $password the new password in clear text
   * @return string error message if one else empty string if OK
   */
function changePassword($login,$password) {
  global $action;
  $dbfreedom=  _initFreedom();
  $uid = _getUserFid();
  if (! $action->hasPermission("FUSERS_MASTER","FUSERS")) return sprintf(_("permission FUSERS_MASTER:FUSERS needed\n"));

    $u = new User();
    if ($u->SetLoginName($login)) {
      if ($u->fid > 0) {	
	$du=new_doc($dbfreedom,$u->fid);
	if ($du->isAlive()) {
	  $err=$du->control("edit");
	  if ($err != "") return $err;
	}
      }
      $u->password_new=$password;
      $err=$u->modify();
      if ($err != "") return $err;
    } else {
      return sprintf(_("login %s not found\n"),$login);
    }
  }


/**
   * get list of avalaibel application for a user
   * @param string $login the login of the user
   * @return array list of application
   */
   function getAvailableApplication($login) {
  
    global $action;
    _initFreedom();

    $action->user=new User();

    //    return serialize(array("coucou"=>"test"));
    if ($action->user->SetLoginName($login)) {
      
      return serialize($action->GetAvailableApplication());
    } else {
      return sprintf(_("login %s not found\n"),$login);
    }    
  }
?>